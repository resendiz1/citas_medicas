<?php

namespace App\Http\Controllers;

use App\Models\MedicoHorario;
use App\Models\User;
use Illuminate\Http\Request;

class MedicoHorarioController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $medicoId = $request->route('medico');

        if ($user->esAdmin() && $medicoId) {
            $medico = User::where('role', 'medico')->findOrFail($medicoId);
        } elseif ($user->esMedico()) {
            $medico = $user;
        } else {
            abort(403);
        }

        $horarios = MedicoHorario::where('medico_id', $medico->id)->orderBy('dia_semana')->orderBy('hora_inicio')->get();

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        $intervaloMinutos = optional($medico->medicoPerfil)->intervalo_minutos ?? 30;

        return view('medico.horarios', compact('medico', 'horarios', 'dias', 'intervaloMinutos'));
    }

    public function updateIntervalo(Request $request)
    {
        $user = auth()->user();
        $medicoId = $request->input('medico_id');

        if ($user->esAdmin() && $medicoId) {
            $medico = User::where('role', 'medico')->findOrFail($medicoId);
        } elseif ($user->esMedico()) {
            $medico = $user;
        } else {
            abort(403);
        }

        $data = $request->validate([
            'intervalo_minutos' => 'required|integer|min:15|max:120',
        ]);

        $medico->medicoPerfil()->update($data);

        return redirect()->back()->with('success', 'Intervalo entre citas actualizado correctamente.');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $medicoId = $request->input('medico_id');

        if ($user->esAdmin() && $medicoId) {
            $medico = User::where('role', 'medico')->findOrFail($medicoId);
        } elseif ($user->esMedico()) {
            $medico = $user;
        } else {
            abort(403);
        }

        $data = $request->validate([
            'dia_semana'  => 'required|integer|between:0,6',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
            'activo'      => 'nullable|boolean',
        ]);

        $data['medico_id'] = $medico->id;
        $data['activo'] = $request->boolean('activo');

        MedicoHorario::create($data);

        return redirect()->back()->with('success', 'Horario agregado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $horario = MedicoHorario::findOrFail($id);
        $user = auth()->user();

        if (!$user->esAdmin() && $horario->medico_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'dia_semana'  => 'required|integer|between:0,6',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin'    => 'required|date_format:H:i|after:hora_inicio',
            'activo'      => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo');
        $horario->update($data);

        return redirect()->back()->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $horario = MedicoHorario::findOrFail($id);
        $user = auth()->user();

        if (!$user->esAdmin() && $horario->medico_id !== $user->id) {
            abort(403);
        }

        $horario->delete();

        return redirect()->back()->with('success', 'Horario eliminado correctamente.');
    }
}
