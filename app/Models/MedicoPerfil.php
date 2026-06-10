<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoPerfil extends Model
{
    protected $table = 'medico_perfiles';

    protected $fillable = [
        'user_id', 'tipo_medico_id', 'cedula_profesional',
        'universidad', 'experiencia_anios', 'descripcion', 'activo',
        'intervalo_minutos',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipoMedico()
    {
        return $this->belongsTo(TipoMedico::class, 'tipo_medico_id');
    }

    public function documentos()
    {
        return $this->hasMany(MedicoDocumento::class, 'medico_perfil_id');
    }
}
