<?php

namespace Database\Factories;

use App\Models\TipoMedico;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMedicoFactory extends Factory
{
    protected $model = TipoMedico::class;

    public function definition(): array
    {
        return [
            'nombre_tipo_medico' => fake()->unique()->randomElement([
                'Medicina General','Cardiología','Pediatría','Dermatología','Ginecología',
                'Neurología','Traumatología','Oftalmología','Otorrinolaringología','Psiquiatría',
            ]),
            'descripcion' => fake()->sentence(),
        ];
    }
}
