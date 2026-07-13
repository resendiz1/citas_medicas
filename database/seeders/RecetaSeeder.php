<?php

namespace Database\Seeders;

use App\Models\CitaMedica;
use App\Models\Receta;
use App\Models\RecetaMedicamento;
use Illuminate\Database\Seeder;

class RecetaSeeder extends Seeder
{
    public function run(): void
    {
        $finalizadas = CitaMedica::where('estado', 'finalizada')->with('paciente', 'medico')->get();

        if ($finalizadas->isEmpty()) return;

        $recetasData = [
            [
                'email_paciente' => 'juan.martinez@email.com',
                'fecha_emision' => now()->subDays(10),
                'diagnostico' => 'Infección respiratoria aguda de vías altas de probable origen viral.',
                'indicaciones_generales' => 'Reposo relativo por 48 horas. Aumentar ingesta de líquidos. Evitar cambios bruscos de temperatura.',
                'notas' => 'Paciente con alergia a penicilina (leve). Vigilar evolución en 72 horas.',
                'medicamentos' => [
                    ['medicamento' => 'Paracetamol', 'dosis' => '500 mg', 'frecuencia' => 'Cada 8 horas', 'duracion' => '5 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Tomar después de alimentos'],
                    ['medicamento' => 'Loratadina', 'dosis' => '10 mg', 'frecuencia' => 'Cada 24 horas', 'duracion' => '7 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Una tableta en la noche'],
                    ['medicamento' => 'Ambroxol', 'dosis' => '30 mg', 'frecuencia' => 'Cada 8 horas', 'duracion' => '5 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Para la tos con flemas'],
                ],
            ],
            [
                'email_paciente' => 'maria.lopez@email.com',
                'fecha_emision' => now()->subDays(15),
                'diagnostico' => 'Hipertensión arterial esencial grado 1. Paciente en tratamiento estable.',
                'indicaciones_generales' => 'Continuar con dieta hiposódica. Realizar ejercicio aeróbico 30 min diarios. Control de presión cada semana.',
                'notas' => 'Paciente con DM2 e HTA. Mantener control metabólico estricto.',
                'medicamentos' => [
                    ['medicamento' => 'Enalapril', 'dosis' => '10 mg', 'frecuencia' => 'Cada 12 horas', 'duracion' => '90 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Tomar con el desayuno y la cena'],
                    ['medicamento' => 'Metformina', 'dosis' => '850 mg', 'frecuencia' => 'Cada 12 horas', 'duracion' => '90 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Tomar después de alimentos'],
                ],
            ],
            [
                'email_paciente' => 'pedro.hernandez@email.com',
                'fecha_emision' => now()->subDays(7),
                'diagnostico' => 'Paciente pediátrico sano. Esquema de vacunación completo.',
                'indicaciones_generales' => 'Vigilar reacciones post-vacunación. Administrar antipirético si presenta fiebre >38°C.',
                'notas' => 'Paciente con alergia a frutos secos (grave) e ibuprofeno (moderado). NO administrar ibuprofeno.',
                'medicamentos' => [
                    ['medicamento' => 'Paracetamol solución', 'dosis' => '250 mg (5 ml)', 'frecuencia' => 'Cada 6-8 horas si fiebre', 'duracion' => '3 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Solo si temperatura >38°C'],
                ],
            ],
            [
                'email_paciente' => 'ana.sanchez@email.com',
                'fecha_emision' => now()->subDays(5),
                'diagnostico' => 'Nevus melanocítico benigno en miembro superior izquierdo. Lesión extirpada completamente.',
                'indicaciones_generales' => 'Mantener cura oclusiva por 48 horas. No mojar la herida. Aplicar antibiótico tópico cada 12 horas. Regresar en 7 días para retiro de puntos.',
                'notas' => 'Paciente con alergia a lácteos (moderado). Sin complicaciones postquirúrgicas.',
                'medicamentos' => [
                    ['medicamento' => 'Mupirocina tópica', 'dosis' => 'Aplicar capa fina', 'frecuencia' => 'Cada 12 horas', 'duracion' => '7 días', 'via_administracion' => 'Tópica', 'indicaciones' => 'Sobre la herida después de limpieza'],
                    ['medicamento' => 'Ketorolaco', 'dosis' => '10 mg', 'frecuencia' => 'Cada 8 horas PRN dolor', 'duracion' => '3 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Solo si presenta dolor'],
                ],
            ],
            [
                'email_paciente' => 'roberto.diaz@email.com',
                'fecha_emision' => now()->subDays(20),
                'diagnostico' => 'Control ginecológico anual normal. Citología vaginal sin alteraciones.',
                'indicaciones_generales' => 'Repetir citología en 1 año. Realizar mastografía anual a partir de los 40 años.',
                'notas' => 'Paciente con DM2 e HTA, ambas controladas.',
                'medicamentos' => [
                    ['medicamento' => 'Ácido fólico', 'dosis' => '5 mg', 'frecuencia' => 'Cada 24 horas', 'duracion' => '30 días', 'via_administracion' => 'Oral', 'indicaciones' => 'Una tableta al día'],
                ],
            ],
        ];

        foreach ($recetasData as $data) {
            $cita = $finalizadas->firstWhere('paciente.email', $data['email_paciente']);
            if (!$cita) continue;

            $receta = Receta::create([
                'cita_id' => $cita->id,
                'paciente_id' => $cita->paciente_id,
                'medico_id' => $cita->medico_id,
                'diagnostico' => $data['diagnostico'],
                'indicaciones_generales' => $data['indicaciones_generales'],
                'notas' => $data['notas'],
                'fecha_emision' => $data['fecha_emision'],
            ]);

            foreach ($data['medicamentos'] as $med) {
                RecetaMedicamento::create(array_merge($med, ['receta_id' => $receta->id]));
            }
        }
    }
}
