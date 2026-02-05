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
            'cedula' => 'required|int',
            'correo' => 'required|email|unique:users,correo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Verificar si la ACCIÓN ya fue registrada por otro familiar
        if (User::where('acc', $request->acc)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Esta acción ya tiene un usuario registrado. Los demás familiares deben usar esas credenciales.'
            ], 409);
        }

        // 3. VALIDACIÓN DE IDENTIDAD contra la tabla de socios
        // Buscamos un socio que coincida con la ACCIÓN y (Cédula o Correo)
        $partnerMatch = Partner::where('acc', $request->acc)
            ->where(function($query) use ($request) {
                $query->where('cedula', $request->cedula)
                    ->where('correo', $request->correo);
            })->first();

        if (!$partnerMatch) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontró ningún socio en la base de datos que coincida con estos datos para la acción ' . $request->acc
            ], 404);
        }


        // 2. Transacción de base de datos para asegurar integridad
        DB::beginTransaction();
        try {
            $user = User::create([
                'acc' => $request->acc,
                'cedula' => $request->cedula,
                'correo' => $request->correo,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario registrado exitosamente como titular de la acción',
                'access_token' => $token,
                'user' => $user,
                'member_details' => $partnerMatch // Datos del familiar específico que registró
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

            // Buscamos en partners donde la acción coincida Y la cédula coincida con la del usuario
            $partner = Partner::where('acc', $user->acc)
                ->where('cedula', $user->cedula) // Esto asegura que sea el socio correcto de la familia
                ->first();
            // Opcional: Eliminar tokens viejos para sesión única
//            $user->tokens()->delete();

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
