<?php

namespace App\Service\photo;

use App\Enum\PartnerCategory;
use App\Models\partners\Partner;
use Illuminate\Http\UploadedFile;

class PhotoService
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png'];

    private const IMAGE_PATH = 'assets/acc';

    /**
     * Retorna la URL pública de la foto identificada por cédula,
     * o null si no existe ningún archivo de imagen para esa cédula.
     */
    public function getUrl(string|int $cedula): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            if (file_exists(public_path(self::IMAGE_PATH."/{$cedula}.{$ext}"))) {
                return asset(self::IMAGE_PATH."/{$cedula}.{$ext}");
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
            $existing = public_path(self::IMAGE_PATH."/{$cedula}.{$ext}");
            if (file_exists($existing)) {
                unlink($existing);
            }
        }

        $ext = $image->extension();
        $image->move(public_path(self::IMAGE_PATH), "{$cedula}.{$ext}");

        return ['url' => asset(self::IMAGE_PATH."/{$cedula}.{$ext}")];
    }
}
