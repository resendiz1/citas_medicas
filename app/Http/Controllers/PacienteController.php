<?php

namespace App\Http\Controllers;

use App\Models\Alergia;
use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\ContactoEmergencia;
use App\Models\EnfermedadImportante;
use App\Models\IaChatMensaje;
use App\Notifications\CitaEstadoNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PacienteController extends Controller
{
    public function perfilShow()
    {
        $user = Auth::user()->load([
            'contactosEmergencia',
            'alergias',
            'enfermedadesImportantes',
            'citasComoPaciente.medico.medicoPerfil.tipoMedico',
        ]);

        $citas = $user->citasComoPaciente()->orderBy('fecha_hora', 'desc')->get();
        $catalogoAlergias = Alergia::orderBy('nombre')->get();
        $catalogoEnfermedades = EnfermedadImportante::orderBy('nombre')->get();

        return view('paciente.perfil', compact('user', 'citas', 'catalogoAlergias', 'catalogoEnfermedades'));
    }

    public function historial()
    {
        $user = Auth::user();
        $citas = $user->citasComoPaciente()
            ->with([
                'medico.medicoPerfil.tipoMedico',
                'consultaMedica.dolores',
                'consultaMedica.sintomasRegistrados',
                'recetas.medicamentos',
                'recetas.documentos',
            ])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        return view('paciente.historial', compact('citas'));
    }

    public function perfilUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'fecha_nacimiento' => 'nullable|date',
            'telefono'         => 'nullable|string|max:50',
            'direccion'        => 'nullable|string|max:500',
            'observaciones'    => 'nullable|string|max:1000',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($user->foto_url) {
                Storage::disk('public')->delete($user->foto_url);
            }
            $user->foto_url = $request->file('foto')->store('fotos/' . $user->id, 'public');
            $user->save();
        }

        $user->update($request->only('name', 'email', 'fecha_nacimiento', 'telefono', 'direccion', 'observaciones'));

        return redirect()->route('paciente.perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    public function contactoStore(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono'        => 'required|string|max:50',
            'parentesco'      => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string|max:500',
        ]);

        Auth::user()->contactosEmergencia()->create($request->all());

        return redirect()->route('paciente.perfil')->with('success', 'Contacto agregado.');
    }

    public function contactoUpdate(Request $request, ContactoEmergencia $contacto)
    {
        if ($contacto->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono'        => 'required|string|max:50',
            'parentesco'      => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string|max:500',
        ]);

        $contacto->update($request->all());

        return redirect()->route('paciente.perfil')->with('success', 'Contacto actualizado.');
    }

    public function contactoDestroy(ContactoEmergencia $contacto)
    {
        if ($contacto->user_id !== Auth::id()) {
            abort(403);
        }

        $contacto->delete();

        return redirect()->route('paciente.perfil')->with('success', 'Contacto eliminado.');
    }

    public function alergiaStore(Request $request)
    {
        $request->validate([
            'alergia_id'  => 'required|exists:alergias,id',
            'gravedad'    => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->alergias()->where('alergia_id', $request->alergia_id)->exists()) {
            return redirect()->route('paciente.perfil')->with('error', 'Ya tienes registrada esa alergia.');
        }

        $user->alergias()->attach($request->alergia_id, [
            'gravedad'     => $request->gravedad,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Alergia agregada.');
    }

    public function alergiaUpdate(Request $request, Alergia $alergium)
    {
        $user = Auth::user();

        if (!$user->alergias()->where('alergia_id', $alergium->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'gravedad'     => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $user->alergias()->updateExistingPivot($alergium->id, [
            'gravedad'     => $request->gravedad,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Alergia actualizada.');
    }

    public function alergiaDestroy(Alergia $alergium)
    {
        $user = Auth::user();

        if (!$user->alergias()->where('alergia_id', $alergium->id)->exists()) {
            abort(403);
        }

        $user->alergias()->detach($alergium->id);

        return redirect()->route('paciente.perfil')->with('success', 'Alergia eliminada.');
    }

    public function enfermedadStore(Request $request)
    {
        $request->validate([
            'enfermedad_importante_id' => 'required|exists:enfermedades_importantes,id',
            'fecha_diagnostico'        => 'nullable|date',
            'tratamiento_actual'       => 'nullable|string|max:1000',
            'observaciones'            => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if ($user->enfermedadesImportantes()->where('enfermedad_importante_id', $request->enfermedad_importante_id)->exists()) {
            return redirect()->route('paciente.perfil')->with('error', 'Ya tienes registrada esa enfermedad.');
        }

        $user->enfermedadesImportantes()->attach($request->enfermedad_importante_id, [
            'fecha_diagnostico'  => $request->fecha_diagnostico,
            'tratamiento_actual' => $request->tratamiento_actual,
            'observaciones'      => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Enfermedad agregada.');
    }

    public function enfermedadUpdate(Request $request, EnfermedadImportante $enfermedadImportante)
    {
        $user = Auth::user();

        if (!$user->enfermedadesImportantes()->where('enfermedad_importante_id', $enfermedadImportante->id)->exists()) {
            abort(403);
        }

        $request->validate([
            'fecha_diagnostico'  => 'nullable|date',
            'tratamiento_actual' => 'nullable|string|max:1000',
            'observaciones'      => 'nullable|string|max:500',
        ]);

        $user->enfermedadesImportantes()->updateExistingPivot($enfermedadImportante->id, [
            'fecha_diagnostico'  => $request->fecha_diagnostico,
            'tratamiento_actual' => $request->tratamiento_actual,
            'observaciones'      => $request->observaciones,
        ]);

        return redirect()->route('paciente.perfil')->with('success', 'Enfermedad actualizada.');
    }

    public function enfermedadDestroy(EnfermedadImportante $enfermedadImportante)
    {
        $user = Auth::user();

        if (!$user->enfermedadesImportantes()->where('enfermedad_importante_id', $enfermedadImportante->id)->exists()) {
            abort(403);
        }

        $user->enfermedadesImportantes()->detach($enfermedadImportante->id);

        return redirect()->route('paciente.perfil')->with('success', 'Enfermedad eliminada.');
    }

    public function chatIAHistorial(Request $request)
    {
        $mensajes = IaChatMensaje::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'role'       => $m->role,
                'content'    => $m->content,
                'created_at' => $m->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json($mensajes);
    }

    public function chatIA(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user()->load([
            'citasComoPaciente.medico',
            'citasComoPaciente.consultaMedica.diagnosticos',
            'citasComoPaciente.consultaMedica.dolores',
            'citasComoPaciente.consultaMedica.sintomasRegistrados',
            'citasComoPaciente.consultaMedica.medicamentos',
            'citasComoPaciente.recetas.medicamentos',
        ]);

        $userMessage = $request->input('message');

        IaChatMensaje::create([
            'user_id' => $user->id,
            'role'    => 'user',
            'content' => $userMessage,
        ]);

        $context = "Eres un asistente virtual de salud que ayuda a pacientes a entender sus medicamentos, horarios y tratamientos. ";
        $context .= "Responde SOLO con información basada en los datos clínicos del paciente proporcionados abajo. ";
        $context .= "Si no sabes la respuesta, di que no tienes esa información. NO inventes datos médicos.\n\n";
        $context .= "IMPORTANTE: Puedes CANCELAR citas pendientes usando la herramienta disponible. ";
        $context .= "SIEMPRE pregunta primero al usuario si está seguro antes de ejecutar la cancelación.\n\n";

        $edad = $user->fecha_nacimiento ? now()->diffInYears($user->fecha_nacimiento) : 'No registrada';
        $context .= "Paciente: {$user->name}\nEdad: {$edad}\n\n";

        $citasConDatos = $user->citasComoPaciente->filter(fn($c) => $c->consultaMedica || $c->recetas->count());

        if ($citasConDatos->isEmpty()) {
            $context .= "El paciente no tiene consultas ni recetas registradas en el sistema.\n";
        } else {
            foreach ($citasConDatos as $cita) {
                $context .= "--- Cita del {$cita->fecha_hora->format('d/m/Y')} con Dr. {$cita->medico->name} ---\n";

                if ($consulta = $cita->consultaMedica) {
                    if ($consulta->motivo_consulta) $context .= "Motivo: {$consulta->motivo_consulta}\n";

                    if ($consulta->diagnosticos->count()) {
                        $context .= "Diagnósticos:\n";
                        foreach ($consulta->diagnosticos as $dx) {
                            $context .= "  - {$dx->descripcion}" . ($dx->codigo_cie10 ? " (CIE-10: {$dx->codigo_cie10})" : '') . " [{$dx->tipo}]\n";
                        }
                    }

                    if ($consulta->medicamentos->count()) {
                        $context .= "Medicamentos recetados en consulta:\n";
                        foreach ($consulta->medicamentos as $med) {
                            $context .= "  - {$med->nombre_generico}" . ($med->nombre_comercial ? " ({$med->nombre_comercial})" : '');
                            if ($med->dosis) $context .= ", Dosis: {$med->dosis}";
                            if ($med->frecuencia) $context .= ", Frecuencia: {$med->frecuencia}";
                            if ($med->duracion) $context .= ", Duración: {$med->duracion}";
                            if ($med->indicaciones) $context .= ", Indicaciones: {$med->indicaciones}";
                            $context .= "\n";
                        }
                    }

                    $context .= "Signos vitales: ";
                    $vitals = [];
                    if ($consulta->presion_arterial) $vitals[] = "PA: {$consulta->presion_arterial}";
                    if ($consulta->temperatura) $vitals[] = "Temp: {$consulta->temperatura}°C";
                    if ($consulta->frecuencia_cardiaca) $vitals[] = "FC: {$consulta->frecuencia_cardiaca} lpm";
                    if ($consulta->peso) $vitals[] = "Peso: {$consulta->peso} kg";
                    $context .= ($vitals ? implode(', ', $vitals) : 'No registrados') . "\n";

                    if ($consulta->exploracion_fisica) $context .= "Exploración: {$consulta->exploracion_fisica}\n";
                    if ($consulta->plan_recomendaciones) $context .= "Recomendaciones: {$consulta->plan_recomendaciones}\n";
                    if ($consulta->plan_signos_alarma) $context .= "Signos de alarma: {$consulta->plan_signos_alarma}\n";
                    $context .= "\n";
                }

                if ($cita->recetas->count()) {
                    foreach ($cita->recetas as $receta) {
                        $context .= "Receta - {$receta->fecha_emision?->format('d/m/Y')}:\n";
                        if ($receta->diagnostico) $context .= "  Diagnóstico: {$receta->diagnostico}\n";
                        if ($receta->indicaciones_generales) $context .= "  Indicaciones: {$receta->indicaciones_generales}\n";

                        if ($receta->medicamentos->count()) {
                            $context .= "  Medicamentos:\n";
                            foreach ($receta->medicamentos as $med) {
                                $context .= "    - {$med->medicamento}";
                                if ($med->dosis) $context .= ", Dosis: {$med->dosis}";
                                if ($med->frecuencia) $context .= ", Frecuencia: {$med->frecuencia}";
                                if ($med->duracion) $context .= ", Duración: {$med->duracion}";
                                if ($med->via_administracion) $context .= ", Vía: {$med->via_administracion}";
                                if ($med->indicaciones) $context .= ", Indicaciones: {$med->indicaciones}";
                                $context .= "\n";
                            }
                        }
                        $context .= "\n";
                    }
                }
            }
        }

        $context .= "\nIMPORTANTE: Esto es solo informativo. No reemplaza la consulta médica. Siempre consulta a tu médico ante cualquier duda.";

        $messages = [['role' => 'system', 'content' => $context]];

        $history = IaChatMensaje::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        $tools = [[
            'type' => 'function',
            'function' => [
                'name' => 'cancelar_cita',
                'description' => 'Cancela una cita propia que esté en estado pendiente. Pregunta confirmación antes.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita a cancelar'],
                        'comentario' => ['type' => 'string', 'description' => 'Motivo de la cancelación (opcional)'],
                    ],
                    'required' => ['cita_id'],
                ],
            ],
        ]];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                'Content-Type'  => 'application/json',
            ])->timeout(120)->post(config('services.openrouter.url') . '/chat/completions', [
                'model'       => config('services.openrouter.model'),
                'messages'    => $messages,
                'tools'       => $tools,
                'tool_choice' => 'auto',
                'max_tokens'  => 2048,
            ]);

            if ($response->failed()) {
                Log::error('OpenRouter API error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Error al comunicarse con el asistente. Intenta de nuevo.'], 500);
            }

            $data = $response->json();
            if (isset($data['error'])) {
                Log::error('OpenRouter API error in response', ['error' => $data['error']]);
                return response()->json(['error' => 'El asistente no está disponible: ' . ($data['error']['message'] ?? 'Error desconocido')], 500);
            }

            $choice = $data['choices'][0]['message'] ?? null;
            if ($choice === null) {
                Log::warning('OpenRouter unexpected response', ['body' => $response->body()]);
                return response()->json(['error' => 'Respuesta inesperada del asistente. Intenta de nuevo.'], 500);
            }

            if (isset($choice['tool_calls'])) {
                $messages[] = ['role' => 'assistant', 'content' => $choice['content'] ?? null, 'tool_calls' => $choice['tool_calls']];

                foreach ($choice['tool_calls'] as $toolCall) {
                    $arguments = json_decode($toolCall['function']['arguments'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $arguments = [];
                    }

                    $result = $this->pacienteToolCancelarCita((int)($arguments['cita_id'] ?? 0), $user, $arguments['comentario'] ?? '');

                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'content'      => json_encode($result),
                    ];
                }

                $response2 = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                    'Content-Type'  => 'application/json',
                ])->timeout(120)->post(config('services.openrouter.url') . '/chat/completions', [
                    'model'      => config('services.openrouter.model'),
                    'messages'   => $messages,
                    'max_tokens' => 2048,
                ]);

                if ($response2->failed()) {
                    Log::error('OpenRouter follow-up API error', ['status' => $response2->status(), 'body' => $response2->body()]);
                    return response()->json(['error' => 'Error al procesar la acción.'], 500);
                }

                $data2 = $response2->json();
                $reply = $data2['choices'][0]['message']['content'] ?? 'Acción ejecutada.';
            } else {
                $reply = $choice['content'] ?? null;
                if ($reply === null) {
                    Log::warning('OpenRouter unexpected response (no content)', ['body' => $response->body()]);
                    return response()->json(['error' => 'Respuesta inesperada del asistente. Intenta de nuevo.'], 500);
                }
            }

            IaChatMensaje::create([
                'user_id' => $user->id,
                'role'    => 'assistant',
                'content' => $reply,
            ]);

            return response()->json(['reply' => $reply]);
        } catch (\Exception $e) {
            Log::error('OpenRouter exception: ' . $e->getMessage());
            return response()->json(['error' => 'Error de conexión con el asistente.'], 500);
        }
    }

    private function pacienteToolCancelarCita(int $citaId, $user, string $comentario = ''): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->paciente_id !== $user->id) return ['success' => false, 'error' => 'Esta cita no te pertenece.'];
        if ($cita->estado !== 'pendiente') return ['success' => false, 'error' => 'Solo puedes cancelar citas en estado pendiente.'];
        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes cancelar una cita cuya fecha ya pasó.'];

        $estadoAnterior = $cita->estado;
        $comentarioFinal = $comentario ?: 'Cancelada por el paciente a través del asistente IA.';

        try {
            DB::beginTransaction();

            $cita->update(['estado' => 'cancelada']);

            CitaHistorial::create([
                'cita_id'         => $cita->id,
                'user_id'         => $user->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => 'cancelada',
                'comentario'      => $comentarioFinal,
            ]);

            try {
                broadcast(new \App\Events\CitaEstadoActualizado($cita->id, 'cancelada', $estadoAnterior))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                if ($cita->medico) {
                    $cita->medico->notify(new CitaEstadoNotificacion($cita, 'estado', $estadoAnterior, 'cancelada'));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Cita #{$cita->id} cancelada correctamente.",
                'cita_id' => $cita->id,
                'estado_nuevo' => 'cancelada',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Patient tool cancel error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al cancelar la cita.'];
        }
    }
}
