<?php

namespace App\Enum;

enum DebtMetricType: string
{
    case MENSUAL = 'mensual';
    case TRIMESTRAL = 'trimestral';
    case SEMESTRAL = 'semestral';
}
