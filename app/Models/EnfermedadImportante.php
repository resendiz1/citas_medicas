<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnfermedadImportante extends Model
{
    protected $table = 'enfermedades_importantes';

    protected $fillable = ['nombre', 'descripcion'];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'user_enfermedades_importantes', 'enfermedad_importante_id', 'user_id')
            ->withPivot('fecha_diagnostico', 'tratamiento_actual', 'observaciones')
            ->withTimestamps();
    }
}
