<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Service\UserAdminService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;


class UserAdminController extends Controller
{
    use ApiResponse;

    protected UserAdminService $adminService;

    public function __construct(UserAdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function index()
    {
        return User::all();
    }

    /**
     * Actualizar un directivo existente.
     */
    public function update(UserRequest $request, $acc): JsonResponse
    {
        try {
            $user = User::findOrFail($acc);
            $updatedManager = $this->adminService->updateUser($user, $request->validated());
            return $this->successResponse($updatedManager, 'Usuario actualizado con éxito.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('El usuario no se pudo actualizar', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error al intentar actualizar el usuario.', 500);
        }
    }


    /**
     * DELETE /api/families/{id}
     */
    public function destroy($acc)
    {
        try {
            $user = User::findOrFail($acc);
            $this->adminService->deleteUser($user);
            return $this->successResponse(null, "Usuario eliminado correctamente", 410);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Usuario no encontrado', 404);
        } catch (Exception $e) {
            Log::error("Error delete usuario {$acc}: " . $e->getMessage());
            return $this->errorResponse('Error al eliminar usuario', 500);
        }
    }


}
