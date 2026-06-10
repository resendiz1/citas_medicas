<?php

namespace App\Http\Controllers;

use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\ConsultaMedica;
use App\Models\Receta;
use App\Models\RecetaMedicamento;
use Illuminate\Http\Request;

class ConsultaMedicaController extends Controller
{
    public function create($citaId)
    {
        $cita = CitaMedica::with('consultaMedica.dolores', 'recetas.medicamentos', 'paciente')->findOrFail($citaId);

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
        }

        $consulta = $cita->consultaMedica;

        return view('consulta-medica.form', compact('cita', 'consulta'));
    }

    public function store(Request $request, $citaId)
    {
        $cita = CitaMedica::findOrFail($citaId);

        $data = $request->validate([
            'motivo_consulta'       => 'nullable|string',
            'sintomas'              => 'nullable|string',
            'tiempo_evolucion'      => 'nullable|string|max:255',
            'presion_arterial'      => 'nullable|string|max:50',
            'temperatura'           => 'nullable|numeric|between:30,45',
            'frecuencia_cardiaca'   => 'nullable|integer|min:0|max:300',
            'frecuencia_respiratoria' => 'nullable|integer|min:0|max:100',
            'saturacion_oxigeno'    => 'nullable|integer|min:0|max:100',
            'peso'                  => 'nullable|numeric|between:0,500',
            'estatura'              => 'nullable|numeric|between:0,300',
            'imc'                   => 'nullable|numeric|between:0,100',
            'exploracion_fisica'    => 'nullable|string',
            'observaciones'         => 'nullable|string',
            'diagnostico_probable'  => 'nullable|string',
            'diagnostico_final'     => 'nullable|string',
            'codigo_cie10'          => 'nullable|string|max:20',
            'dolores'               => 'nullable|array',
            'dolores.*.ubicacion'   => 'required|string|max:255',
            'dolores.*.intensidad'  => 'nullable|string|max:255',
            'dolores.*.duracion'    => 'nullable|string|max:255',
            'recetas'               => 'nullable|array',
            'recetas.*.diagnostico' => 'required|string|max:5000',
            'recetas.*.indicaciones_generales' => 'required|string|max:5000',
            'recetas.*.notas'       => 'nullable|string|max:5000',
            'recetas.*.medicamentos' => 'nullable|array',
            'recetas.*.medicamentos.*.nombre' => 'nullable|string|max:255',
            'recetas.*.medicamentos.*.dosis' => 'nullable|string|max:255',
            'recetas.*.medicamentos.*.frecuencia' => 'nullable|string|max:255',
            'recetas.*.medicamentos.*.duracion' => 'nullable|string|max:255',
            'recetas.*.medicamentos.*.indicaciones' => 'nullable|string|max:500',
        ]);

        $data['cita_id'] = $cita->id;
        $data['paciente_id'] = $cita->paciente_id;
        $data['medico_id'] = $cita->medico_id;

        $consulta = ConsultaMedica::updateOrCreate(
            ['cita_id' => $cita->id],
            $data
        );

        $ids = [];
        foreach ($request->dolores ?? [] as $dolorData) {
            $dolorData['ubicacion'] = $dolorData['ubicacion'] ?? '';
            if (empty(trim($dolorData['ubicacion']))) continue;

            if (!empty($dolorData['id'])) {
                $dolor = $consulta->dolores()->find($dolorData['id']);
                if ($dolor) {
                    $dolor->update([
                        'ubicacion'  => $dolorData['ubicacion'],
                        'intensidad' => $dolorData['intensidad'] ?? null,
                        'duracion'   => $dolorData['duracion'] ?? null,
                    ]);
                    $ids[] = $dolor->id;
                    continue;
                }
            }
            $dolor = $consulta->dolores()->create([
                'ubicacion'  => $dolorData['ubicacion'],
                'intensidad' => $dolorData['intensidad'] ?? null,
                'duracion'   => $dolorData['duracion'] ?? null,
            ]);
            $ids[] = $dolor->id;
        }
        $consulta->dolores()->whereNotIn('id', $ids)->delete();

        $recetaIds = [];
        foreach ($request->recetas ?? [] as $recetaData) {
            if (empty(trim($recetaData['diagnostico'] ?? ''))) continue;

            $receta = Receta::updateOrCreate(
                ['id' => $recetaData['id'] ?? null, 'cita_id' => $cita->id],
                [
                    'cita_id'              => $cita->id,
                    'paciente_id'          => $cita->paciente_id,
                    'medico_id'            => $cita->medico_id,
                    'diagnostico'          => $recetaData['diagnostico'],
                    'indicaciones_generales' => $recetaData['indicaciones_generales'],
                    'notas'                => $recetaData['notas'] ?? null,
                    'fecha_emision'        => now()->toDateString(),
                ]
            );
            $recetaIds[] = $receta->id;

            $medIds = [];
            foreach ($recetaData['medicamentos'] ?? [] as $medData) {
                if (empty(trim($medData['nombre'] ?? ''))) continue;

                $med = RecetaMedicamento::updateOrCreate(
                    ['id' => $medData['id'] ?? null, 'receta_id' => $receta->id],
                    [
                        'receta_id'    => $receta->id,
                        'medicamento'  => $medData['nombre'],
                        'dosis'        => $medData['dosis'] ?? null,
                        'frecuencia'   => $medData['frecuencia'] ?? null,
                        'duracion'     => $medData['duracion'] ?? null,
                        'indicaciones' => $medData['indicaciones'] ?? null,
                    ]
                );
                $medIds[] = $med->id;
            }
            $receta->medicamentos()->whereNotIn('id', $medIds)->delete();
        }
        $cita->recetas()->whereNotIn('id', $recetaIds)->delete();

        if ($cita->estado === 'en_consulta') {
            $cita->update(['estado' => 'finalizada']);
            CitaHistorial::create([
                'cita_id'         => $cita->id,
                'user_id'         => auth()->id(),
                'estado_anterior' => 'en_consulta',
                'estado_nuevo'    => 'finalizada',
                'comentario'      => 'Consulta finalizada.',
            ]);
        }

        $msg = $cita->consultaMedica ? 'Consulta médica actualizada.' : 'Consulta médica guardada.';

        return redirect()->route('dashboard')->with('success', $msg);
    }

    public function show($citaId)
    {
        $cita = CitaMedica::with('consultaMedica.dolores', 'paciente', 'medico')->findOrFail($citaId);

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
