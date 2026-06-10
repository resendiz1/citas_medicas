<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoHorario extends Model
{
    protected $table = 'medico_horarios';

    protected $fillable = [
        'medico_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'activo',
    ];

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
}
