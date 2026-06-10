<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaDocumento extends Model
{
    protected $table = 'receta_documentos';

    protected $fillable = [
        'receta_id', 'nombre_original', 'ruta_archivo', 'tipo_mime', 'tamano',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }
}
