<?php

namespace Database\Seeders;

use App\Models\MedicoHorario;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicoHorarioSeeder extends Seeder
{
    public function run(): void
    {
        $medicos = User::where('role', 'medico')->pluck('id');

        $horariosPorMedico = [
            1 => [ // Dr. Ricardo Mendoza - Medicina General - L-V 9:00-17:00
                [1, '09:00', '17:00'],
                [2, '09:00', '17:00'],
                [3, '09:00', '17:00'],
                [4, '09:00', '17:00'],
                [5, '09:00', '17:00'],
            ],
            2 => [ // Dra. Elena Torres - Cardiologia - L-V 8:00-14:00
                [1, '08:00', '14:00'],
                [2, '08:00', '14:00'],
                [3, '08:00', '14:00'],
                [4, '08:00', '14:00'],
                [5, '08:00', '14:00'],
            ],
            3 => [ // Dr. Miguel Ángel Ríos - Pediatría - L-V 10:00-18:00
                [1, '10:00', '18:00'],
                [2, '10:00', '18:00'],
                [3, '10:00', '18:00'],
                [4, '10:00', '18:00'],
                [5, '10:00', '18:00'],
            ],
            4 => [ // Dra. Sofía Vega - Dermatología - L-V 11:00-19:00
                [1, '11:00', '19:00'],
                [2, '11:00', '19:00'],
                [3, '11:00', '19:00'],
                [4, '11:00', '19:00'],
                [5, '11:00', '19:00'],
            ],
            5 => [ // Dr. Luis Campos - Ginecología - M-Sab 9:00-15:00 (inactivo)
                [2, '09:00', '15:00'],
                [3, '09:00', '15:00'],
                [4, '09:00', '15:00'],
                [5, '09:00', '15:00'],
                [6, '09:00', '13:00'],
            ],
        ];

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        foreach ($medicos as $index => $medicoId) {
            $medicoNumber = $index + 1;
            $horarios = $horariosPorMedico[$medicoNumber] ?? [];

            foreach ($horarios as $h) {
                MedicoHorario::create([
                    'medico_id' => $medicoId,
                    'dia_semana' => $h[0],
                    'hora_inicio' => $h[1],
                    'hora_fin' => $h[2],
                    'activo' => $medicoNumber !== 5,
                ]);
            }

            $perfil = \App\Models\MedicoPerfil::where('user_id', $medicoId)->first();
            if ($perfil) {
                $perfil->update(['intervalo_minutos' => 30]);
            }
        }
    }
}
