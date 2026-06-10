<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            ['name' => 'Juan Carlos Martínez', 'email' => 'juan.martinez@email.com', 'password' => 'paciente123', 'fecha_nacimiento' => '1990-05-15', 'telefono' => '555-300-1001', 'direccion' => 'Calle Principal 123, Centro', 'observaciones' => null, 'id_contacto_emergencia' => 1, 'id_alergias' => 1, 'id_enfermedades_importantes' => null],
            ['name' => 'María Fernanda López', 'email' => 'maria.lopez@email.com', 'password' => 'paciente123', 'fecha_nacimiento' => '1985-08-22', 'telefono' => '555-300-1002', 'direccion' => 'Av. Reforma 456, Colonia Juárez', 'observaciones' => 'Paciente con antecedentes de cirugía', 'id_contacto_emergencia' => 2, 'id_alergias' => null, 'id_enfermedades_importantes' => 2],
            ['name' => 'Pedro Hernández García', 'email' => 'pedro.hernandez@email.com', 'password' => 'paciente123', 'fecha_nacimiento' => '1978-11-03', 'telefono' => '555-300-1003', 'direccion' => 'Blvd. Independencia 789, Las Flores', 'observaciones' => null, 'id_contacto_emergencia' => 3, 'id_alergias' => 3, 'id_enfermedades_importantes' => 1],
            ['name' => 'Ana Patricia Sánchez', 'email' => 'ana.sanchez@email.com', 'password' => 'paciente123', 'fecha_nacimiento' => '2000-02-14', 'telefono' => '555-300-1004', 'direccion' => 'Callejón del Sol 234, Del Valle', 'observaciones' => 'Menor de edad, autorización de tutores', 'id_contacto_emergencia' => 1, 'id_alergias' => null, 'id_enfermedades_importantes' => null],
            ['name' => 'Roberto Díaz Jiménez', 'email' => 'roberto.diaz@email.com', 'password' => 'paciente123', 'fecha_nacimiento' => '1965-07-30', 'telefono' => '555-300-1005', 'direccion' => 'Av. Universidad 567, Jardines', 'observaciones' => null, 'id_contacto_emergencia' => 4, 'id_alergias' => 5, 'id_enfermedades_importantes' => 3],
        ];

        foreach ($pacientes as $p) {
            $p['password'] = Hash::make($p['password']);
            $p['role'] = 'paciente';
            User::create($p);
        }
    }
}
