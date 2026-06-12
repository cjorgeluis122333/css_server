<?php

namespace App\Http\Controllers\photo;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerPhotoRequest;
use App\Service\photo\PhotoService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PartnerPhotoController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PhotoService $photoService
    ) {}

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

    /**
     * Sube o reemplaza la foto del socio titular identificado por su número de acción.
     */
    public function store(PartnerPhotoRequest $request, int $acc): JsonResponse
    {
        try {
            $result = $this->photoService->uploadPhoto($acc, $request->file('image'));

            return $this->successResponse($result, 'Foto actualizada correctamente.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
