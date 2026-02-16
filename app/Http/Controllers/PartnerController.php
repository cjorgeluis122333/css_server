<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
// CAMPOS PARA EL INDEX (TABLA RESUMEN)
    protected $selectIndex = [
        'ind', // ID interno necesario para links
        'acc', 'nombre', 'cedula', 'telefono', 'correo', 'nacimiento'
    ];

    // CAMPOS PARA EDITAR/CREAR (DETALLE COMPLETO)
    protected $fillableFields = [
        'acc', 'nombre', 'cedula', 'carnet', 'celular',
        'telefono', 'correo', 'direccion', 'nacimiento',
        'ingreso', 'ocupacion', 'cobrador'
    ];

    public function index(Request $request)
    {
        $partners = Partner::select($this->selectIndex)
            ->orderBy('acc', 'asc')
            ->paginate($request->get('per_page', 50));

        return response()->json($partners);
    }

    public function show($id)
    {
        // Al mostrar un socio para editar, traemos todos los campos requeridos
        $partner = Partner::select($this->fillableFields)->find($id);

        if (!$partner) {
            return response()->json(['message' => 'Socio no encontrado'], 404);
        }

        return response()->json($partner);
    }

    public function store(Request $request)
    {
        // Validación básica
        $data = $request->validate([
            'acc' => 'required|integer|unique:0cc_socios,acc',
            'nombre' => 'required|string',
            // ... agrega tus otras validaciones aquí
        ]);

        // Aseguramos que sea titular y tomamos solo los campos permitidos
        $data['categoria'] = 'titular';

        $partner = Partner::create($request->only($this->fillableFields) + ['categoria' => 'titular']);

        return response()->json(['message' => 'Socio creado', 'data' => $partner], 201);
    }

    public function update(Request $request, $id)
    {
        $partner = Partner::find($id);

        if (!$partner) {
            return response()->json(['message' => 'Socio no encontrado'], 404);
        }

        // Actualizamos solo los campos de interés
        $partner->update($request->only($this->fillableFields));

        return response()->json(['message' => 'Socio actualizado']);
    }

    public function destroy($id)
    {
        $partner = Partner::find($id);
        if ($partner) {
            $partner->delete();
            // Opcional: Aquí podrías borrar también a sus familiares en cascada
        }
        return response()->json(['message' => 'Socio eliminado']);
    }
}
