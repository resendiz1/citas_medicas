<?php

namespace Database\Factories;

use App\Models\MedicoPerfil;
use App\Models\TipoMedico;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicoPerfilFactory extends Factory
{
    protected $model = MedicoPerfil::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->asMedico(),
            'tipo_medico_id' => TipoMedico::factory(),
            'cedula_profesional' => fake()->numerify('##########'),
            'universidad' => fake()->company() . ' University',
            'experiencia_anios' => fake()->numberBetween(1, 30),
            'descripcion' => fake()->sentence(),
            'activo' => true,
            'aprobado' => true,
            'intervalo_minutos' => 30,
        ];
    }

    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => ['aprobado' => false]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['activo' => false]);
    }
}
