<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PamiMedicamento extends Model
{
    protected $table = 'pami_medicamentos';

    protected $fillable = [
        'droga',
        'marca',
        'presentacion',
        'laboratorio',
        'cobertura',
    ];
}
