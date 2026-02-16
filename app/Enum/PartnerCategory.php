<?php

namespace App\Enum;

enum PartnerCategory: string
{
    case TITULAR = 'titular';
    case FAMILIAR = 'familiar';

    public function label(): string
    {
        return match($this) {
            self::TITULAR => 'Socio Titular',
            self::FAMILIAR => 'Familiar Dependiente',
        };
    }

}
