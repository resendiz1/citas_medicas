<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEnfermedadImportante extends Model
{
    protected $table = 'user_enfermedades_importantes';

    protected $fillable = [
        'user_id', 'enfermedad_importante_id',
        'fecha_diagnostico', 'tratamiento_actual', 'observaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enfermedadImportante()
    {
        return $this->belongsTo(EnfermedadImportante::class);
    }
}
