<?php

namespace Database\Seeders;

use App\Models\ContactoEmergencia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PacienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = [
            [
                'name' => 'Juan Carlos Martínez', 'email' => 'juan.martinez@email.com', 'password' => 'paciente123',
                'fecha_nacimiento' => '1990-05-15', 'telefono' => '555-300-1001',
                'direccion' => 'Calle Principal 123, Centro', 'observaciones' => null,
                'alergias' => [1 => 'leve'], 'enfermedades' => [],
                'contacto' => ['nombre_completo' => 'María García López', 'telefono' => '555-100-2001', 'email' => 'maria.garcia@email.com', 'parentesco' => 'Madre'],
            ],
            [
                'name' => 'María Fernanda López', 'email' => 'maria.lopez@email.com', 'password' => 'paciente123',
                'fecha_nacimiento' => '1985-08-22', 'telefono' => '555-300-1002',
                'direccion' => 'Av. Reforma 456, Colonia Juárez', 'observaciones' => 'Paciente con antecedentes de cirugía',
                'alergias' => [], 'enfermedades' => [1 => 'Diagnosticada en 2020, controlada con metformina', 2 => 'Diagnosticada en 2021, controlada con enalapril'],
                'contacto' => ['nombre_completo' => 'Juan Pérez Hernández', 'telefono' => '555-100-2002', 'email' => 'juan.perez@email.com', 'parentesco' => 'Esposo'],
            ],
            [
                'name' => 'Pedro Hernández García', 'email' => 'pedro.hernandez@email.com', 'password' => 'paciente123',
                'fecha_nacimiento' => '1978-11-03', 'telefono' => '555-300-1003',
                'direccion' => 'Blvd. Independencia 789, Las Flores', 'observaciones' => null,
                'alergias' => [3 => 'grave', 8 => 'moderado'], 'enfermedades' => [3 => 'Diagnosticado en 2015, uso de salbutamol PRN'],
                'contacto' => ['nombre_completo' => 'Ana Martínez Ruiz', 'telefono' => '555-100-2003', 'email' => 'ana.martinez@email.com', 'parentesco' => 'Hermana'],
            ],
            [
                'name' => 'Ana Patricia Sánchez', 'email' => 'ana.sanchez@email.com', 'password' => 'paciente123',
                'fecha_nacimiento' => '2000-02-14', 'telefono' => '555-300-1004',
                'direccion' => 'Callejón del Sol 234, Del Valle', 'observaciones' => null,
                'alergias' => [4 => 'moderado'], 'enfermedades' => [],
                'contacto' => ['nombre_completo' => 'Carlos Sánchez Díaz', 'telefono' => '555-100-2004', 'email' => 'carlos.sanchez@email.com', 'parentesco' => 'Padre'],
            ],
            [
                'name' => 'Roberto Díaz Jiménez', 'email' => 'roberto.diaz@email.com', 'password' => 'paciente123',
                'fecha_nacimiento' => '1965-07-30', 'telefono' => '555-300-1005',
                'direccion' => 'Av. Universidad 567, Jardines', 'observaciones' => null,
                'alergias' => [5 => 'leve'], 'enfermedades' => [1 => 'Diagnosticado en 2018, controlado con dieta', 2 => 'Diagnosticado en 2019, controlado con losartán'],
                'contacto' => ['nombre_completo' => 'Laura Ramírez Morales', 'telefono' => '555-100-2005', 'email' => 'laura.ramirez@email.com', 'parentesco' => 'Hija'],
            ],
        ];

        foreach ($pacientes as $p) {
            $alergias = $p['alergias'];
            $enfermedades = $p['enfermedades'];
            $contacto = $p['contacto'];
            unset($p['alergias'], $p['enfermedades'], $p['contacto']);

            $p['password'] = Hash::make($p['password']);
            $p['role'] = 'paciente';

            $user = User::create($p);

            foreach ($alergias as $alergiaId => $gravedad) {
                DB::table('user_alergias')->insert([
                    'user_id' => $user->id,
                    'alergia_id' => $alergiaId,
                    'gravedad' => $gravedad,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($enfermedades as $enfId => $observaciones) {
                DB::table('user_enfermedades_importantes')->insert([
                    'user_id' => $user->id,
                    'enfermedad_importante_id' => $enfId,
                    'observaciones' => $observaciones,
                    'fecha_diagnostico' => now()->subYears(rand(2, 8))->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            ContactoEmergencia::create(array_merge($contacto, ['user_id' => $user->id]));
        }
    }
}
