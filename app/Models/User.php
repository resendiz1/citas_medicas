<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'email', 'password', 'role',
    'fecha_nacimiento', 'telefono', 'direccion', 'observaciones', 'foto_url',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
        ];
    }

    public function medicoPerfil()
    {
        return $this->hasOne(MedicoPerfil::class, 'user_id');
    }

    public function contactosEmergencia()
    {
        return $this->hasMany(ContactoEmergencia::class, 'user_id');
    }

    public function alergias()
    {
        return $this->belongsToMany(Alergia::class, 'user_alergias', 'user_id', 'alergia_id')
            ->withPivot('gravedad', 'observaciones')
            ->withTimestamps();
    }

    public function enfermedadesImportantes()
    {
        return $this->belongsToMany(EnfermedadImportante::class, 'user_enfermedades_importantes', 'user_id', 'enfermedad_importante_id')
            ->withPivot('fecha_diagnostico', 'tratamiento_actual', 'observaciones')
            ->withTimestamps();
    }

    public function citasComoPaciente()
    {
        return $this->hasMany(CitaMedica::class, 'paciente_id');
    }

    public function citasComoMedico()
    {
        return $this->hasMany(CitaMedica::class, 'medico_id');
    }

    public function horarios()
    {
        return $this->hasMany(MedicoHorario::class, 'medico_id');
    }

    public function bloqueos()
    {
        return $this->hasMany(MedicoBloqueo::class, 'medico_id');
    }

    public function esAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function esMedico(): bool
    {
        return $this->role === 'medico';
    }

    public function esPaciente(): bool
    {
        return $this->role === 'paciente';
    }

    public function esRecepcionista(): bool
    {
        return $this->role === 'recepcionista';
    }
}
