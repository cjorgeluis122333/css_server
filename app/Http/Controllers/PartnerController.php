<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{

    public function index(Request $request)
    {
        $partners = Partner::select([
            'acc',
            'nombre',
            'cedula',
            'telefono',
            'correo',
            'nacimiento' // Necesario para que el accessor calcule la edad
        ])
            ->orderBy('acc', 'asc')
            ->paginate($request->get('per_page', 50)); // Dinámico, por defecto 50

        // 3. Respuesta estructurada
        return response()->json($partners);
    }
}
