<?php

namespace App\Http\Controllers;
use App\Models\Partner;
use App\Enum\PartnerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class FamilyController extends Controller
{
    // CAMPOS EXCLUSIVOS PARA FAMILIARES
    protected $familyFields = [
        'ind', // ID necesario para editar/borrar
        'acc', // Necesario para saber de quién es familiar (aunque no se muestre en tabla)
        'nombre', 'cedula', 'carnet', 'celular', 'nacimiento', 'direccion'
    ];

    /**
     * GET /api/families
     * Lista los familiares.
     * * Optimización: Permite filtrar por '?acc=123' para traer solo los dependientes
     * de un socio específico de forma instantánea gracias al índice compuesto.
     */
    public function index(Request $request)
    {
        // Usamos el scope 'dependents' (definido en el Modelo)
        // SQL: WHERE categoria = 'familiar'
        $query = Partner::dependents()->select($this->familyFields);

        // Filtro Crítico: Obtener familiares de un titular específico
        if ($request->has('acc')) {
            $query->where('acc', $request->input('acc'));
        }

        // Búsqueda opcional por nombre del familiar
        if ($search = $request->input('buscar')) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        // Ordenamos por nombre para facilitar la lectura en listas familiares
        $query->orderBy('nombre', 'asc');

        return response()->json($query->paginate($request->input('per_page', 50)));
    }

    /**
     * GET /api/families/{id}
     * Muestra detalle de un familiar específico.
     */
    public function show($id)
    {
        // Seguridad: Solo devuelve si el ID corresponde a un FAMILIAR.
        $dependent = Partner::dependents()
            ->where('ind', $id)
            ->first();

        if (!$dependent) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        return response()->json($dependent);
    }

    /**
     * POST /api/families
     * Crea un nuevo familiar vinculado a un titular existente.
     */
    public function store(Request $request)
    {
        $request->validate([
            // Validamos que el 'acc' exista en la base de datos (que haya un titular)
            'acc'        => 'required|integer|exists:0cc_socios,acc',
            'nombre'     => 'required|string|max:150',
            'cedula'     => 'nullable|string|max:30|unique:0cc_socios,cedula',
            'nacimiento' => 'nullable|date',
            'celular'    => 'nullable|string|max:25',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Preparamos los datos
                $data = $request->only($this->familyFields);

                // Forzamos la lógica de negocio
                $data['categoria'] = PartnerCategory::FAMILIAR;
                $data['sincro'] = 0; // Marcar para sincronizar
                $data['cobrador'] = 0; // Familiares no suelen tener cobrador asignado

                $dependent = Partner::create($data);

                return response()->json([
                    'message' => 'Familiar agregado exitosamente',
                    'data'    => $dependent
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear familiar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/families/{id}
     * Actualiza datos de un familiar.
     */
    public function update(Request $request, $id)
    {
        // Buscamos usando el scope para asegurar que editamos un familiar
        $dependent = Partner::dependents()->find($id);

        if (!$dependent) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        $request->validate([
            'nombre' => 'string|max:150',
            'cedula' => ['nullable', 'string', Rule::unique('0cc_socios', 'cedula')->ignore($dependent->ind, 'ind')],
            // Ojo: Generalmente NO permitimos cambiar el 'acc' aquí,
            // porque mover un familiar a otro socio es una operación delicada.
        ]);

        try {
            DB::transaction(function () use ($dependent, $request) {
                // Filtramos campos para evitar que inyecten 'categoria' o cambien el 'acc' accidentalmente
                $inputs = $request->only(['nombre', 'cedula', 'carnet', 'celular', 'telefono', 'correo', 'direccion', 'nacimiento', 'ocupacion']);

                $dependent->update($inputs);

                $dependent->sincro = 0;
                $dependent->save();
            });

            return response()->json(['message' => 'Familiar actualizado correctamente', 'data' => $dependent]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /api/families/{id}
     * Elimina un familiar.
     */
    public function destroy($id)
    {
        $dependent = Partner::dependents()->find($id);

        if (!$dependent) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        try {
            $dependent->delete();
            return response()->json(['message' => 'Familiar eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
