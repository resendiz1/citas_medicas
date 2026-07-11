<?php

namespace App\Http\Controllers;

use App\Events\CitaEstadoActualizado;
use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\ConsultaMedica;
use App\Models\ConsultaMedicamento;
use App\Models\Diagnostico;
use App\Models\Receta;
use App\Models\RecetaMedicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaMedicaController extends Controller
{
    public function create($citaId)
    {
        $cita = CitaMedica::with([
            'consultaMedica.dolores',
            'consultaMedica.sintomasRegistrados',
            'consultaMedica.diagnosticos',
            'consultaMedica.medicamentos',
            'recetas.medicamentos',
            'paciente',
        ])->findOrFail($citaId);

        if (in_array($cita->estado, ['cancelada', 'no_asistio'])) {
            abort(403, 'No puedes iniciar una consulta en una cita cancelada o con inasistencia.');
        }

        if ($cita->estado === 'en_espera') {
            $cita->update(['estado' => 'en_consulta']);
            CitaHistorial::create([
                'cita_id'         => $cita->id,
                'user_id'         => auth()->id(),
                'estado_anterior' => 'en_espera',
                'estado_nuevo'    => 'en_consulta',
                'comentario'      => 'Inicio de consulta médica.',
            ]);
            try {
                broadcast(new CitaEstadoActualizado($cita->id, 'en_consulta', 'en_espera'))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $consulta = $cita->consultaMedica;

        return view('consulta-medica.form', compact('cita', 'consulta'));
    }

    public function store(Request $request, $citaId)
    {
        $cita = CitaMedica::findOrFail($citaId);

        $data = $request->validate([
            'accion'                           => 'required|in:borrador,finalizar',

            'motivo_consulta'                  => 'nullable|string',
            'fecha_inicio_sintomas'            => 'nullable|date|before_or_equal:' . $cita->fecha_hora->format('Y-m-d'),
            'tiempo_evolucion'                 => 'nullable|string|max:255',
            'forma_inicio'                     => 'nullable|in:subito,gradual',
            'evolucion'                        => 'nullable|in:mejorando,estable,empeorando',
            'descripcion_padecimiento'         => 'nullable|string',

            'presion_arterial'                 => 'nullable|string|max:50',
            'temperatura'                      => 'nullable|numeric|between:30,45',
            'frecuencia_cardiaca'              => 'nullable|integer|min:0|max:300',
            'frecuencia_respiratoria'          => 'nullable|integer|min:0|max:100',
            'saturacion_oxigeno'               => 'nullable|integer|min:0|max:100',
            'peso'                             => 'nullable|numeric|between:0,500',
            'estatura'                         => 'nullable|numeric|between:0,300',

            'exploracion_sin_hallazgos'        => 'nullable|boolean',
            'exploracion_estado_general'       => 'nullable|string',
            'exploracion_cabeza_cuello'        => 'nullable|string',
            'exploracion_respiratorio'         => 'nullable|string',
            'exploracion_cardiovascular'       => 'nullable|string',
            'exploracion_abdomen'              => 'nullable|string',
            'exploracion_extremidades'         => 'nullable|string',
            'exploracion_neurologico'          => 'nullable|string',
            'exploracion_hallazgos'            => 'nullable|string',

            'resumen_clinico'                  => 'nullable|string',
            'codigo_cie10'                     => 'nullable|string|max:20',
            'pronostico'                       => 'nullable|in:favorable,reservado,grave,muy_grave',

            'plan_estudios'                    => 'nullable|string',
            'plan_procedimientos'              => 'nullable|string',
            'plan_recomendaciones'             => 'nullable|string',
            'plan_signos_alarma'               => 'nullable|string',
            'plan_referencia'                  => 'nullable|string',
            'plan_seguimiento_fecha'           => 'nullable|date',
            'plan_incapacidad'                 => 'nullable|string',

            'observaciones'                    => 'nullable|string',

            'sintomas_lista'                   => 'nullable|array',
            'sintomas_lista.*.nombre'          => 'required|string|max:255',
            'sintomas_lista.*.ubicacion'       => 'nullable|string|max:255',
            'sintomas_lista.*.intensidad_categoria' => 'nullable|in:leve,moderado,intenso',

            'sintomas_lista.*.inicio'          => 'nullable|string|max:255',
            'sintomas_lista.*.duracion'        => 'nullable|string|max:255',
            'sintomas_lista.*.frecuencia'      => 'nullable|string|max:255',
            'sintomas_lista.*.observaciones'   => 'nullable|string|max:1000',

            'diagnosticos'                     => 'nullable|array',
            'diagnosticos.*.descripcion'       => 'required|string|max:2000',
            'diagnosticos.*.codigo_cie10'      => 'nullable|string|max:20',
            'diagnosticos.*.tipo'              => 'required|in:probable,diferencial,definitivo',
            'diagnosticos.*.es_principal'      => 'nullable|boolean',

            'medicamentos'                     => 'nullable|array',
            'medicamentos.*.nombre_generico'   => 'required|string|max:255',
            'medicamentos.*.nombre_comercial'  => 'nullable|string|max:255',
            'medicamentos.*.concentracion'     => 'nullable|string|max:255',
            'medicamentos.*.presentacion'      => 'nullable|string|max:255',
            'medicamentos.*.forma_farmaceutica' => 'nullable|string|max:255',
            'medicamentos.*.dosis'             => 'nullable|string|max:255',
            'medicamentos.*.via_administracion' => 'nullable|string|max:255',
            'medicamentos.*.frecuencia'        => 'nullable|string|max:255',
            'medicamentos.*.duracion'          => 'nullable|string|max:255',
            'medicamentos.*.cantidad_total'    => 'nullable|string|max:255',
            'medicamentos.*.indicaciones'      => 'nullable|string|max:500',
            'medicamentos.*.incluir_en_receta' => 'nullable|boolean',
        ]);

        $data['cita_id'] = $cita->id;
        $data['paciente_id'] = $cita->paciente_id;
        $data['medico_id'] = $cita->medico_id;

        if (!empty($data['peso']) && !empty($data['estatura']) && $data['estatura'] > 0) {
            $data['imc'] = round($data['peso'] / (($data['estatura'] / 100) ** 2), 1);
        }

        $consulta = ConsultaMedica::updateOrCreate(
            ['cita_id' => $cita->id],
            $data
        );

        $sintomaIds = [];
        foreach ($request->sintomas_lista ?? [] as $sintomaData) {
            $nombre = trim($sintomaData['nombre'] ?? '');
            if (empty($nombre)) continue;

            if (!empty($sintomaData['id'])) {
                $sintoma = $consulta->sintomasRegistrados()->find($sintomaData['id']);
                if ($sintoma) {
                    $sintoma->update([
                        'nombre'               => $nombre,
                        'ubicacion'            => $sintomaData['ubicacion'] ?? null,
                        'intensidad_categoria' => $sintomaData['intensidad_categoria'] ?? null,
                        'inicio'               => $sintomaData['inicio'] ?? null,
                        'duracion'             => $sintomaData['duracion'] ?? null,
                        'frecuencia'           => $sintomaData['frecuencia'] ?? null,
                        'observaciones'        => $sintomaData['observaciones'] ?? null,
                    ]);
                    $sintomaIds[] = $sintoma->id;
                    continue;
                }
            }
            $sintoma = $consulta->sintomasRegistrados()->create([
                'nombre'               => $nombre,
                'ubicacion'            => $sintomaData['ubicacion'] ?? null,
                'intensidad_categoria' => $sintomaData['intensidad_categoria'] ?? null,
                'inicio'               => $sintomaData['inicio'] ?? null,
                'duracion'             => $sintomaData['duracion'] ?? null,
                'frecuencia'           => $sintomaData['frecuencia'] ?? null,
                'observaciones'        => $sintomaData['observaciones'] ?? null,
            ]);
            $sintomaIds[] = $sintoma->id;
        }
        $consulta->sintomasRegistrados()->whereNotIn('id', $sintomaIds)->delete();

        $diagIds = [];
        foreach ($request->diagnosticos ?? [] as $diagData) {
            $desc = trim($diagData['descripcion'] ?? '');
            if (empty($desc)) continue;

            if (!empty($diagData['id'])) {
                $diag = $consulta->diagnosticos()->find($diagData['id']);
                if ($diag) {
                    $diag->update([
                        'descripcion'  => $desc,
                        'codigo_cie10' => $diagData['codigo_cie10'] ?? null,
                        'tipo'         => $diagData['tipo'],
                        'es_principal' => $diagData['es_principal'] ?? false,
                    ]);
                    $diagIds[] = $diag->id;
                    continue;
                }
            }
            $diag = $consulta->diagnosticos()->create([
                'descripcion'  => $desc,
                'codigo_cie10' => $diagData['codigo_cie10'] ?? null,
                'tipo'         => $diagData['tipo'],
                'es_principal' => $diagData['es_principal'] ?? false,
            ]);
            $diagIds[] = $diag->id;
        }
        $consulta->diagnosticos()->whereNotIn('id', $diagIds)->delete();

        $medIds = [];
        foreach ($request->medicamentos ?? [] as $medData) {
            $gen = trim($medData['nombre_generico'] ?? '');
            if (empty($gen)) continue;

            if (!empty($medData['id'])) {
                $med = $consulta->medicamentos()->find($medData['id']);
                if ($med) {
                    $med->update([
                        'nombre_generico'   => $gen,
                        'nombre_comercial'  => $medData['nombre_comercial'] ?? null,
                        'concentracion'     => $medData['concentracion'] ?? null,
                        'presentacion'      => $medData['presentacion'] ?? null,
                        'forma_farmaceutica'=> $medData['forma_farmaceutica'] ?? null,
                        'dosis'             => $medData['dosis'] ?? null,
                        'via_administracion'=> $medData['via_administracion'] ?? null,
                        'frecuencia'        => $medData['frecuencia'] ?? null,
                        'duracion'          => $medData['duracion'] ?? null,
                        'cantidad_total'    => $medData['cantidad_total'] ?? null,
                        'indicaciones'      => $medData['indicaciones'] ?? null,
                        'incluir_en_receta' => $medData['incluir_en_receta'] ?? true,
                    ]);
                    $medIds[] = $med->id;
                    continue;
                }
            }
            $med = $consulta->medicamentos()->create([
                'nombre_generico'   => $gen,
                'nombre_comercial'  => $medData['nombre_comercial'] ?? null,
                'concentracion'     => $medData['concentracion'] ?? null,
                'presentacion'      => $medData['presentacion'] ?? null,
                'forma_farmaceutica'=> $medData['forma_farmaceutica'] ?? null,
                'dosis'             => $medData['dosis'] ?? null,
                'via_administracion'=> $medData['via_administracion'] ?? null,
                'frecuencia'        => $medData['frecuencia'] ?? null,
                'duracion'          => $medData['duracion'] ?? null,
                'cantidad_total'    => $medData['cantidad_total'] ?? null,
                'indicaciones'      => $medData['indicaciones'] ?? null,
                'incluir_en_receta' => $medData['incluir_en_receta'] ?? true,
            ]);
            $medIds[] = $med->id;
        }
        $consulta->medicamentos()->whereNotIn('id', $medIds)->delete();

        if ($request->accion === 'finalizar' && $cita->estado === 'en_consulta') {
            $cita->update(['estado' => 'finalizada']);
            CitaHistorial::create([
                'cita_id'         => $cita->id,
                'user_id'         => auth()->id(),
                'estado_anterior' => 'en_consulta',
                'estado_nuevo'    => 'finalizada',
                'comentario'      => 'Consulta finalizada.',
            ]);
            try {
                broadcast(new CitaEstadoActualizado($cita->id, 'finalizada', 'en_consulta'))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $msg = $cita->consultaMedica
            ? 'Consulta médica guardada.'
            : 'Consulta médica creada.';

        return redirect()->route('dashboard')->with('success', $msg);
    }

    public function generarReceta($citaId)
    {
        $cita = CitaMedica::with([
            'consultaMedica.diagnosticos',
            'consultaMedica.medicamentos',
            'consultaMedica',
            'paciente',
        ])->findOrFail($citaId);

        $consulta = $cita->consultaMedica;
        if (!$consulta) {
            return redirect()->back()->with('error', 'No hay datos de consulta para generar la receta.');
        }

        $diagnosticoPrincipal = $consulta->diagnosticos()->where('es_principal', true)->first();
        $diagnosticoTexto = $diagnosticoPrincipal?->descripcion ?? $consulta->diagnostico_final ?? '';

        $medParaReceta = $consulta->medicamentos()->where('incluir_en_receta', true)->get();
        if ($medParaReceta->isEmpty()) {
            return redirect()->back()->with('error', 'No hay medicamentos marcados para incluir en la receta.');
        }

        $receta = DB::transaction(function () use ($cita, $consulta, $diagnosticoTexto, $medParaReceta, $diagnosticoPrincipal) {
            $receta = Receta::create([
                'cita_id'              => $cita->id,
                'paciente_id'          => $cita->paciente_id,
                'medico_id'            => $cita->medico_id,
                'diagnostico'          => $diagnosticoTexto,
                'indicaciones_generales' => $consulta->plan_recomendaciones ?? '',
                'notas'                => 'Generada desde consulta del ' . now()->format('d/m/Y'),
                'fecha_emision'        => now()->toDateString(),
            ]);

            foreach ($medParaReceta as $med) {
                RecetaMedicamento::create([
                    'receta_id'         => $receta->id,
                    'medicamento'       => $med->nombre_generico,
                    'nombre_generico'   => $med->nombre_generico,
                    'nombre_comercial'  => $med->nombre_comercial,
                    'concentracion'     => $med->concentracion,
                    'presentacion'      => $med->presentacion,
                    'forma_farmaceutica'=> $med->forma_farmaceutica,
                    'dosis'             => $med->dosis,
                    'via_administracion'=> $med->via_administracion,
                    'frecuencia'        => $med->frecuencia,
                    'duracion'          => $med->duracion,
                    'cantidad_total'    => $med->cantidad_total,
                    'indicaciones'      => $med->indicaciones,
                    'incluir_en_receta' => true,
                ]);
            }

            return $receta;
        });

        return redirect()->route('recetas.show', $receta->id)
            ->with('success', 'Receta generada correctamente.');
    }

    public function show($citaId)
    {
        $cita = CitaMedica::with([
            'consultaMedica.dolores',
            'consultaMedica.sintomasRegistrados',
            'consultaMedica.diagnosticos',
            'consultaMedica.medicamentos',
            'paciente',
            'medico',
        ])->findOrFail($citaId);

        if (in_array($cita->estado, ['cancelada', 'no_asistio']) && !$cita->consultaMedica) {
            abort(403, 'No hay consulta para esta cita.');
        }

        $consulta = $cita->consultaMedica;

        if (!$consulta) {
            return redirect()->route('dashboard')->with('error', 'No hay datos de consulta para esta cita.');
        }

        return view('consulta-medica.show', compact('cita', 'consulta'));
    }
}
