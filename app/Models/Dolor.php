<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dolor extends Model
{
    protected $table = 'dolores';

    protected $fillable = ['consulta_medica_id', 'ubicacion', 'intensidad', 'duracion'];

    public function consultaMedica()
    {
        return $this->belongsTo(ConsultaMedica::class);
    }
}
