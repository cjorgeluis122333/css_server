<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hall extends Model
{

    // Match the exact table name from the migration
    protected $table = '0cc_salones_precios';

    // Set the custom primary key
    protected $primaryKey = 'ind';

    // Disable timestamps since they are not in the migration
    public $timestamps = false;

    // Mass assignable attributes matching your exact column names
    protected $fillable = [
        'salon',
        'socio',
        'no_socio'
    ];

}
