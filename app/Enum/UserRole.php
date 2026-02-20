<?php

namespace App\Enum;

enum UserRole: string
{
    case SUPER_ADMIN = 'Usuario Super Administrador';
    case ADMIN = 'Usuario administración';
    case OPERATOR = 'Usuarios operadores';
    case SUPERVISOR = 'Usuarios supervisores';
    case ALLY = 'Usuarios Aliados';
    case HONORARY = 'Usuarios Honorarios';
    case PARTNER = 'Usuarios Socios';

    // Método opcional para obtener todos los valores si los necesitas en un select
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Determina el rol basado en el número de acción (acc)
     */
    public static function fromAcc(int $acc): self
    {
        return match (true) {
            $acc == 1000    => self::SUPER_ADMIN,
            $acc >= 991 && $acc <= 999   => self::ADMIN,
            $acc >= 961 && $acc <= 990  => self::OPERATOR,
            $acc >= 931 && $acc <= 960 => self::SUPERVISOR,
            $acc >= 901 && $acc <= 930 => self::ALLY,
            $acc >= 801 && $acc <= 900 => self::HONORARY,
            default                    => self::PARTNER, // El resto son socios
        };
    }
}
