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

        if (empty($medicos) || empty($pacientes)) return;

        $now = now();

        $citas = [
            // Future citas - pendiente
            ['paciente_id' => $pacientes[0], 'medico_id' => $medicos[0], 'fecha_hora' => $now->copy()->addDays(1)->setTime(9, 0), 'motivo' => 'Consulta general por dolor de cabeza persistente', 'estado' => 'pendiente', 'precio_consulta' => 500],
            ['paciente_id' => $pacientes[1], 'medico_id' => $medicos[2], 'fecha_hora' => $now->copy()->addDays(2)->setTime(10, 30), 'motivo' => 'Dolor abdominal recurrente', 'estado' => 'pendiente', 'precio_consulta' => 450],
            ['paciente_id' => $pacientes[2], 'medico_id' => $medicos[0], 'fecha_hora' => $now->copy()->addDays(3)->setTime(11, 0), 'motivo' => 'Control de rutina y revisión de análisis', 'estado' => 'pendiente', 'precio_consulta' => 500],
            ['paciente_id' => $pacientes[3], 'medico_id' => $medicos[3], 'fecha_hora' => $now->copy()->addDays(4)->setTime(15, 0), 'motivo' => 'Evaluación de lunar sospechoso en brazo izquierdo', 'estado' => 'pendiente', 'precio_consulta' => 600],
            ['paciente_id' => $pacientes[4], 'medico_id' => $medicos[3], 'fecha_hora' => $now->copy()->addDays(5)->setTime(12, 0), 'motivo' => 'Revisión dermatológica anual', 'estado' => 'pendiente', 'precio_consulta' => 600],

            // Future citas - confirmada
            ['paciente_id' => $pacientes[0], 'medico_id' => $medicos[1], 'fecha_hora' => $now->copy()->addDays(1)->setTime(8, 0), 'motivo' => 'Revisión cardiológica de rutina', 'estado' => 'confirmada', 'precio_consulta' => 800],
            ['paciente_id' => $pacientes[2], 'medico_id' => $medicos[3], 'fecha_hora' => $now->copy()->addDays(2)->setTime(14, 0), 'motivo' => 'Consulta por dermatitis atópica', 'estado' => 'confirmada', 'precio_consulta' => 600],
            ['paciente_id' => $pacientes[4], 'medico_id' => $medicos[4], 'fecha_hora' => $now->copy()->addDays(6)->setTime(9, 30), 'motivo' => 'Control ginecológico anual', 'estado' => 'confirmada', 'precio_consulta' => 700],

            // Today citas - en_espera / en_consulta
            ['paciente_id' => $pacientes[1], 'medico_id' => $medicos[1], 'fecha_hora' => $now->copy()->setTime(9, 0), 'motivo' => 'Dolor en el pecho - evaluación', 'estado' => 'en_espera', 'precio_consulta' => 800],
            ['paciente_id' => $pacientes[3], 'medico_id' => $medicos[2], 'fecha_hora' => $now->copy()->setTime(10, 0), 'motivo' => 'Fiebre recurrente en niño', 'estado' => 'en_consulta', 'precio_consulta' => 450],

            // Past citas - finalized/completed
            ['paciente_id' => $pacientes[0], 'medico_id' => $medicos[0], 'fecha_hora' => $now->copy()->subDays(10)->setTime(9, 0), 'motivo' => 'Infección respiratoria aguda', 'estado' => 'finalizada', 'precio_consulta' => 500],
            ['paciente_id' => $pacientes[1], 'medico_id' => $medicos[1], 'fecha_hora' => $now->copy()->subDays(15)->setTime(11, 0), 'motivo' => 'Control cardiológico trimestral', 'estado' => 'finalizada', 'precio_consulta' => 800],
            ['paciente_id' => $pacientes[2], 'medico_id' => $medicos[2], 'fecha_hora' => $now->copy()->subDays(7)->setTime(8, 30), 'motivo' => 'Vacunación hexavalente', 'estado' => 'finalizada', 'precio_consulta' => 350],
            ['paciente_id' => $pacientes[3], 'medico_id' => $medicos[3], 'fecha_hora' => $now->copy()->subDays(5)->setTime(16, 0), 'motivo' => 'Extracción de lunar beningo', 'estado' => 'finalizada', 'precio_consulta' => 1200],
            ['paciente_id' => $pacientes[4], 'medico_id' => $medicos[4], 'fecha_hora' => $now->copy()->subDays(20)->setTime(10, 0), 'motivo' => 'Consulta ginecológica de rutina', 'estado' => 'finalizada', 'precio_consulta' => 700],

            // Past citas - cancelada / no_asistio / reprogramada
            ['paciente_id' => $pacientes[2], 'medico_id' => $medicos[0], 'fecha_hora' => $now->copy()->subDays(3)->setTime(9, 0), 'motivo' => 'Dolor lumbar crónico', 'estado' => 'cancelada', 'precio_consulta' => null],
            ['paciente_id' => $pacientes[0], 'medico_id' => $medicos[2], 'fecha_hora' => $now->copy()->subDays(2)->setTime(14, 0), 'motivo' => 'Consulta pediátrica de seguimiento', 'estado' => 'no_asistio', 'precio_consulta' => null],
            ['paciente_id' => $pacientes[4], 'medico_id' => $medicos[0], 'fecha_hora' => $now->copy()->subDays(1)->setTime(11, 0), 'motivo' => 'Resultados de laboratorio', 'estado' => 'reprogramada', 'precio_consulta' => null],
        ];

        foreach ($citas as $c) {
            CitaMedica::create($c);
        }
    }
}
