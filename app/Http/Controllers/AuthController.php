<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'acc' => 'required|integer|unique:users,acc',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Creamos el usuario que servirá para el login
        $user = User::create([
            'acc' => $request->acc,
            'password' => Hash::make($request->password),
        ]);

        // Buscamos al socio relacionado para devolver su info
        $partner = Partner::where('acc', $request->acc)->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user,
            'socio_info' => $partner // El frontend ya tiene los datos del socio aquí
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'acc' => 'required|integer',
            'password' => 'required|string',
        ]);

        $user = User::where('acc', $request->acc)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Acción o contraseña incorrectos'], 401);
        }

        // Buscamos los datos del socio para que el Frontend los tenga de una vez
        $partner = Partner::where('acc', $user->acc)->first();

        $token = $user->createToken('login_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'socio_info' => $partner
        ]);
    }

    public function logout(Request $request)
    {
        // Elimina el token que se está usando actualmente
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

}
