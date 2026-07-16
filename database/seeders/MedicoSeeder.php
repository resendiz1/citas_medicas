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
            ['name' => 'Dr. Ricardo Mendoza López', 'email' => 'ricardo.mendoza@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1001', 'fecha_nacimiento' => '1975-03-10'],
            ['name' => 'Dra. Elena Torres Ruiz', 'email' => 'elena.torres@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1002', 'fecha_nacimiento' => '1980-07-22'],
            ['name' => 'Dr. Miguel Ángel Ríos', 'email' => 'miguel.rios@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1003', 'fecha_nacimiento' => '1985-11-05'],
            ['name' => 'Dra. Sofía Vega Castillo', 'email' => 'sofia.vega@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1004', 'fecha_nacimiento' => '1982-02-18'],
            ['name' => 'Dr. Luis Fernando Campos', 'email' => 'luis.campos@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1005', 'fecha_nacimiento' => '1990-09-30'],
            ['name' => 'Dr. Javier Ortiz Vega', 'email' => 'javier.ortiz@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1006', 'fecha_nacimiento' => '1983-06-14'],
            ['name' => 'Dra. Carmen Reyes Luna', 'email' => 'carmen.reyes@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1007', 'fecha_nacimiento' => '1988-12-01'],
            ['name' => 'Dr. Andrés Morales Peña', 'email' => 'andres.morales@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1008', 'fecha_nacimiento' => '1979-04-25'],
            ['name' => 'Dra. Patricia Delgado Ruiz', 'email' => 'patricia.delgado@citas.com', 'password' => 'medico123', 'telefono' => '555-200-1009', 'fecha_nacimiento' => '1992-08-19'],
        ];

        foreach ($medicos as $m) {
            $m['password'] = Hash::make($m['password']);
            $m['role'] = 'medico';
            User::create($m);
        }
    }
}
