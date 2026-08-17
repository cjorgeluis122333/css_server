<?php

namespace App\Service\photo;

use App\Enum\PartnerCategory;
use App\Models\partners\Partner;
use Illuminate\Http\UploadedFile;

class PhotoService
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png'];

    private const IMAGE_PATH = 'assets/acc';

    private const DNI_FRONT_PATH = 'assets/dni/front';

    private const DNI_BACK_PATH = 'assets/dni/back';

    /**
     * Retorna la URL pública de la foto identificada por cédula,
     * o null si no existe ningún archivo de imagen para esa cédula.
     */
    public function getUrl(string|int $cedula): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            if (file_exists(public_path(self::IMAGE_PATH . "/{$cedula}.{$ext}"))) {
                return asset(self::IMAGE_PATH . "/{$cedula}.{$ext}");
            }
        }

        return null;
    }

    /**
     * Sube (o reemplaza) la foto del socio titular identificado por su número de acción.
     *
     * @return array{url: string}
     *
     * @throws \Exception
     */
    public function uploadPhoto(int $acc, UploadedFile $image): array
    {
        $partner = Partner::query()
            ->where('acc', $acc)
            ->where('categoria', PartnerCategory::TITULAR->value)
            ->first();

        if (! $partner) {
            throw new \Exception('Socio no encontrado.', 404);
        }

        $cedula = (string) $partner->cedula;

        if (empty($cedula) || strlen($cedula) < 5 || ! is_numeric($cedula)) {
            throw new \Exception('Cédula del socio inválida.', 422);
        }

        foreach (self::EXTENSIONS as $ext) {
            $existing = public_path(self::IMAGE_PATH . "/{$cedula}.{$ext}");

            if (file_exists($existing)) {
                unlink($existing);
            }
        }

        $ext = $image->extension();

        $image->move(
            public_path(self::IMAGE_PATH),
            "{$cedula}.{$ext}"
        );

        return [
            'url' => asset(self::IMAGE_PATH . "/{$cedula}.{$ext}")
        ];
    }

    public function getPath(string|int $cedula): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            $path = public_path(self::IMAGE_PATH . "/{$cedula}.{$ext}");

            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Elimina las imágenes del frente y reverso de un DNI.
     *
     * Busca todas las extensiones permitidas y elimina cualquier
     * imagen que exista con la cédula indicada.
     */
    public function deleteDniImages(string|int $cedula): void
    {
        $cedula = (string) $cedula;

        if (! is_numeric($cedula)) {
            throw new \Exception('Cédula inválida.', 422);
        }

        $directories = [
            self::DNI_FRONT_PATH,
            self::DNI_BACK_PATH,
        ];

        foreach ($directories as $directory) {
            foreach (self::EXTENSIONS as $ext) {
                $path = public_path("{$directory}/{$cedula}.{$ext}");

                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }

    /**
     * Guarda las imágenes del frente y reverso del DNI.
     *
     * Antes de guardar elimina cualquier imagen existente
     * para evitar duplicidad.
     *
     * @return array{
     *     cedula: string,
     *     front_url: string,
     *     back_url: string
     * }
     *
     * @throws \Exception
     */
    public function uploadDniImages(
        string|int $cedula,
        UploadedFile $front,
        UploadedFile $back
    ): array {
        $cedula = (string) $cedula;

        if (! is_numeric($cedula)) {
            throw new \Exception('Cédula inválida.', 422);
        }

        if (strlen($cedula) < 5) {
            throw new \Exception('Cédula inválida.', 422);
        }

        // Eliminamos cualquier imagen anterior.
        $this->deleteDniImages($cedula);

        $frontExtension = $front->extension();
        $backExtension = $back->extension();

        $front->move(
            public_path(self::DNI_FRONT_PATH),
            "{$cedula}.{$frontExtension}"
        );

        $back->move(
            public_path(self::DNI_BACK_PATH),
            "{$cedula}.{$backExtension}"
        );

        return [
            'cedula' => $cedula,
            'front_url' => asset(
                self::DNI_FRONT_PATH . "/{$cedula}.{$frontExtension}"
            ),
            'back_url' => asset(
                self::DNI_BACK_PATH . "/{$cedula}.{$backExtension}"
            ),
        ];
    }
}
