<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaHistorial extends Model
{
    protected $table = 'cita_historiales';

    protected $fillable = [
        'cita_id', 'user_id', 'estado_anterior',
        'estado_nuevo', 'comentario',
    ];

    public function cita()
    {
        return $this->belongsTo(CitaMedica::class, 'cita_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
