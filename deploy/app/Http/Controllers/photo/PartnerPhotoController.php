<?php

namespace App\Http\Controllers\photo;


use App\Http\Controllers\Controller;
use App\Http\Requests\photo\DniPhotoRequest;
use App\Http\Requests\photo\PartnerPhotoRequest;
use App\Service\photo\PhotoService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Return the image
     */

    public function image(string $cedula): BinaryFileResponse
    {
        if (! is_numeric($cedula)) {
            abort(404);
        }

        $path = $this->photoService->getPath($cedula);

        if (! $path) {
            abort(404);
        }

        return response()->file($path, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => '*',
        ]);
    }

    //-------------------------------DNI

    /**
     * Guarda las imágenes del frente y reverso del DNI.
     */
    public function storeDni(
        DniPhotoRequest $request,
        string $cedula
    ): JsonResponse {
        try {
            $result = $this->photoService->uploadDniImages(
                $cedula,
                $request->file('front'),
                $request->file('back')
            );

            return $this->successResponse(
                $result,
                'Imágenes del DNI guardadas correctamente.'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }



    /**
     * Elimina las imágenes del frente y reverso del DNI.
     */
    public function deleteDni(string $cedula): JsonResponse
    {
        try {
            $this->photoService->deleteDniImages($cedula);

            return $this->successResponse(
                null,
                'Imágenes del DNI eliminadas correctamente.'
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }


    /**
     * Retorna las URLs del frente y reverso del DNI.
     */
    public function dni(string $cedula): JsonResponse
    {
        try {
            $result = $this->photoService->getDniUrls($cedula);

            return $this->successResponse(
                $result,
                'Imágenes del DNI encontradas correctamente.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }
}
