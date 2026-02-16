<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{

    /**
     * This methode returns a list of partner paginated
     * internal has an addGlobalScope and put default "titular"
     */
    public function index(Request $request)
    {

        $partners = Partner::select([
            'acc',
            'nombre',
            'cedula',
            'telefono',
            'correo',
            'nacimiento'
        ])
            ->orderBy('acc', 'asc')
            ->paginate($request->get('per_page', 50));

        return response()->json($partners);
    }


    /**
     * Mostrar un Socio específico Y sus familiares.
     */
    public function show($id)
    {
        // Buscamos al titular y cargamos la relación 'familiares'
        $socio = Partner::with('familiares')->find($id);

        if (!$socio) {
            return response()->json(['message' => 'Socio no encontrado'], 404);
        }

        return response()->json($socio);
    }

    /**
     * Create a new partner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'acc'    => 'required|integer|unique:0cc_socios,acc',
            'cedula' => 'nullable|integer',
            // ... resto de validaciones
        ]);

        // Forzamos la categoría
        $validated['categoria'] = 'titular';

        $socio = Partner::create($validated);

        return response()->json(['message' => 'Socio creado', 'data' => $socio], 201);
    }

    /**
     * Update Partner.
     */
    public function update(Request $request, $id)
    {
        $socio = Partner::find($id); // El Scope asegura que sea titular

        if (!$socio) {
            return response()->json(['message' => 'Socio no encontrado'], 404);
        }

        $socio->update($request->all());

        return response()->json(['message' => 'Socio actualizado', 'data' => $socio]);
    }

    /**
     * Eliminar un Socio (y opcionalmente a toda su familia).
     */
    public function destroy($id)
    {
        $socio = Partner::find($id);

        if (!$socio) {
            return response()->json(['message' => 'Socio no encontrado'], 404);
        }

        // Transacción para asegurar que si borramos al titular, se borren los familiares
        DB::transaction(function () use ($socio) {
            // 1. Borrar familiares asociados al mismo 'acc'
            Partner::withoutGlobalScope('solo_titulares')
                ->where('acc', $socio->acc)
                ->where('categoria', 'familiar')
                ->delete();

            // 2. Borrar al titular
            $socio->delete();
        });

        return response()->json(['message' => 'Socio y sus familiares eliminados correctamente']);
    }

    // ==========================================
    // SECCIÓN 2: GESTIÓN DE FAMILIARES
    // ==========================================

    /**
     * Listar solo los familiares de un socio específico (por ID del socio padre).
     */
    public function getFamiliares($socioId)
    {
        $socio = Partner::find($socioId);

        if (!$socio) {
            return response()->json(['message' => 'Socio titular no encontrado'], 404);
        }

        // Usamos la relación definida en el modelo
        return response()->json($socio->familiares);
    }

    /**
     * Agregar un familiar a un Socio existente.
     */
    public function storeFamiliar(Request $request, $socioId)
    {
        $socioTitular = Partner::find($socioId);

        if (!$socioTitular) {
            return response()->json(['message' => 'Socio titular no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'parentesco' => 'nullable|string', // Si agregaste este campo
            // ... otras validaciones
        ]);

        // Datos automáticos
        $validated['acc'] = $socioTitular->acc; // Hereda el número de cuenta
        $validated['categoria'] = 'familiar';   // Se marca como familiar

        // Usamos withoutGlobalScope para poder crear/ver sin restricciones,
        // aunque create funciona directo, es buena práctica ser explícito si usas el mismo modelo.
        $familiar = Partner::create($validated);

        return response()->json(['message' => 'Familiar agregado', 'data' => $familiar], 201);
    }

    /**
     * Actualizar un Familiar específico.
     * NOTA: Aquí necesitamos 'withoutGlobalScope' porque por defecto Laravel
     * oculta a los familiares.
     */
    public function updateFamiliar(Request $request, $familiarId)
    {
        // Buscamos saltándonos el filtro "solo titulares"
        $familiar = Partner::withoutGlobalScope('solo_titulares')
            ->where('id', $familiarId) // Asumiendo que 'id' es 'ind' en tu base de datos
            ->where('categoria', 'familiar') // Seguridad extra
            ->first();

        if (!$familiar) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        $familiar->update($request->all());

        return response()->json(['message' => 'Familiar actualizado', 'data' => $familiar]);
    }

    /**
     * Eliminar un Familiar específico.
     */
    public function destroyFamiliar($familiarId)
    {
        $familiar = Partner::withoutGlobalScope('solo_titulares')
            ->where('id', $familiarId)
            ->where('categoria', 'familiar')
            ->first();

        if (!$familiar) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        $familiar->delete();

        return response()->json(['message' => 'Familiar eliminado']);
    }
}
