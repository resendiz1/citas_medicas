<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoDocumento extends Model
{
    protected $table = 'medico_documentos';

    protected $fillable = [
        'medico_perfil_id', 'nombre', 'nombre_original', 'ruta_archivo', 'tipo_mime', 'tamano',
    ];

    public function medicoPerfil()
    {
        return $this->belongsTo(MedicoPerfil::class, 'medico_perfil_id');
    }
}
