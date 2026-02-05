<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validación manual para capturar errores y devolverlos en JSON siempre
        $validator = Validator::make($request->all(), [
            'acc' => 'required|integer|unique:users,acc',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Transacción de base de datos para asegurar integridad
        DB::beginTransaction();
        try {
            $user = User::create([
                'acc' => $request->acc,
                'password' => Hash::make($request->password),
            ]);

            // Intentamos cargar el socio de una vez si existe
            $partner = Partner::where('acc', $request->acc)->first();

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario registrado exitosamente',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'socio_info' => $partner
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar el usuario',
                'debug' => $e->getMessage() // Solo para desarrollo
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'acc' => 'required|integer',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = User::where('acc', $request->acc)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales inválidas (Acción o contraseña incorrecta)'
                ], 401);
            }

            // Usamos la relación definida en el modelo si existe, si no, el query manual
            $partner = Partner::where('acc', $user->acc)->first();

            // Opcional: Eliminar tokens viejos para sesión única
            $user->tokens()->delete();

            $token = $user->createToken('login_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'socio_info' => $partner
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el servidor',
                'debug' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revocar el token actual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada y token eliminado'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'No se pudo cerrar sesión'], 500);
        }
    }
}
