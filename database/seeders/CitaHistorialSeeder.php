<?php

namespace Database\Seeders;

use App\Models\CitaMedica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitaHistorialSeeder extends Seeder
{
    public function run(): void
    {
        $citas = CitaMedica::all();

        foreach ($citas as $cita) {
            $historial = [];

            switch ($cita->estado) {
                case 'pendiente':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    break;

                case 'confirmada':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'confirmada', 'created_at' => $cita->created_at->copy()->addMinutes(30), 'updated_at' => $cita->created_at->copy()->addMinutes(30)];
                    break;

                case 'en_espera':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'confirmada', 'created_at' => $cita->created_at->copy()->addMinutes(30), 'updated_at' => $cita->created_at->copy()->addMinutes(30)];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'confirmada', 'estado_nuevo' => 'en_espera', 'created_at' => $cita->fecha_hora->copy()->subMinutes(10), 'updated_at' => $cita->fecha_hora->copy()->subMinutes(10)];
                    break;

                case 'en_consulta':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'confirmada', 'created_at' => $cita->created_at->copy()->addMinutes(30), 'updated_at' => $cita->created_at->copy()->addMinutes(30)];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'confirmada', 'estado_nuevo' => 'en_espera', 'created_at' => $cita->fecha_hora->copy()->subMinutes(10), 'updated_at' => $cita->fecha_hora->copy()->subMinutes(10)];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'en_espera', 'estado_nuevo' => 'en_consulta', 'created_at' => $cita->fecha_hora, 'updated_at' => $cita->fecha_hora];
                    break;

                case 'finalizada':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'confirmada', 'created_at' => $cita->created_at->copy()->addMinutes(30), 'updated_at' => $cita->created_at->copy()->addMinutes(30)];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'confirmada', 'estado_nuevo' => 'en_espera', 'created_at' => $cita->fecha_hora->copy()->subMinutes(10), 'updated_at' => $cita->fecha_hora->copy()->subMinutes(10)];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'en_espera', 'estado_nuevo' => 'en_consulta', 'created_at' => $cita->fecha_hora, 'updated_at' => $cita->fecha_hora];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'en_consulta', 'estado_nuevo' => 'finalizada', 'created_at' => $cita->fecha_hora->copy()->addMinutes(30), 'updated_at' => $cita->fecha_hora->copy()->addMinutes(30)];
                    break;

                case 'cancelada':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'cancelada', 'created_at' => $cita->fecha_hora->copy()->subDays(1), 'updated_at' => $cita->fecha_hora->copy()->subDays(1)];
                    break;

                case 'no_asistio':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'confirmada', 'created_at' => $cita->created_at->copy()->addMinutes(30), 'updated_at' => $cita->created_at->copy()->addMinutes(30)];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'confirmada', 'estado_nuevo' => 'no_asistio', 'created_at' => $cita->fecha_hora->copy()->addHours(1), 'updated_at' => $cita->fecha_hora->copy()->addHours(1)];
                    break;

                case 'reprogramada':
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->paciente_id, 'estado_anterior' => null, 'estado_nuevo' => 'pendiente', 'created_at' => $cita->created_at, 'updated_at' => $cita->created_at];
                    $historial[] = ['cita_id' => $cita->id, 'user_id' => $cita->medico_id, 'estado_anterior' => 'pendiente', 'estado_nuevo' => 'reprogramada', 'created_at' => $cita->fecha_hora->copy()->subDays(2), 'updated_at' => $cita->fecha_hora->copy()->subDays(2)];
                    break;
            }

            if (!empty($historial)) {
                DB::table('cita_historiales')->insert($historial);
            }
        }
    }
}
