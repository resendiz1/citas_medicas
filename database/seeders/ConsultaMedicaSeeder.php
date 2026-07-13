<?php

namespace Database\Seeders;

use App\Models\CitaMedica;
use App\Models\ConsultaMedica;
use App\Models\Dolor;
use App\Models\ConsultaMedicamento;
use Illuminate\Database\Seeder;

class ConsultaMedicaSeeder extends Seeder
{
    public function run(): void
    {
        $finalizadas = CitaMedica::where('estado', 'finalizada')->with('paciente', 'medico')->get();

        if ($finalizadas->isEmpty()) return;

        $consultasData = [
            [
                'email_paciente' => 'juan.martinez@email.com',
                'motivo_consulta' => 'Paciente presenta dolor de garganta, tos productiva y fiebre de 38.5°C desde hace 3 días.',
                'sintomas' => 'Fiebre, tos con flemas verdes, congestión nasal, dolor de garganta, malestar general.',
                'fecha_inicio_sintomas' => now()->subDays(13),
                'evolucion' => 'Progresivo - inició con dolor de garganta leve y ha ido empeorando',
                'descripcion_padecimiento' => 'Cuadro de 3 días de evolución caracterizado por odinofagia, tos productiva con expectoración verdosa, rinorrea y fiebre cuantificada hasta 38.5°C. Refiere cefalea frontal y malestar general.',
                'presion_arterial' => '120/80',
                'temperatura' => 38.5,
                'frecuencia_cardiaca' => 88,
                'frecuencia_respiratoria' => 20,
                'saturacion_oxigeno' => 97,
                'peso' => 78.5,
                'estatura' => 1.75,
                'imc' => 25.6,
                'exploracion_fisica' => 'Orofaringe hiperémica con exudados amigdalinos bilaterales. Campos pulmonares con estertores crepitantes en base derecha. Resto normal.',
                'diagnostico_probable' => 'Faringoamigdalitis aguda bacteriana vs neumonía atípica.',
                'codigo_cie10' => 'J02.0',
                'plan_recomendaciones' => 'Reposo por 48h, abundantes líquidos, control de temperatura cada 6h.',
                'plan_signos_alarma' => 'Regresar si fiebre >39°C persistente, dificultad respiratoria, o dolor torácico.',
                'dolores' => [
                    ['ubicacion' => 'Garganta', 'intensidad' => '7/10', 'duracion' => '3 días'],
                    ['ubicacion' => 'Cabeza (frontal)', 'intensidad' => '5/10', 'duracion' => '2 días'],
                ],
            ],
            [
                'email_paciente' => 'maria.lopez@email.com',
                'motivo_consulta' => 'Control cardiológico trimestral. Paciente con HTA y DM2 en tratamiento.',
                'sintomas' => 'Asintomática desde última consulta. Refiere cumplir tratamiento y dieta.',
                'fecha_inicio_sintomas' => null,
                'evolucion' => 'Estable',
                'descripcion_padecimiento' => 'Paciente femenino de 40 años acude a control cardiológico trimestral. Asintomática cardiovascular. Refiere adherencia a tratamiento farmacológico y dieta hiposódica. TA domiciliaria en metas.',
                'presion_arterial' => '125/82',
                'temperatura' => 36.6,
                'frecuencia_cardiaca' => 72,
                'frecuencia_respiratoria' => 16,
                'saturacion_oxigeno' => 98,
                'peso' => 65.0,
                'estatura' => 1.62,
                'imc' => 24.8,
                'exploracion_fisica' => 'Ruidos cardiacos rítmicos, sin soplos. Campos pulmonares limpios. No edema en extremidades.',
                'diagnostico_probable' => 'Hipertensión arterial esencial controlada. Diabetes mellitus tipo 2 controlada.',
                'codigo_cie10' => 'I10.X',
                'plan_recomendaciones' => 'Continuar mismo tratamiento. Mantener dieta y ejercicio. Control en 3 meses.',
                'plan_signos_alarma' => null,
                'dolores' => [],
            ],
            [
                'email_paciente' => 'pedro.hernandez@email.com',
                'motivo_consulta' => 'Vacunación hexavalente programada para paciente pediátrico.',
                'sintomas' => 'Paciente asintomático. Desarrollo ponderal y neurológico normal para su edad.',
                'fecha_inicio_sintomas' => null,
                'evolucion' => 'Sano',
                'descripcion_padecimiento' => 'Paciente pediátrico de 2 meses acude para aplicación de vacuna hexavalente (1ra dosis). Sin contraindicaciones. Peso y talla adecuados para edad.',
                'presion_arterial' => null,
                'temperatura' => 36.8,
                'frecuencia_cardiaca' => 130,
                'frecuencia_respiratoria' => 35,
                'saturacion_oxigeno' => 99,
                'peso' => 5.2,
                'estatura' => 0.56,
                'imc' => 16.6,
                'exploracion_fisica' => 'Paciente alerta, hidratado. Fontanelas normotensas. Cardiopulmonar normal. Abdomen blando. Reflejos primitivos presentes.',
                'diagnostico_probable' => 'Paciente pediátrico sano. Esquema de vacunación completo según edad.',
                'codigo_cie10' => 'Z00.1',
                'plan_recomendaciones' => 'Vigilar reacciones post-vacunación. Siguiente cita en 2 meses para 2da dosis.',
                'plan_signos_alarma' => 'Regresar si fiebre >39°C, llanto persistente, o reacción alérgica.',
                'dolores' => [],
            ],
            [
                'email_paciente' => 'ana.sanchez@email.com',
                'motivo_consulta' => 'Paciente acude para extracción quirúrgica de nevus melanocítico en brazo izquierdo.',
                'sintomas' => 'Nevus pigmentado de 8mm en cara anterior de antebrazo izquierdo. Asintomático pero con cambios recientes de coloración.',
                'fecha_inicio_sintomas' => null,
                'evolucion' => 'Crónico - el lunar ha presentado cambios en los últimos 6 meses',
                'descripcion_padecimiento' => 'Paciente refiere lunar en antebrazo izquierdo de años de evolución que ha presentado cambios en coloración (zonas más oscuras) y bordes ligeramente irregulares en los últimos 6 meses. No refiere sangrado, prurito ni dolor.',
                'presion_arterial' => '110/70',
                'temperatura' => 36.5,
                'frecuencia_cardiaca' => 76,
                'frecuencia_respiratoria' => 18,
                'saturacion_oxigeno' => 98,
                'peso' => 58.0,
                'estatura' => 1.65,
                'imc' => 21.3,
                'exploracion_fisica' => 'Nevus de 8x6mm en antebrazo izquierdo, bordes ligeramente irregulares, pigmentación heterogénea. Se realiza escisión elíptica con márgenes de 2mm. Cierre con puntos simples. Pieza enviada a patología.',
                'diagnostico_probable' => 'Nevus melanocítico displásico vs melanoma temprano. Pendiente resultado de patología.',
                'codigo_cie10' => 'D22.6',
                'plan_recomendaciones' => 'Cura oclusiva 48h. Regresar en 7 días para retiro de puntos y resultados de patología.',
                'plan_signos_alarma' => 'Regresar antes si presenta sangrado, signos de infección, o fiebre.',
                'dolores' => [
                    ['ubicacion' => 'Antebrazo izquierdo (sitio quirúrgico)', 'intensidad' => '4/10', 'duracion' => '24 horas post-op'],
                ],
            ],
            [
                'email_paciente' => 'roberto.diaz@email.com',
                'motivo_consulta' => 'Control ginecológico anual. Toma de citología cervical y exploración mamaria.',
                'sintomas' => 'Asintomática. Menstruación regular. Sin dolor ni sangrados anormales.',
                'fecha_inicio_sintomas' => null,
                'evolucion' => 'Estable',
                'descripcion_padecimiento' => 'Paciente acude a consulta ginecológica anual de rutina. Ciclos menstruales regulares cada 28 días. Sin sintomatología. Se toma citología cervical y exploración mamaria sin hallazgos.',
                'presion_arterial' => '118/76',
                'temperatura' => 36.4,
                'frecuencia_cardiaca' => 68,
                'frecuencia_respiratoria' => 16,
                'saturacion_oxigeno' => 99,
                'peso' => 62.0,
                'estatura' => 1.60,
                'imc' => 24.2,
                'exploracion_fisica' => 'Exploración ginecológica: genitales externos sin alteraciones. Especuloscopia: cérvix sano. Toma de citología. Exploración mamaria: sin masas ni secreciones.',
                'diagnostico_probable' => 'Control ginecológico anual normal. Citología cervical sin alteraciones.',
                'codigo_cie10' => 'Z01.4',
                'plan_recomendaciones' => 'Repetir citología en 1 año. Mastografía anual a partir de los 40 años.',
                'plan_signos_alarma' => null,
                'dolores' => [],
            ],
        ];

        foreach ($consultasData as $data) {
            $cita = $finalizadas->firstWhere('paciente.email', $data['email_paciente']);
            if (!$cita) continue;

            $dolores = $data['dolores'];
            unset($data['dolores'], $data['email_paciente']);

            $data['cita_id'] = $cita->id;
            $data['paciente_id'] = $cita->paciente_id;
            $data['medico_id'] = $cita->medico_id;

            $consulta = ConsultaMedica::create($data);

            foreach ($dolores as $d) {
                Dolor::create(array_merge($d, ['consulta_medica_id' => $consulta->id]));
            }
        }
    }
}
