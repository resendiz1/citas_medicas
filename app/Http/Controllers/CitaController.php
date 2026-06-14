<?php

namespace App\Http\Controllers;

use App\Events\CitaCreada;
use App\Events\CitaEstadoActualizado;
use App\Events\MensajeEnviado;
use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\Mensaje;
use App\Models\User;
use App\Models\MedicoBloqueo;
use App\Models\MedicoHorario;
use App\Notifications\CitaEstadoNotificacion;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function create(Request $request)
    {
        $medicos = User::where('role', 'medico')->whereHas('medicoPerfil', fn($q) => $q->where('activo', true))->with('medicoPerfil.tipoMedico')->get();
        $bloqueos = MedicoBloqueo::all();
        $medicoSeleccionado = $request->query('medico_id');

        $bloqueosPorMedico = [];
        foreach ($bloqueos as $b) {
            $bloqueosPorMedico[$b->medico_id][] = [
                'from'   => $b->fecha_inicio->format('Y-m-d'),
                'to'     => $b->fecha_fin->format('Y-m-d'),
                'motivo' => $b->motivo,
            ];
        }

        $horarios = MedicoHorario::where('activo', true)->get();
        $horariosPorMedico = [];
        foreach ($horarios as $h) {
            $horariosPorMedico[$h->medico_id][] = [
                'dia_semana'  => $h->dia_semana,
                'hora_inicio' => substr($h->hora_inicio, 0, 5),
                'hora_fin'    => substr($h->hora_fin, 0, 5),
            ];
        }

        $medicosConPerfil = User::where('role', 'medico')->with('medicoPerfil')->get();
        $intervalosPorMedico = [];
        foreach ($medicosConPerfil as $m) {
            $intervalosPorMedico[$m->id] = optional($m->medicoPerfil)->intervalo_minutos ?? 30;
        }

        $citas = CitaMedica::whereIn('estado', ['pendiente', 'confirmada', 'en_espera', 'en_consulta'])->get();
        $citasPorMedico = [];
        foreach ($citas as $c) {
            $citasPorMedico[$c->medico_id][] = $c->fecha_hora->format('Y-m-d H:i');
        }

        return view('citas.create', compact('medicos', 'bloqueosPorMedico', 'horariosPorMedico', 'citasPorMedico', 'medicoSeleccionado', 'intervalosPorMedico'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medico_id'  => 'required|exists:users,id',
            'fecha_hora' => 'required|date|after:now',
            'motivo'     => 'required|string|max:1000',
        ]);

        $medico = User::where('role', 'medico')->find($data['medico_id']);
        if (!$medico || !$medico->medicoPerfil || !$medico->medicoPerfil->activo) {
            return redirect()->back()->with('error', 'El médico seleccionado no está disponible.')->withInput();
        }

        $fecha = \Carbon\Carbon::parse($data['fecha_hora']);
        $diaSemana = $fecha->dayOfWeek;
        $hora = $fecha->format('H:i');

        $horarios = MedicoHorario::where('medico_id', $data['medico_id'])
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horarios->isEmpty()) {
            return redirect()->back()->with('error', 'El médico no trabaja en la fecha seleccionada.')->withInput();
        }

        $enHorario = false;
        foreach ($horarios as $horario) {
            if ($hora >= substr($horario->hora_inicio, 0, 5) && $hora <= substr($horario->hora_fin, 0, 5)) {
                $enHorario = true;
                break;
            }
        }

        if (!$enHorario) {
            return redirect()->back()->with('error', 'La hora seleccionada está fuera del horario del médico.')->withInput();
        }

        $intervalo = $medico->medicoPerfil->intervalo_minutos ?? 30;
        $slotValido = false;
        foreach ($horarios as $horario) {
            $inicio = \Carbon\Carbon::parse($horario->hora_inicio);
            $fin = \Carbon\Carbon::parse($horario->hora_fin);
            while ($inicio->copy()->addMinutes($intervalo)->lte($fin)) {
                if ($hora === $inicio->format('H:i')) {
                    $slotValido = true;
                    break 2;
                }
                $inicio->addMinutes($intervalo);
            }
        }
        if (!$slotValido) {
            return redirect()->back()->with('error', 'La hora seleccionada no respeta el intervalo de ' . $intervalo . ' minutos del médico.')->withInput();
        }

        $bloqueado = MedicoBloqueo::where('medico_id', $data['medico_id'])
            ->where('fecha_inicio', '<=', $data['fecha_hora'])
            ->where('fecha_fin', '>=', $data['fecha_hora'])
            ->exists();

        if ($bloqueado) {
            return redirect()->back()->with('error', 'El médico tiene un bloqueo en la fecha seleccionada.')->withInput();
        }

        $conflicto = CitaMedica::where('medico_id', $data['medico_id'])
            ->where('fecha_hora', $data['fecha_hora'])
            ->whereIn('estado', ['pendiente', 'confirmada', 'en_espera', 'en_consulta'])
            ->exists();

        if ($conflicto) {
            return redirect()->back()->with('error', 'El médico ya tiene una cita en ese horario.')->withInput();
        }

        $data['paciente_id'] = auth()->id();
        $data['estado'] = 'pendiente';

        $cita = CitaMedica::create($data);

        CitaHistorial::create([
            'cita_id'       => $cita->id,
            'user_id'       => auth()->id(),
            'estado_anterior' => null,
            'estado_nuevo'  => 'pendiente',
            'comentario'    => 'Cita creada por el paciente.',
        ]);

        try {
            broadcast(new CitaCreada($cita->medico_id, [
                'cita_id'    => $cita->id,
                'paciente'   => auth()->user()->name,
                'fecha'      => $cita->fecha_hora->format('d/m/Y H:i'),
                'motivo'     => $cita->motivo,
            ]))->toOthers();
            if ($cita->paciente_id !== $cita->medico_id) {
                broadcast(new CitaCreada($cita->paciente_id, [
                    'cita_id'    => $cita->id,
                    'medico'     => $cita->medico->name,
                    'fecha'      => $cita->fecha_hora->format('d/m/Y H:i'),
                    'motivo'     => $cita->motivo,
                ]))->toOthers();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $mensaje = Mensaje::create([
            'cita_id' => $cita->id,
            'user_id' => auth()->id(),
            'mensaje' => '🟢 Se ha agendado una cita para el ' . $cita->fecha_hora->format('d/m/Y H:i') . '. Motivo: ' . $cita->motivo,
        ]);
        broadcast(new MensajeEnviado(
            [
                'id'         => $mensaje->id,
                'user_id'    => $mensaje->user_id,
                'nombre'     => auth()->user()->name,
                'mensaje'    => $mensaje->mensaje,
                'created_at' => $mensaje->created_at->format('d/m/Y H:i'),
            ],
            $cita->id
        ))->toOthers();

        try {
            $cita->medico->notify(new CitaEstadoNotificacion($cita, 'creada'));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('dashboard')->with('success', 'Cita creada correctamente.');
    }

    public function updateEstado(Request $request, $id)
    {
        $cita = CitaMedica::findOrFail($id);
        $user = auth()->user();

        $estadoActual = $cita->estado;

        if ($user->esPaciente()) {
            if ($cita->paciente_id !== $user->id || $estadoActual !== 'pendiente') {
                abort(403, 'No tienes permiso para modificar esta cita.');
            }
            $nuevoEstado = 'cancelada';
            $comentario = $request->input('comentario', 'Cancelada por el paciente.');
        } elseif ($user->esMedico()) {
            if ($cita->medico_id !== $user->id) {
                abort(403, 'No tienes permiso para modificar esta cita.');
            }
            $nuevoEstado = $request->input('estado');
            $comentario = $request->input('comentario', '');
        } elseif ($user->esAdmin()) {
            $nuevoEstado = $request->input('estado');
            $comentario = $request->input('comentario', '');
        } elseif ($user->esRecepcionista()) {
            $nuevoEstado = $request->input('estado');
            $comentario = $request->input('comentario', '');
        } else {
            abort(403);
        }

        $transitions = [
            'pendiente'    => ['confirmada', 'cancelada', 'reprogramada', 'no_asistio'],
            'confirmada'   => ['en_espera', 'cancelada', 'reprogramada', 'no_asistio'],
            'en_espera'    => ['en_consulta', 'cancelada', 'no_asistio'],
            'en_consulta'  => ['finalizada'],
            'finalizada'   => [],
            'cancelada'    => [],
            'no_asistio'   => [],
            'reprogramada' => ['confirmada'],
        ];

        if (!isset($transitions[$estadoActual]) || !in_array($nuevoEstado, $transitions[$estadoActual])) {
            return redirect()->back()->with('error', 'Transición de estado no válida desde "' . $estadoActual . '" a "' . $nuevoEstado . '".');
        }

        $updateData = ['estado' => $nuevoEstado];

        if ($nuevoEstado === 'reprogramada') {
            $request->validate([
                'fecha_reprogramada' => 'required|date|after:now',
            ]);
            $updateData['fecha_reprogramada'] = $request->input('fecha_reprogramada');
            $updateData['reprogramacion_rechazada'] = null;
        }

        $cita->update($updateData);

        CitaHistorial::create([
            'cita_id'         => $cita->id,
            'user_id'         => $user->id,
            'estado_anterior' => $estadoActual,
            'estado_nuevo'    => $nuevoEstado,
            'comentario'      => $comentario ?: "Estado cambiado de {$estadoActual} a {$nuevoEstado}.",
        ]);

        try {
            broadcast(new CitaEstadoActualizado($cita->id, $nuevoEstado, $estadoActual))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        $mensajesChat = [
            'confirmada' => '✅ Cita confirmada por ' . $user->name . '.',
            'cancelada'  => '❌ Cita cancelada. ' . ($comentario ?: ''),
            'finalizada' => '🏁 Consulta finalizada.',
        ];
        if (isset($mensajesChat[$nuevoEstado])) {
            $msg = Mensaje::create([
                'cita_id' => $cita->id,
                'user_id' => $user->id,
                'mensaje' => $mensajesChat[$nuevoEstado],
            ]);
            broadcast(new MensajeEnviado(
                [
                    'id'         => $msg->id,
                    'user_id'    => $msg->user_id,
                    'nombre'     => $user->name,
                    'mensaje'    => $msg->mensaje,
                    'created_at' => $msg->created_at->format('d/m/Y H:i'),
                ],
                $cita->id
            ))->toOthers();
        }

        try {
            if ($cita->paciente) {
                $cita->paciente->notify(new CitaEstadoNotificacion($cita, 'estado', $estadoActual, $nuevoEstado));
            }
            if ($cita->medico && $user->id !== $cita->medico_id) {
                $cita->medico->notify(new CitaEstadoNotificacion($cita, 'estado', $estadoActual, $nuevoEstado));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->back()->with('success', 'Estado de la cita actualizado correctamente.');
    }

    public function acciones($id)
    {
        $cita = CitaMedica::with([
            'paciente',
            'medico.medicoPerfil.tipoMedico',
            'consultaMedica',
            'ultimaReceta',
        ])->findOrFail($id);

        $user = auth()->user();
        if ($user->esPaciente() && $cita->paciente_id !== $user->id) abort(403);
        if ($user->esMedico() && $cita->medico_id !== $user->id) abort(403);

        return response()->json([
            'html' => view('dashboard._acciones', compact('cita'))->render(),
        ]);
    }

    public function estadosPoll(Request $request)
    {
        $ids = $request->query('ids', '');
        $ids = array_filter(array_map('intval', explode(',', $ids)), fn($v) => $v > 0);
        if (empty($ids)) return response()->json([]);

        $citas = CitaMedica::whereIn('id', $ids)->get(['id', 'estado']);
        $result = [];
        foreach ($citas as $c) {
            $result[(string)$c->id] = $c->estado;
        }
        return response()->json($result);
    }

    public function show($id)
    {
        $cita = CitaMedica::with([
            'paciente',
            'medico.medicoPerfil.tipoMedico',
            'historiales.user',
            'consultaMedica.dolores',
            'recetas.medicamentos',
            'recetas.documentos',
            'recetas.cita.paciente',
            'recetas.cita.medico',
        ])->findOrFail($id);

        $user = auth()->user();
        if ($user->esPaciente() && $cita->paciente_id !== $user->id) {
            abort(403);
        }
        if ($user->esMedico() && $cita->medico_id !== $user->id) {
            abort(403);
        }

        return view('citas.show', compact('cita'));
    }

    public function confirmarReprogramacion($id)
    {
        $cita = CitaMedica::findOrFail($id);
        $user = auth()->user();

        if (!$user->esPaciente() || $cita->paciente_id !== $user->id) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        if ($cita->estado !== 'reprogramada' || !$cita->fecha_reprogramada) {
            return redirect()->back()->with('error', 'No hay una reprogramación pendiente para esta cita.');
        }

        $estadoAnterior = $cita->estado;

        $cita->update([
            'fecha_hora'         => $cita->fecha_reprogramada,
            'fecha_reprogramada' => null,
            'estado'             => 'pendiente',
        ]);

        CitaHistorial::create([
            'cita_id'         => $cita->id,
            'user_id'         => $user->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => 'pendiente',
            'comentario'      => 'Paciente confirmó la reprogramación. Nueva fecha: ' . $cita->fecha_hora->format('d/m/Y H:i') . '.',
        ]);

        try {
            broadcast(new CitaEstadoActualizado($cita->id, 'pendiente', $estadoAnterior))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $cita->medico->notify(new CitaEstadoNotificacion($cita, 'reprogramacion_confirmada'));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('dashboard')->with('success', 'Reprogramación confirmada. Tu cita ha sido actualizada a la nueva fecha.');
    }

    public function cancelarReprogramacion($id)
    {
        $cita = CitaMedica::findOrFail($id);
        $user = auth()->user();

        if (!$user->esPaciente() || $cita->paciente_id !== $user->id) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        if ($cita->estado !== 'reprogramada' || !$cita->fecha_reprogramada) {
            return redirect()->back()->with('error', 'No hay una reprogramación pendiente para esta cita.');
        }

        $estadoAnterior = $cita->estado;

        $cita->update([
            'fecha_reprogramada'      => null,
            'estado'                  => 'pendiente',
            'reprogramacion_rechazada' => now(),
        ]);

        CitaHistorial::create([
            'cita_id'         => $cita->id,
            'user_id'         => $user->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo'    => 'pendiente',
            'comentario'      => 'Paciente rechazó la reprogramación.',
        ]);

        try {
            broadcast(new CitaEstadoActualizado($cita->id, 'pendiente', $estadoAnterior))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $cita->medico->notify(new CitaEstadoNotificacion($cita, 'reprogramacion_rechazada'));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('dashboard')->with('success', 'Has rechazado la reprogramación. La cita mantiene su fecha original.');
    }
}
