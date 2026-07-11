<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sintoma extends Model
{
    protected $fillable = [
        'consulta_medica_id',
        'nombre',
        'ubicacion',
        'intensidad_categoria',
        'inicio',
        'duracion',
        'frecuencia',
        'observaciones',
    ];

    public function consultaMedica()
    {
        return $this->belongsTo(ConsultaMedica::class);
    }
}
