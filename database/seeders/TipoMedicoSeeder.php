<?php

namespace Database\Seeders;

use App\Models\TipoMedico;
use Illuminate\Database\Seeder;

class TipoMedicoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Medicina General',
            'Cardiología',
            'Pediatría',
            'Dermatología',
            'Ginecología',
            'Neurología',
            'Traumatología',
            'Oftalmología',
            'Otorrinolaringología',
            'Psiquiatría',
        ];

        foreach ($tipos as $tipo) {
            TipoMedico::create(['nombre_tipo_medico' => $tipo]);
        }
    }
}
