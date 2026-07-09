<?php

namespace App\Http\Controllers;

use App\Models\CitaMedica;
use App\Models\ConsultaMedica;
use App\Models\RecetaMedicamento;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->esAdmin()) {
            $statsUrl = route('estadisticas.admin.general');
        } elseif ($user->esMedico()) {
            $statsUrl = route('estadisticas.medico');
        } elseif ($user->esPaciente()) {
            $statsUrl = route('estadisticas.paciente');
        } else {
            abort(403);
        }

        return view('estadisticas.index', compact('statsUrl'));
    }

    public function adminGeneral()
    {
        return response()->json([
            'citasPorEstado' => $this->citasPorEstadoGeneral(),
            'citasPorMes'    => $this->citasPorMesGeneral(),
            'medicamentos'    => $this->topMedicamentosGeneral(),
            'diagnosticos'    => $this->topDiagnosticosGeneral(),
            'medicosVisited' => $this->topMedicosGeneral(),
        ]);
    }

    public function admin($medicoId)
    {
        $user = User::where('role', 'medico')->findOrFail($medicoId);

        return response()->json([
            'citasPorEstado' => $this->citasPorEstado($medicoId),
            'citasPorMes'    => $this->citasPorMes($medicoId),
            'medicamentos'    => $this->topMedicamentos($medicoId),
            'diagnosticos'    => $this->topDiagnosticos($medicoId),
        ]);
    }

    public function medico()
    {
        $medicoId = auth()->id();

        return response()->json([
            'citasPorEstado' => $this->citasPorEstado($medicoId),
            'citasPorMes'    => $this->citasPorMes($medicoId),
            'medicamentos'    => $this->topMedicamentos($medicoId),
            'diagnosticos'    => $this->topDiagnosticos($medicoId),
        ]);
    }

    public function paciente()
    {
        $pacienteId = auth()->id();

        return response()->json([
            'citasPorEstado' => $this->citasPorEstadoPaciente($pacienteId),
            'citasPorMes'    => $this->citasPorMesPaciente($pacienteId),
            'medicosVisited' => $this->topMedicos($pacienteId),
            'diagnosticos'   => $this->topDiagnosticosPaciente($pacienteId),
        ]);
    }

    private function citasPorEstado($medicoId)
    {
        return CitaMedica::where('medico_id', $medicoId)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');
    }

    private function citasPorEstadoPaciente($pacienteId)
    {
        return CitaMedica::where('paciente_id', $pacienteId)
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');
    }

    private function citasPorMes($medicoId)
    {
        return CitaMedica::where('medico_id', $medicoId)
            ->select(DB::raw("DATE_FORMAT(fecha_hora, '%Y-%m') as mes"), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');
    }

    private function citasPorMesPaciente($pacienteId)
    {
        return CitaMedica::where('paciente_id', $pacienteId)
            ->select(DB::raw("DATE_FORMAT(fecha_hora, '%Y-%m') as mes"), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');
    }

    private function topMedicamentos($medicoId)
    {
        return RecetaMedicamento::whereHas('receta', function ($q) use ($medicoId) {
            $q->where('medico_id', $medicoId);
        })
            ->select('medicamento', DB::raw('count(*) as total'))
            ->groupBy('medicamento')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'medicamento');
    }

    private function topDiagnosticos($medicoId)
    {
        return ConsultaMedica::where('medico_id', $medicoId)
            ->whereNotNull('diagnostico_final')
            ->select('diagnostico_final', DB::raw('count(*) as total'))
            ->groupBy('diagnostico_final')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'diagnostico_final');
    }

    private function topDiagnosticosPaciente($pacienteId)
    {
        return ConsultaMedica::where('paciente_id', $pacienteId)
            ->whereNotNull('diagnostico_final')
            ->select('diagnostico_final', DB::raw('count(*) as total'))
            ->groupBy('diagnostico_final')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'diagnostico_final');
    }

    private function topMedicos($pacienteId)
    {
        return CitaMedica::where('paciente_id', $pacienteId)
            ->join('users', 'citas_medicas.medico_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name', 'users.id')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'users.name');
    }

    private function citasPorEstadoGeneral()
    {
        return CitaMedica::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');
    }

    private function citasPorMesGeneral()
    {
        return CitaMedica::select(DB::raw("DATE_FORMAT(fecha_hora, '%Y-%m') as mes"), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');
    }

    private function topMedicamentosGeneral()
    {
        return RecetaMedicamento::select('medicamento', DB::raw('count(*) as total'))
            ->groupBy('medicamento')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'medicamento');
    }

    private function topDiagnosticosGeneral()
    {
        return ConsultaMedica::whereNotNull('diagnostico_final')
            ->select('diagnostico_final', DB::raw('count(*) as total'))
            ->groupBy('diagnostico_final')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'diagnostico_final');
    }

    private function topMedicosGeneral()
    {
        return CitaMedica::join('users', 'citas_medicas.medico_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as total'))
            ->groupBy('users.name', 'users.id')
            ->orderByDesc('total')
            ->limit(10)
            ->pluck('total', 'users.name');
    }
}
