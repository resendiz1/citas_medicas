<?php

namespace App\Http\Controllers;

use App\Models\CitaMedica;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function checkNuevas(Request $request)
    {
        $user = auth()->user();
        $maxId = $request->integer('max_id', 0);

        $query = CitaMedica::where('id', '>', $maxId);
        if ($user->esMedico()) {
            $query->where('medico_id', $user->id);
        } elseif ($user->esPaciente()) {
            $query->where('paciente_id', $user->id);
        }
        // admin/recepcionista: any cita

        $nueva = $query->exists();
        $nuevoMax = CitaMedica::when($user->esMedico(), fn($q) => $q->where('medico_id', $user->id))
            ->when($user->esPaciente(), fn($q) => $q->where('paciente_id', $user->id))
            ->max('id') ?? $maxId;

        return response()->json([
            'nuevas' => $nueva,
            'max_id' => $nuevoMax,
        ]);
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->esAdmin()) {
            $totalPacientes = User::where('role', 'paciente')->count();
            $totalMedicos   = User::where('role', 'medico')->count();
            $totalCitas     = CitaMedica::count();
            $citasPendientes = CitaMedica::where('estado', 'pendiente')->count();

            return view('dashboard.index', compact(
                'totalPacientes', 'totalMedicos', 'totalCitas', 'citasPendientes'
            ));
        }

        if ($user->esMedico()) {
            $citas = CitaMedica::where('medico_id', $user->id)
                ->with('paciente', 'ultimaReceta')
                ->orderBy('fecha_hora')
                ->get();

            return view('dashboard.index', compact('citas'));
        }

        if ($user->esRecepcionista()) {
            $totalCitas = CitaMedica::count();
            $citasPendientes = CitaMedica::where('estado', 'pendiente')->count();
            $citasHoy = CitaMedica::whereDate('fecha_hora', today())->count();
            $citas = CitaMedica::with('paciente', 'medico.medicoPerfil.tipoMedico')
                ->orderBy('fecha_hora')
                ->paginate(20);

            return view('dashboard.index', compact('totalCitas', 'citasPendientes', 'citasHoy', 'citas'));
        }

        $citas = CitaMedica::where('paciente_id', $user->id)
            ->with('medico', 'ultimaReceta')
            ->orderBy('fecha_hora')
            ->get();

        $medicos = User::where('role', 'medico')
            ->whereHas('medicoPerfil', fn($q) => $q->where('activo', true))
            ->with('medicoPerfil.tipoMedico')
            ->get();

        return view('dashboard.index', compact('citas', 'medicos'));
    }
}
