<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaMedicamento extends Model
{
    protected $table = 'receta_medicamentos';

    protected $fillable = [
        'receta_id', 'medicamento', 'dosis',
        'frecuencia', 'duracion', 'indicaciones',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }
}
