<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicamentoCatalogo extends Model
{
    use HasFactory;

    protected $table = 'medicamentos_catalogo';

    protected $fillable = [
        'laboratorio_titular',
        'numero_certificado',
        'nombre_comercial',
        'nombre_generico',
        'concentracion',
        'forma_farmaceutica',
        'presentacion',
    ];
}
