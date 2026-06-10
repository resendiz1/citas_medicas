<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaMedica extends Model
{
    protected $fillable = [
        'cita_id', 'paciente_id', 'medico_id',
        'motivo_consulta', 'sintomas', 'tiempo_evolucion',
        'presion_arterial', 'temperatura', 'frecuencia_cardiaca',
        'frecuencia_respiratoria', 'saturacion_oxigeno',
        'peso', 'estatura', 'imc',
        'exploracion_fisica', 'observaciones',
        'diagnostico_probable', 'diagnostico_final', 'codigo_cie10',
    ];

    public function cita()
    {
        return $this->belongsTo(CitaMedica::class, 'cita_id');
    }

    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function dolores()
    {
        return $this->hasMany(Dolor::class);
    }
}
