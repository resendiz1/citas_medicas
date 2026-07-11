<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaMedicamento extends Model
{
    protected $table = 'receta_medicamentos';

    protected $fillable = [
        'receta_id', 'medicamento',
        'nombre_generico', 'nombre_comercial', 'concentracion',
        'presentacion', 'forma_farmaceutica',
        'dosis', 'via_administracion', 'frecuencia', 'duracion', 'cantidad_total',
        'indicaciones', 'incluir_en_receta',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
}
