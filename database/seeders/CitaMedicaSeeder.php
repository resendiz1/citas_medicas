<?php

namespace Database\Seeders;

use App\Models\CitaMedica;
use App\Models\User;
use Illuminate\Database\Seeder;

class CitaMedicaSeeder extends Seeder
{
    public function run(): void
    {
        $medicos = User::where('role', 'medico')->pluck('id')->toArray();
        $pacientes = User::where('role', 'paciente')->pluck('id')->toArray();

        if (empty($medicos) || empty($pacientes)) {
            return;
        }

        $citas = [
            ['id_paciente' => $pacientes[0], 'id_medico' => $medicos[0], 'fecha_hora' => '2026-06-10 09:00:00', 'motivo' => 'Consulta general por dolor de cabeza persistente', 'estado' => 'pendiente'],
            ['id_paciente' => $pacientes[1], 'id_medico' => $medicos[1], 'fecha_hora' => '2026-06-10 10:30:00', 'motivo' => 'Revisión cardiológica de rutina', 'estado' => 'confirmada'],
            ['id_paciente' => $pacientes[2], 'id_medico' => $medicos[2], 'fecha_hora' => '2026-06-11 08:00:00', 'motivo' => 'Vacunación programada', 'estado' => 'pendiente'],
            ['id_paciente' => $pacientes[3], 'id_medico' => $medicos[3], 'fecha_hora' => '2026-06-11 11:00:00', 'motivo' => 'Evaluación de lunar sospechoso en brazo izquierdo', 'estado' => 'pendiente'],
            ['id_paciente' => $pacientes[4], 'id_medico' => $medicos[4], 'fecha_hora' => '2026-06-12 09:30:00', 'motivo' => 'Control ginecológico anual', 'estado' => 'confirmada'],
            ['id_paciente' => $pacientes[0], 'id_medico' => $medicos[0], 'fecha_hora' => '2026-06-15 10:00:00', 'motivo' => 'Resultados de análisis de sangre', 'estado' => 'pendiente'],
            ['id_paciente' => $pacientes[1], 'id_medico' => $medicos[2], 'fecha_hora' => '2026-05-28 08:30:00', 'motivo' => 'Dolor abdominal recurrente', 'estado' => 'completada'],
            ['id_paciente' => $pacientes[3], 'id_medico' => $medicos[1], 'fecha_hora' => '2026-05-25 11:00:00', 'motivo' => 'Dolor en el pecho', 'estado' => 'cancelada'],
        ];

        foreach ($citas as $c) {
            CitaMedica::create($c);
        }
    }
}
