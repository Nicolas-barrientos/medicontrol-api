<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'presentacion',
        'dosis',
        'frecuencia',
        'via',
        'stock',
        'hora_recordatorio',  // ⬅️ AGREGAR ESTE
        'inicio',
        'fin',
        'notas',
        'imagen',  // ⬅️ AGREGAR ESTE
    ];

    protected $casts = [
        'inicio' => 'date',
        'fin' => 'date',
        'stock' => 'integer',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relación con los registros de tomas
     */
    public function registrosTomas()
    {
        return $this->hasMany(RegistroToma::class);
    }

    /**
     * Obtener la URL completa de la imagen
     */
    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
    }

    /**
     * Scope para medicamentos activos
     */
    public function scopeActivos($query)
    {
        return $query->where(function($q) {
            $q->whereNull('fin')
              ->orWhere('fin', '>=', now());
        });
    }

    /**
     * Verificar si el medicamento tiene stock bajo
     */
    public function tieneStockBajo()
    {
        return $this->stock <= 5;
    }
}