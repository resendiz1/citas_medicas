<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultaMedica extends Model
{
    protected $fillable = [
        'cita_id', 'paciente_id', 'medico_id',
        'motivo_consulta', 'sintomas', 'tiempo_evolucion',
        'fecha_inicio_sintomas', 'forma_inicio', 'evolucion', 'descripcion_padecimiento',
        'presion_arterial', 'temperatura', 'frecuencia_cardiaca',
        'frecuencia_respiratoria', 'saturacion_oxigeno',
        'peso', 'estatura', 'imc',
        'exploracion_fisica', 'observaciones',
        'exploracion_sin_hallazgos',
        'exploracion_estado_general', 'exploracion_cabeza_cuello',
        'exploracion_respiratorio', 'exploracion_cardiovascular',
        'exploracion_abdomen', 'exploracion_extremidades',
        'exploracion_neurologico', 'exploracion_hallazgos',
        'diagnostico_probable', 'diagnostico_final', 'codigo_cie10',
        'diagnostico_diferencial', 'pronostico', 'resumen_clinico',
        'plan_medicamentos', 'plan_estudios', 'plan_procedimientos',
        'plan_recomendaciones', 'plan_signos_alarma', 'plan_referencia',
        'plan_seguimiento_fecha', 'plan_incapacidad',
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

    public function sintomasRegistrados()
    {
        return $this->hasMany(Sintoma::class);
    }

    public function diagnosticos()
    {
        return $this->hasMany(Diagnostico::class);
    }

    public function diagnosticoPrincipal()
    {
        return $this->hasOne(Diagnostico::class)->where('es_principal', true);
    }

    public function medicamentos()
    {
        return $this->hasMany(ConsultaMedicamento::class);
    }
}
