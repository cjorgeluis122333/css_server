<?php

namespace App\Service;

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
}
