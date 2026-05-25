<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PartnerPhotoController extends Controller
{
    use ApiResponse;

    /**
     * Retorna la URL pública de la foto del socio titular identificado por su cédula.
     */
    public function show(string $cedula): JsonResponse
    {
        if (! is_numeric($cedula)) {
            return $this->errorResponse('Foto no encontrada.', 404);
        }

        $extensions = ['jpg', 'jpeg', 'png'];

        foreach ($extensions as $ext) {
            if (file_exists(public_path("assets/acc/{$cedula}.{$ext}"))) {
                return $this->successResponse(
                    ['url' => asset("assets/acc/{$cedula}.{$ext}")],
                    'Foto encontrada.'
                );
            }
        }

        return $this->errorResponse('Foto no encontrada.', 404);
    }
}
