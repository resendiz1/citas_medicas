<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    protected $fillable = [
        'consulta_medica_id',
        'descripcion',
        'codigo_cie10',
        'tipo',
        'es_principal',
    ];

    public function consultaMedica()
    {
        return $this->belongsTo(ConsultaMedica::class);
    }
}
