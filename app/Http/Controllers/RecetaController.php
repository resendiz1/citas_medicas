<?php

namespace App\Http\Controllers;

use App\Models\CitaMedica;
use App\Models\Receta;
use App\Models\RecetaDocumento;
use App\Models\RecetaMedicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecetaController extends Controller
{
    public function create($idCita)
    {
        $cita = CitaMedica::with('paciente', 'medico')->findOrFail($idCita);

        if (Auth::user()->role !== 'admin' && Auth::id() !== $cita->medico_id) {
            abort(403);
        }

        if (Auth::user()->role !== 'admin' && !$cita->fecha_hora->isToday()) {
            return redirect()->route('dashboard')
                ->with('error', 'Solo puedes generar la receta el día de la consulta.');
        }

        return view('recetas.create', compact('cita'));
    }

    public function store(Request $request, $idCita)
    {
        $cita = CitaMedica::findOrFail($idCita);

        if (Auth::user()->role !== 'admin' && Auth::id() !== $cita->medico_id) {
            abort(403);
        }

        if (Auth::user()->role !== 'admin' && !$cita->fecha_hora->isToday()) {
            return redirect()->route('dashboard')
                ->with('error', 'Solo puedes generar la receta el día de la consulta.');
        }

        $data = $request->validate([
            'diagnostico'          => 'required|string|max:5000',
            'indicaciones_generales' => 'required|string|max:5000',
            'notas'                => 'nullable|string|max:5000',
            'medicamentos.*.nombre' => 'nullable|string|max:255',
            'medicamentos.*.dosis' => 'nullable|string|max:255',
            'medicamentos.*.frecuencia' => 'nullable|string|max:255',
            'medicamentos.*.duracion' => 'nullable|string|max:255',
            'medicamentos.*.indicaciones' => 'nullable|string|max:500',
            'documentos.*'         => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
        ]);

        $receta = Receta::create([
            'cita_id'              => $cita->id,
            'paciente_id'          => $cita->paciente_id,
            'medico_id'            => $cita->medico_id,
            'diagnostico'          => $data['diagnostico'],
            'indicaciones_generales' => $data['indicaciones_generales'],
            'notas'                => $data['notas'] ?? null,
            'fecha_emision'        => now()->toDateString(),
        ]);

        if (!empty($data['medicamentos'])) {
            foreach ($data['medicamentos'] as $med) {
                if (!empty($med['nombre'])) {
                    RecetaMedicamento::create([
                        'receta_id'    => $receta->id,
                        'medicamento'  => $med['nombre'],
                        'dosis'        => $med['dosis'] ?? null,
                        'frecuencia'   => $med['frecuencia'] ?? null,
                        'duracion'     => $med['duracion'] ?? null,
                        'indicaciones' => $med['indicaciones'] ?? null,
                    ]);
                }
            }
        }

        if ($request->hasFile('documentos')) {
            foreach ($request->file('documentos') as $file) {
                $nombreOriginal = $file->getClientOriginalName();
                $ruta = $file->store('recetas/' . $receta->id, 'public');

                RecetaDocumento::create([
                    'receta_id'       => $receta->id,
                    'nombre_original' => $nombreOriginal,
                    'ruta_archivo'    => $ruta,
                    'tipo_mime'       => $file->getMimeType(),
                    'tamano'          => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('recetas.show', $receta->id)
            ->with('success', 'Receta creada correctamente.');
    }

    public function show($id)
    {
        $receta = Receta::with('cita.paciente', 'cita.medico', 'medicamentos', 'documentos')->findOrFail($id);
        $user = Auth::user();

        if ($user->role === 'medico' && $receta->cita->medico_id !== $user->id) {
            abort(403);
        }

        if ($user->role === 'paciente' && $receta->cita->paciente_id !== $user->id) {
            abort(403);
        }

        return view('recetas.show', compact('receta'));
    }

    public function downloadDocumento($id)
    {
        $doc = RecetaDocumento::with('receta.cita')->findOrFail($id);
        $user = Auth::user();
        $cita = $doc->receta->cita;

        if ($user->role !== 'admin' &&
            $user->id !== $cita->medico_id &&
            $user->id !== $cita->paciente_id) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($doc->ruta_archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('public')->download($doc->ruta_archivo, $doc->nombre_original);
    }
}
