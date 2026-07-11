<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaMedicamento extends Model
{
    protected $fillable = [
        'consulta_medica_id',
        'nombre_generico',
        'nombre_comercial',
        'concentracion',
        'presentacion',
        'forma_farmaceutica',
        'dosis',
        'via_administracion',
        'frecuencia',
        'duracion',
        'cantidad_total',
        'indicaciones',
        'incluir_en_receta',
    ];

    public function consultaMedica()
    {
        return $this->belongsTo(ConsultaMedica::class);
    }
}
