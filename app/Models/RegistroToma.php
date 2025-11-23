<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroToma extends Model
{
    use HasFactory;

    // ⬇️ AGREGAR ESTA LÍNEA
    protected $table = 'registros_tomas';

    protected $fillable = [
        'user_id',
        'medicamento_id',
        'tomado',
        'fecha_hora',
        'notas',
    ];

    protected $casts = [
        'tomado' => 'boolean',
        'fecha_hora' => 'datetime',
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con el medicamento
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    // Scope para filtrar por rango de fechas
    public function scopeFechasEntre($query, $inicio, $fin)
    {
        return $query->whereBetween('fecha_hora', [$inicio, $fin]);
    }

    // Scope para filtrar por usuario
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope para filtrar solo tomados
    public function scopeTomados($query)
    {
        return $query->where('tomado', true);
    }

    // Scope para filtrar no tomados
    public function scopeNoTomados($query)
    {
        return $query->where('tomado', false);
    }
}