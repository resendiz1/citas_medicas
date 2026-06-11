<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = [
        'cita_id', 'user_id', 'mensaje',
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
