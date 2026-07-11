<?php

namespace Database\Factories;

use App\Models\CitaMedica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CitaMedicaFactory extends Factory
{
    protected $model = CitaMedica::class;

    public function definition(): array
    {
        return [
            'paciente_id' => User::factory()->asPaciente(),
            'medico_id' => User::factory()->asMedico()->hasMedicoPerfil(),
            'fecha_hora' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'motivo' => fake()->sentence(),
            'estado' => 'pendiente',
        ];
    }

    public function estado(string $estado): static
    {
        return $this->state(fn (array $attributes) => ['estado' => $estado]);
    }

    public function confirmada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => 'confirmada']);
    }

    public function enEspera(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => 'en_espera']);
    }

    public function enConsulta(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => 'en_consulta']);
    }

    public function finalizada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => 'finalizada']);
    }

    public function cancelada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => 'cancelada']);
    }
}
