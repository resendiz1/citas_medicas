<?php

namespace Database\Seeders;

use App\Models\MedicoBloqueo;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedicoBloqueoSeeder extends Seeder
{
    public function run(): void
    {
        $medicos = User::where('role', 'medico')->pluck('id', 'email');

        $bloqueos = [
            ['email' => 'ricardo.mendoza@citas.com', 'fecha_inicio' => '2026-07-20', 'fecha_fin' => '2026-07-25', 'motivo' => 'Vacaciones familiares'],
            ['email' => 'ricardo.mendoza@citas.com', 'fecha_inicio' => '2026-08-15', 'fecha_fin' => '2026-08-15', 'motivo' => 'Curso de actualización médica'],
            ['email' => 'elena.torres@citas.com', 'fecha_inicio' => '2026-07-28', 'fecha_fin' => '2026-07-30', 'motivo' => 'Congreso de Cardiología'],
            ['email' => 'miguel.rios@citas.com', 'fecha_inicio' => '2026-08-01', 'fecha_fin' => '2026-08-10', 'motivo' => 'Vacaciones'],
        ];

        foreach ($bloqueos as $b) {
            $medicoId = $medicos[$b['email']] ?? null;
            if (!$medicoId) continue;

            MedicoBloqueo::create([
                'medico_id' => $medicoId,
                'fecha_inicio' => $b['fecha_inicio'] . ' 00:00:00',
                'fecha_fin' => $b['fecha_fin'] . ' 23:59:00',
                'motivo' => $b['motivo'],
            ]);
        }
    }
}
