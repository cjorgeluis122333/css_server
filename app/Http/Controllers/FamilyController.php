<?php

namespace App\Http\Controllers;
use App\Models\Partner;
use Illuminate\Http\Request;
class FamilyController extends Controller
{
    // CAMPOS EXCLUSIVOS PARA FAMILIARES
    protected $familyFields = [
        'ind', // ID necesario para editar/borrar
        'acc', // Necesario para saber de quién es familiar (aunque no se muestre en tabla)
        'nombre', 'cedula', 'carnet', 'celular', 'nacimiento', 'direccion'
    ];

    /**
     * Listar familiares.
     * Puedes pasar ?acc=123 para ver los de un socio, o ver todos.
     */
    public function index(Request $request)
    {
        $query = Partner::withoutGlobalScope('solo_titulares')
            ->select($this->familyFields)
            ->where('categoria', 'familiar');

        // Filtro opcional: Si envías el ID del socio titular (?acc=100)
        if ($request->has('acc')) {
            $query->where('acc', $request->input('acc'));
        }

        $familiares = $query->orderBy('nombre', 'asc')
            ->paginate($request->get('per_page', 50));

        return response()->json($familiares);
    }

    public function show($id)
    {
        $familiar = Partner::withoutGlobalScope('solo_titulares')
            ->where('categoria', 'familiar')
            ->select($this->familyFields)
            ->where('ind', $id) // Buscamos por ID primario (ind)
            ->first();

        if (!$familiar) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        return response()->json($familiar);
    }

    public function store(Request $request)
    {
        $request->validate([
            'acc' => 'required|integer', // OBLIGATORIO: Para vincular al titular
            'nombre' => 'required|string',
        ]);

        $data = $request->only($this->familyFields);
        $data['categoria'] = 'familiar'; // Forzamos categoría

        $familiar = Partner::create($data);

        return response()->json(['message' => 'Familiar creado', 'data' => $familiar], 201);
    }

    public function update(Request $request, $id)
    {
        $familiar = Partner::withoutGlobalScope('solo_titulares')
            ->where('categoria', 'familiar')
            ->where('ind', $id)
            ->first();

        if (!$familiar) {
            return response()->json(['message' => 'Familiar no encontrado'], 404);
        }

        // Actualizamos solo los campos permitidos para familiares
        $familiar->update($request->only($this->familyFields));

        return response()->json(['message' => 'Familiar actualizado']);
    }

    public function destroy($id)
    {
        $familiar = Partner::withoutGlobalScope('solo_titulares')
            ->where('categoria', 'familiar')
            ->where('ind', $id)
            ->first();

        if ($familiar) {
            $familiar->delete();
        }

        return response()->json(['message' => 'Familiar eliminado']);
    }
}
