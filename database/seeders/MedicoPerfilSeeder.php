<?php

namespace Database\Seeders;

use App\Models\MedicoPerfil;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicoPerfilSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = [
            [
                'email'              => 'ricardo.mendoza@citas.com',
                'tipo_medico_id'     => 1,
                'cedula_profesional' => 'Céd. Prof. 12345678',
                'universidad'        => 'Universidad Nacional Autónoma de México',
                'experiencia_anios'  => 15,
                'descripcion'        => 'Médico general con amplia experiencia en atención primaria y medicina preventiva.',
                'activo'             => true,
            ],
            [
                'email'              => 'elena.torres@citas.com',
                'tipo_medico_id'     => 2,
                'cedula_profesional' => 'Céd. Prof. 23456789',
                'universidad'        => 'Instituto Politécnico Nacional',
                'experiencia_anios'  => 10,
                'descripcion'        => 'Cardióloga especializada en ecocardiografía y prevención de enfermedades cardiovasculares.',
                'activo'             => true,
            ],
            [
                'email'              => 'miguel.rios@citas.com',
                'tipo_medico_id'     => 3,
                'cedula_profesional' => 'Céd. Prof. 34567890',
                'universidad'        => 'Universidad de Guadalajara',
                'experiencia_anios'  => 8,
                'descripcion'        => 'Pediatra apasionado por la salud infantil y el desarrollo temprano.',
                'activo'             => true,
            ],
            [
                'email'              => 'sofia.vega@citas.com',
                'tipo_medico_id'     => 4,
                'cedula_profesional' => 'Céd. Prof. 45678901',
                'universidad'        => 'Universidad Autónoma de Nuevo León',
                'experiencia_anios'  => 12,
                'descripcion'        => 'Dermatóloga con experiencia en diagnósticos avanzados y tratamientos estéticos.',
                'activo'             => true,
            ],
            [
                'email'              => 'luis.campos@citas.com',
                'tipo_medico_id'     => 5,
                'cedula_profesional' => 'Céd. Prof. 56789012',
                'universidad'        => 'Universidad de Monterrey',
                'experiencia_anios'  => 6,
                'descripcion'        => 'Ginecólogo comprometido con la salud de la mujer en todas las etapas de su vida.',
                'activo'             => false,
            ],
        ];

        foreach ($perfiles as $p) {
            $user = User::where('email', $p['email'])->first();
            if (!$user) continue;

            MedicoPerfil::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tipo_medico_id'     => $p['tipo_medico_id'],
                    'cedula_profesional' => $p['cedula_profesional'],
                    'universidad'        => $p['universidad'],
                    'experiencia_anios'  => $p['experiencia_anios'],
                    'descripcion'        => $p['descripcion'],
                    'activo'             => $p['activo'],
                ]
            );
        }
    }
}
