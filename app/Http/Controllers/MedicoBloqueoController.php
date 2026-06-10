<?php

namespace App\Http\Controllers;

use App\Models\MedicoBloqueo;
use App\Models\User;
use Illuminate\Http\Request;

class MedicoBloqueoController extends Controller
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

        $bloqueos = MedicoBloqueo::where('medico_id', $medico->id)->orderBy('fecha_inicio')->get();

        return view('medico.bloqueos', compact('medico', 'bloqueos'));
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
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'motivo'       => 'nullable|string|max:500',
        ]);

        $data['medico_id'] = $medico->id;
        $data['fecha_inicio'] = $data['fecha_inicio'] . ' 00:00:00';
        $data['fecha_fin'] = $data['fecha_fin'] . ' 23:59:59';

        MedicoBloqueo::create($data);

        return redirect()->back()->with('success', 'Bloqueo registrado correctamente.');
    }

    public function destroy($id)
    {
        $bloqueo = MedicoBloqueo::findOrFail($id);
        $user = auth()->user();

        if (!$user->esAdmin() && $bloqueo->medico_id !== $user->id) {
            abort(403);
        }

        $bloqueo->delete();

        return redirect()->back()->with('success', 'Bloqueo eliminado correctamente.');
    }
}
