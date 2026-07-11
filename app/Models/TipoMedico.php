<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoMedico extends Model
{
    use HasFactory;
    protected $table = 'tipo_medicos';

    protected $fillable = ['nombre_tipo_medico', 'descripcion'];

    public function medicosPerfiles()
    {
        return $this->hasMany(MedicoPerfil::class, 'tipo_medico_id');
    }
}
