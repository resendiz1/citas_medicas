<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table = 'recetas';

    protected $fillable = [
        'cita_id', 'paciente_id', 'medico_id',
        'diagnostico', 'indicaciones_generales',
        'notas', 'fecha_emision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
        ];
    }

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

    public function medicamentos()
    {
        return $this->hasMany(RecetaMedicamento::class, 'receta_id');
    }

    public function documentos()
    {
        return $this->hasMany(RecetaDocumento::class, 'receta_id');
    }
}
