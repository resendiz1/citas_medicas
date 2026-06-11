<?php

namespace App\Http\Controllers;

use App\Events\MensajeEnviado;
use App\Models\Mensaje;
use App\Models\CitaMedica;
use App\Notifications\NuevoMensaje;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function citas(Request $request)
    {
        $user = $request->user();
        $citas = CitaMedica::where(function ($q) use ($user) {
            if ($user->esPaciente()) {
                $q->where('paciente_id', $user->id);
            } elseif ($user->esMedico()) {
                $q->where('medico_id', $user->id);
            }
        })->with(['paciente', 'medico'])
            ->orderBy('fecha_hora', 'desc')
            ->get()
            ->map(function ($c) use ($user) {
                $ultimo = \App\Models\Mensaje::where('cita_id', $c->id)->latest()->first();
                $otro = $user->id === $c->paciente_id ? $c->medico : $c->paciente;
                return [
                    'id'           => $c->id,
                    'con quien'    => $otro?->name ?? '—',
                    'fecha'        => $c->fecha_hora->format('d/m/Y H:i'),
                    'estado'       => $c->estado,
                    'ultimo_msg'   => $ultimo?->mensaje ?? '',
                    'ultimo_time'  => $ultimo?->created_at?->diffForHumans() ?? '',
                ];
            });

        return response()->json($citas);
    }

    public function mensajes(Request $request, $citaId)
    {
        $cita = CitaMedica::findOrFail($citaId);
        $user = $request->user();

        if ($user->esPaciente() && $cita->paciente_id !== $user->id) {
            abort(403);
        }
        if ($user->esMedico() && $cita->medico_id !== $user->id) {
            abort(403);
        }

        $query = Mensaje::where('cita_id', $citaId);

        if ($request->filled('since_id')) {
            $query->where('id', '>', $request->integer('since_id'));
        }

        $mensajes = $query->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id'         => $m->id,
                    'user_id'    => $m->user_id,
                    'nombre'     => $m->user->name,
                    'mensaje'    => $m->mensaje,
                    'created_at' => $m->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json($mensajes);
    }

    public function send(Request $request, $citaId)
    {
        $cita = CitaMedica::findOrFail($citaId);
        $user = $request->user();

        if ($user->esPaciente() && $cita->paciente_id !== $user->id) {
            abort(403);
        }
        if ($user->esMedico() && $cita->medico_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'mensaje' => 'required|string|max:2000',
        ]);

        $mensaje = Mensaje::create([
            'cita_id' => $citaId,
            'user_id' => $user->id,
            'mensaje' => $data['mensaje'],
        ]);

        broadcast(new MensajeEnviado(
            [
                'id'         => $mensaje->id,
                'user_id'    => $mensaje->user_id,
                'nombre'     => $user->name,
                'mensaje'    => $mensaje->mensaje,
                'created_at' => $mensaje->created_at->format('d/m/Y H:i'),
            ],
            $citaId
        ))->toOthers();

        $receptor = $user->id === $cita->paciente_id ? $cita->medico : $cita->paciente;
        if ($receptor) {
            try {
                $receptor->notify(new NuevoMensaje($user, $cita, $mensaje->mensaje));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'id'         => $mensaje->id,
            'user_id'    => $mensaje->user_id,
            'nombre'     => $user->name,
            'mensaje'    => $mensaje->mensaje,
            'created_at' => $mensaje->created_at->format('d/m/Y H:i'),
        ]);
    }
}
