<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Enum\PartnerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PartnerController extends Controller
{
    /**
     * Campos ligeros para el listado masivo (Index).
     * Evitamos traer campos pesados como 'direccion' o 'notas' si las hubiera.
     */
    protected $selectIndex = [
        'ind', 'acc', 'nombre', 'cedula', 'celular', 'correo', 'nacimiento', 'categoria'
    ];

    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillableFields = [
        'acc', 'nombre', 'cedula', 'carnet', 'celular',
        'telefono', 'correo', 'direccion', 'nacimiento',
        'ingreso', 'ocupacion', 'cobrador'
    ];

    /**
     * GET /api/partners
     * Listado optimizado y paginado de Titulares.
     */
    public function index(Request $request)
    {
        // 1. Iniciamos la consulta usando el Scope 'titulares' (definido en el Modelo)
        $query = Partner::holders()->select($this->selectIndex);

        // 2. Búsqueda optimizada
        if ($search = $request->input('buscar')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('acc', 'like', "{$search}%") // Búsqueda por prefijo es más rápida
                    ->orWhere('cedula', 'like', "{$search}%");
            });
        }

        // 3. Ordenamiento por índice (acc es indexado)
        $query->orderBy('acc', 'asc');

        // 4. Paginación (Vital para escalabilidad)
        $partners = $query->paginate($request->input('per_page', 50));

        return response()->json($partners);
    }

    /**
     * GET /api/partners/{id}
     * Muestra el detalle completo de un Titular.
     */
    public function show($id)
    {
        // Buscamos por ID ('ind') pero aseguramos que sea TITULAR.
        // Si el ID existe pero es un FAMILIAR, devolverá 404 (seguridad).
        $partner = Partner::holders()
            ->where('ind', $id)
            ->first();

        if (!$partner) {
            return response()->json(['message' => 'Socio titular no encontrado'], 404);
        }

        return response()->json($partner);
    }

    /**
     * POST /api/partners
     * Crea un nuevo Socio Titular.
     */
    public function store(Request $request)
    {
        // 1. Validación estricta
        $validated = $request->validate([
            'acc'        => 'required|integer|unique:0cc_socios,acc', // La acción debe ser única globalmente
            'nombre'     => 'required|string|max:150',
            'cedula'     => 'nullable|string|max:30|unique:0cc_socios,cedula',
            'correo'     => 'nullable|email|max:150',
            'nacimiento' => 'nullable|date',
            'ingreso'    => 'nullable|date',
            'cobrador'   => 'integer',
            // ... resto de validaciones
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Forzamos la categoría TITULAR y fusionamos con los datos validados
                $data = $request->only($this->fillableFields);
                $data['categoria'] = PartnerCategory::TITULAR;
                $data['sincro'] = 0; // Marcar para sincronizar

                $partner = Partner::create($data);

                return response()->json([
                    'message' => 'Socio titular creado exitosamente',
                    'data'    => $partner
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear socio: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/partners/{id}
     * Actualiza un Socio Titular existente.
     */
    public function update(Request $request, $id)
    {
        $partner = Partner::holders()->find($id);

        if (!$partner) {
            return response()->json(['message' => 'Socio titular no encontrado'], 404);
        }

        // Validación (ignorando el ID actual para unique)
        $request->validate([
            'acc'    => ['integer', Rule::unique('0cc_socios', 'acc')->ignore($partner->ind, 'ind')],
            'cedula' => ['nullable', 'string', Rule::unique('0cc_socios', 'cedula')->ignore($partner->ind, 'ind')],
            'nombre' => 'string|max:150',
            'correo' => 'nullable|email|max:150',
        ]);

        try {
            DB::transaction(function () use ($partner, $request) {
                // Actualizamos solo los campos permitidos
                $partner->update($request->only($this->fillableFields));

                // Actualizamos estado de sincronización si hubo cambios
                $partner->sincro = 0;
                $partner->save();
            });

            return response()->json(['message' => 'Socio actualizado correctamente', 'data' => $partner]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/partners/{id}
     * Elimina un socio (Hard Delete).
     */
    public function destroy($id)
    {
        $partner = Partner::holders()->find($id);

        if (!$partner) {
            return response()->json(['message' => 'Socio no encontrado'], 404);
        }

        try {
            // VERIFICACIÓN DE INTEGRIDAD (Opcional pero recomendada)
            // Verificar si tiene familiares antes de borrar
            if ($partner->dependents()->exists()) {
                return response()->json([
                    'message' => 'No se puede eliminar: El socio tiene familiares asociados. Elimine los familiares primero.'
                ], 409); // Conflict 409
            }

            $partner->delete(); // DELETE FROM ... (Irreversible)

            return response()->json(['message' => 'Socio eliminado permanentemente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
