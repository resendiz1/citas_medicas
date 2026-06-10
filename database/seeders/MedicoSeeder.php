<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MedicoSeeder extends Seeder
{
    public function run(): void
    {
        $medicos = [
            ['name' => 'Dr. Ricardo Mendoza López', 'email' => 'ricardo.mendoza@citas.com', 'password' => 'medico123', 'id_tipo_medico' => 1, 'telefono' => '555-200-1001'],
            ['name' => 'Dra. Elena Torres Ruiz', 'email' => 'elena.torres@citas.com', 'password' => 'medico123', 'id_tipo_medico' => 2, 'telefono' => '555-200-1002'],
            ['name' => 'Dr. Miguel Ángel Ríos', 'email' => 'miguel.rios@citas.com', 'password' => 'medico123', 'id_tipo_medico' => 3, 'telefono' => '555-200-1003'],
            ['name' => 'Dra. Sofía Vega Castillo', 'email' => 'sofia.vega@citas.com', 'password' => 'medico123', 'id_tipo_medico' => 4, 'telefono' => '555-200-1004'],
            ['name' => 'Dr. Luis Fernando Campos', 'email' => 'luis.campos@citas.com', 'password' => 'medico123', 'id_tipo_medico' => 5, 'telefono' => '555-200-1005'],
        ];

        foreach ($medicos as $m) {
            $m['password'] = Hash::make($m['password']);
            $m['role'] = 'medico';
            User::create($m);
        }
    }
}
