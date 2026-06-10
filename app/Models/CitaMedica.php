<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaMedica extends Model
{
    protected $table = 'citas_medicas';

    protected $fillable = [
        'paciente_id', 'medico_id',
        'fecha_hora', 'fecha_reprogramada', 'motivo', 'estado',
        'notas_paciente', 'notas_medico', 'precio_consulta',
        'reprogramacion_rechazada',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'fecha_reprogramada' => 'datetime',
            'reprogramacion_rechazada' => 'datetime',
            'precio_consulta' => 'decimal:2',
        ];
    }

    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class, 'cita_id');
    }

    public function ultimaReceta()
    {
        return $this->hasOne(Receta::class, 'cita_id')->latest('fecha_emision');
    }

    public function historiales()
    {
        return $this->hasMany(CitaHistorial::class, 'cita_id');
    }

    public function consultaMedica()
    {
        return $this->hasOne(ConsultaMedica::class, 'cita_id');
    }
}
