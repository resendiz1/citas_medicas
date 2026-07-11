<?php

namespace App\Http\Controllers;

use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\IaChatMensaje;
use App\Models\MedicoDocumento;
use App\Models\Mensaje;
use App\Models\TipoMedico;
use App\Models\User;
use App\Events\CitaEstadoActualizado;
use App\Events\MensajeEnviado;
use App\Notifications\CitaEstadoNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MedicoController extends Controller
{
    public function pacienteShow($id)
    {
        $paciente = User::where('role', 'paciente')
            ->with('contactosEmergencia', 'alergias', 'enfermedadesImportantes')
            ->findOrFail($id);

        $citas = $paciente->citasComoPaciente()
            ->where('medico_id', Auth::id())
            ->orderBy('fecha_hora', 'desc')
            ->get();

        if ($citas->isEmpty()) {
            abort(403, 'No tienes citas con este paciente.');
        }

        return view('medico.paciente-show', compact('paciente', 'citas'));
    }

    public function documentosStore(Request $request)
    {
        $request->validate([
            'documento' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:20480',
            'nombre'    => 'nullable|string|max:255',
        ]);

        $perfil = Auth::user()->medicoPerfil;

        if (!$perfil) {
            return redirect()->back()->with('error', 'No tienes un perfil de médico configurado.');
        }

        $file = $request->file('documento');
        $nombreOriginal = $file->getClientOriginalName();
        $ruta = $file->store('medico-documentos/' . $perfil->id, 'public');

        MedicoDocumento::create([
            'medico_perfil_id' => $perfil->id,
            'nombre'           => $request->input('nombre'),
            'nombre_original'  => $nombreOriginal,
            'ruta_archivo'     => $ruta,
            'tipo_mime'        => $file->getMimeType(),
            'tamano'           => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Documento subido correctamente.');
    }

    public function documentosDestroy($id)
    {
        $doc = MedicoDocumento::findOrFail($id);

        if ($doc->medicoPerfil->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::disk('public')->delete($doc->ruta_archivo);
        $doc->delete();

        return redirect()->back()->with('success', 'Documento eliminado.');
    }

    public function perfilShow()
    {
        $user = Auth::user();
        $perfil = $user->medicoPerfil;
        $tiposMedico = TipoMedico::all();
        $documentos = optional($perfil)->documentos ?? collect();

        return view('medico.perfil', compact('user', 'perfil', 'tiposMedico', 'documentos'));
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
            'tipo_medico_id'   => 'nullable|exists:tipo_medicos,id',
            'cedula_profesional'  => 'nullable|string|max:100',
            'universidad'      => 'nullable|string|max:255',
            'experiencia_anios' => 'nullable|integer|min:0|max:100',
            'descripcion'      => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('foto')) {
            if ($user->foto_url) {
                Storage::disk('public')->delete($user->foto_url);
            }
            $user->foto_url = $request->file('foto')->store('fotos/' . $user->id, 'public');
            $user->save();
        }

        $user->update($request->only('name', 'email', 'fecha_nacimiento', 'telefono', 'direccion', 'observaciones'));

        $perfil = $user->medicoPerfil;
        if ($perfil) {
            $perfil->update(array_merge(
                $request->only('tipo_medico_id', 'cedula_profesional', 'universidad', 'experiencia_anios', 'descripcion'),
                ['activo' => $request->boolean('activo')]
            ));
        }

        return redirect()->route('medico.perfil')->with('success', 'Perfil actualizado correctamente.');
    }

    public function historialCitas()
    {
        $user = Auth::user();

        $citas = $user->citasComoMedico()
            ->with('paciente', 'consultaMedica', 'medico', 'ultimaReceta')
            ->orderBy('fecha_hora', 'desc')
            ->paginate(15);

        return view('medico.historial-citas', compact('citas'));
    }

    public function toggleActivo()
    {
        $perfil = Auth::user()->medicoPerfil;

        if (!$perfil) {
            return redirect()->back()->with('error', 'No tienes un perfil de médico.');
        }

        $perfil->update(['activo' => !$perfil->activo]);

        return redirect()->route('medico.perfil')->with('success', $perfil->activo ? 'Marcado como activo.' : 'Marcado como inactivo.');
    }

    public function chatIAIndex()
    {
        return view('medico.chat-ia');
    }

    public function chatIAHistorial(Request $request)
    {
        $mensajes = IaChatMensaje::where('user_id', $request->user()->id)
            ->whereNull('medico_id')
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

    public function chatIASend(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $user = Auth::user()->load([
            'medicoPerfil.tipoMedico',
            'medicoPerfil.documentos',
            'horarios',
            'bloqueos',
        ]);

        $userMessage = $request->input('message');

        IaChatMensaje::create([
            'user_id' => $user->id,
            'role'    => 'user',
            'content' => $userMessage,
        ]);

        $context = "Eres un asistente virtual de salud experto para médicos. ";
        $context .= "Responde preguntas sobre TODA la información del médico: sus datos personales, horarios, bloqueos, pacientes, citas, consultas, diagnósticos, recetas y más. ";
        $context .= "Usa SOLO la información proporcionada abajo. No inventes datos.\n\n";
        $context .= "IMPORTANTE: Puedes MODIFICAR el estado de las citas usando las herramientas disponibles. ";
        $context .= "Para acciones destructivas (cancelar, no_asistio), SIEMPRE pregunta primero al usuario si está seguro antes de ejecutar la herramienta. ";
        $context .= "Para las demás acciones (confirmar, pasar a espera, pasar a consulta, finalizar), puedes ejecutarlas directamente.\n\n";

        $context .= "=== DATOS DEL MÉDICO ===\n";
        $telefono = $user->telefono ?? 'No registrado';
        $direccion = $user->direccion ?? 'No registrada';
        $fechaNac = $user->fecha_nacimiento ? $user->fecha_nacimiento->format('d/m/Y') : 'No registrada';

        $context .= "Nombre: {$user->name}\n";
        $context .= "Email: {$user->email}\n";
        $context .= "Teléfono: {$telefono}\n";
        $context .= "Fecha de nacimiento: {$fechaNac}\n";
        $context .= "Dirección: {$direccion}\n";

        $perfil = $user->medicoPerfil;
        if ($perfil) {
            $especialidad = optional($perfil->tipoMedico)->nombre_tipo_medico ?? 'No asignada';
            $cedula = $perfil->cedula_profesional ?? 'No registrada';
            $universidad = $perfil->universidad ?? 'No registrada';
            $experiencia = $perfil->experiencia_anios ?? 'No registrados';
            $descripcion = $perfil->descripcion ?? 'No registrada';
            $intervalo = $perfil->intervalo_minutos ?? 'No configurado';

            $context .= "Especialidad: {$especialidad}\n";
            $context .= "Cédula profesional: {$cedula}\n";
            $context .= "Universidad: {$universidad}\n";
            $context .= "Años de experiencia: {$experiencia}\n";
            $context .= "Descripción: {$descripcion}\n";
            $context .= "Intervalo de citas: {$intervalo} minutos\n";
            $context .= "Activo: " . ($perfil->activo ? 'Sí' : 'No') . "\n";
            $context .= "Aprobado: " . ($perfil->aprobado ? 'Sí' : 'No') . "\n";

            if ($perfil->documentos->count()) {
                $context .= "Documentos subidos ({$perfil->documentos->count()}):\n";
                foreach ($perfil->documentos as $doc) {
                    $context .= "  - {$doc->nombre_original} ({$doc->tipo_mime})\n";
                }
            }
        }
        $context .= "\n";

        $horarios = $user->horarios;
        if ($horarios->count()) {
            $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $context .= "=== HORARIOS ===\n";
            foreach ($horarios as $h) {
                $dia = $dias[$h->dia_semana] ?? "Día {$h->dia_semana}";
                $context .= "{$dia}: {$h->hora_inicio} - {$h->hora_fin}" . ($h->activo ? '' : ' (inactivo)') . "\n";
            }
            $context .= "\n";
        }

        $bloqueos = $user->bloqueos;
        if ($bloqueos->count()) {
            $context .= "=== BLOQUEOS ===\n";
            foreach ($bloqueos as $b) {
                $context .= "{$b->fecha_inicio->format('d/m/Y')} - {$b->fecha_fin->format('d/m/Y')}: {$b->motivo}\n";
            }
            $context .= "\n";
        }

        $citas = CitaMedica::where('medico_id', $user->id)
            ->with([
                'paciente.contactosEmergencia',
                'paciente.alergias',
                'paciente.enfermedadesImportantes',
                'consultaMedica.diagnosticos',
                'consultaMedica.medicamentos',
                'consultaMedica.dolores',
                'recetas.medicamentos',
                'recetas.documentos',
            ])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        if ($citas->isEmpty()) {
            $context .= "=== CITAS ===\nNo tienes citas registradas.\n\n";
        } else {
            $context .= "=== CITAS (Total: {$citas->count()}) ===\n\n";
            foreach ($citas as $cita) {
                $context .= "--- Cita #{$cita->id} ---\n";
                $context .= "Paciente: {$cita->paciente->name}\n";
                $context .= "Email paciente: {$cita->paciente->email}\n";
                $telPaciente = $cita->paciente->telefono ?? 'No registrado';
                $context .= "Teléfono paciente: {$telPaciente}\n";
                $context .= "Fecha: {$cita->fecha_hora->format('d/m/Y H:i')}\n";
                $context .= "Estado: {$cita->estado}\n";
                if ($cita->motivo) $context .= "Motivo: {$cita->motivo}\n";

                $paciente = $cita->paciente;
                if ($paciente->alergias->count()) {
                    $context .= "Alergias del paciente:\n";
                    foreach ($paciente->alergias as $al) {
                        $context .= "  - {$al->nombre}" . ($al->pivot->gravedad ? " ({$al->pivot->gravedad})" : '') . "\n";
                    }
                }
                if ($paciente->enfermedadesImportantes->count()) {
                    $context .= "Enfermedades del paciente:\n";
                    foreach ($paciente->enfermedadesImportantes as $enf) {
                        $context .= "  - {$enf->nombre}" . ($enf->pivot->observaciones ? ": {$enf->pivot->observaciones}" : '') . "\n";
                    }
                }

                if ($consulta = $cita->consultaMedica) {
                    $context .= "[CONSULTA]\n";
                    if ($consulta->motivo_consulta) $context .= "Motivo consulta: {$consulta->motivo_consulta}\n";
                    if ($consulta->sintomas) $context .= "Síntomas: {$consulta->sintomas}\n";

                    if ($consulta->dolores->count()) {
                        $context .= "Dolores:\n";
                        foreach ($consulta->dolores as $d) {
                            $context .= "  - Ubicación: {$d->ubicacion}, Intensidad: {$d->intensidad}/10, Duración: {$d->duracion}\n";
                        }
                    }

                    $vitals = [];
                    if ($consulta->presion_arterial) $vitals[] = "PA: {$consulta->presion_arterial}";
                    if ($consulta->temperatura) $vitals[] = "Temp: {$consulta->temperatura}°C";
                    if ($consulta->frecuencia_cardiaca) $vitals[] = "FC: {$consulta->frecuencia_cardiaca} lpm";
                    if ($consulta->frecuencia_respiratoria) $vitals[] = "FR: {$consulta->frecuencia_respiratoria} rpm";
                    if ($consulta->peso) $vitals[] = "Peso: {$consulta->peso} kg";
                    if ($consulta->estatura) $vitals[] = "Estatura: {$consulta->estatura} m";
                    if ($consulta->imc) $vitals[] = "IMC: {$consulta->imc}";
                    if ($vitals) $context .= "Signos vitales: " . implode(', ', $vitals) . "\n";

                    if ($consulta->exploracion_fisica) $context .= "Exploración física: {$consulta->exploracion_fisica}\n";
                    if ($consulta->diagnostico_descripcion) $context .= "Diagnóstico: {$consulta->diagnostico_descripcion}\n";

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
                    if ($consulta->plan_recomendaciones) $context .= "Recomendaciones: {$consulta->plan_recomendaciones}\n";
                    if ($consulta->plan_signos_alarma) $context .= "Signos de alarma: {$consulta->plan_signos_alarma}\n";
                }

                if ($cita->recetas->count()) {
                    foreach ($cita->recetas as $receta) {
                        $context .= "[RECETA #{$receta->id}]\n";
                        if ($receta->fecha_emision) $context .= "Fecha emisión: {$receta->fecha_emision->format('d/m/Y')}\n";
                        if ($receta->diagnostico) $context .= "Diagnóstico: {$receta->diagnostico}\n";
                        if ($receta->indicaciones_generales) $context .= "Indicaciones: {$receta->indicaciones_generales}\n";
                        if ($receta->notas_adicionales) $context .= "Notas: {$receta->notas_adicionales}\n";

                        if ($receta->medicamentos->count()) {
                            $context .= "Medicamentos:\n";
                            foreach ($receta->medicamentos as $med) {
                                $context .= "  - {$med->medicamento}";
                                if ($med->dosis) $context .= ", Dosis: {$med->dosis}";
                                if ($med->frecuencia) $context .= ", Frecuencia: {$med->frecuencia}";
                                if ($med->duracion) $context .= ", Duración: {$med->duracion}";
                                if ($med->via_administracion) $context .= ", Vía: {$med->via_administracion}";
                                if ($med->indicaciones) $context .= ", Indicaciones: {$med->indicaciones}";
                                $context .= "\n";
                            }
                        }
                        if ($receta->documentos->count()) {
                            $context .= "Documentos adjuntos: {$receta->documentos->count()}\n";
                        }
                    }
                }
                $context .= "\n";
            }
        }

        $context .= "\nIMPORTANTE: Esto es solo informativo. Siempre verifica con el historial clínico real.";

        $messages = [['role' => 'system', 'content' => $context]];

        $history = IaChatMensaje::where('user_id', $user->id)
            ->whereNull('medico_id')
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                'Content-Type'  => 'application/json',
            ])->timeout(120)->post(config('services.openrouter.url') . '/chat/completions', [
                'model'       => config('services.openrouter.model'),
                'messages'    => $messages,
                'tools'       => $this->getToolsArray(),
                'tool_choice' => 'auto',
                'max_tokens'  => 2048,
            ]);

            if ($response->failed()) {
                Log::error('OpenRouter API error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Error al comunicarse con el asistente.'], 500);
            }

            $data = $response->json();
            if (isset($data['error'])) {
                Log::error('OpenRouter API error', ['error' => $data['error']]);
                return response()->json(['error' => 'El asistente no está disponible.'], 500);
            }

            $choice = $data['choices'][0]['message'] ?? null;
            if ($choice === null) {
                Log::warning('OpenRouter unexpected response', ['body' => $response->body()]);
                return response()->json(['error' => 'Respuesta inesperada del asistente.'], 500);
            }

            if (isset($choice['tool_calls'])) {
                $messages[] = ['role' => 'assistant', 'content' => $choice['content'] ?? null, 'tool_calls' => $choice['tool_calls']];

                foreach ($choice['tool_calls'] as $toolCall) {
                    $arguments = json_decode($toolCall['function']['arguments'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $arguments = [];
                    }

                    $result = $this->executeChatTool($toolCall['function']['name'], $arguments, $user);

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
                    return response()->json(['error' => 'Respuesta inesperada del asistente.'], 500);
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

    private function getToolsArray(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'confirmar_cita',
                    'description' => 'Confirma una cita pendiente. Estado requerido: pendiente. La cita no debe haber pasado (fecha debe ser hoy o futura).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita a confirmar'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cancelar_cita',
                    'description' => 'Cancela una cita. Estados válidos: pendiente, confirmada, en_espera. No se puede cancelar citas pasadas. Pregunta confirmación antes de ejecutar.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita a cancelar'],
                            'comentario' => ['type' => 'string', 'description' => 'Motivo de la cancelación (opcional)'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'reprogramar_cita',
                    'description' => 'Reprograma una cita a una nueva fecha futura. Estados válidos: pendiente, confirmada. La cita no debe haber pasado. La cita pasará a "reprogramada" y el paciente deberá confirmar.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita a reprogramar'],
                            'nueva_fecha' => ['type' => 'string', 'description' => 'Nueva fecha en formato Y-m-d H:i (ej. 2026-07-15 10:00). Debe ser futura.'],
                        ],
                        'required' => ['cita_id', 'nueva_fecha'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'marcar_no_asistio',
                    'description' => 'Marca cita como "no asistió". Estados: pendiente, confirmada, en_espera. Solo el mismo día de la cita. Pregunta confirmación.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'pasar_en_espera',
                    'description' => 'Paciente en sala de espera. Estado requerido: confirmada. Solo el mismo día de la cita.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'pasar_en_consulta',
                    'description' => 'Paciente en consulta. Estado requerido: en_espera. Solo el mismo día de la cita.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'finalizar_cita',
                    'description' => 'Finaliza consulta médica. Estado requerido: en_consulta. Solo el mismo día de la cita.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita a finalizar'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
        ];
    }

    private function executeChatTool(string $name, array $args, User $user): array
    {
        return match ($name) {
            'confirmar_cita'   => $this->toolConfirmarCita((int)($args['cita_id'] ?? 0), $user),
            'cancelar_cita'    => $this->toolCancelarCita((int)($args['cita_id'] ?? 0), $user, $args['comentario'] ?? ''),
            'reprogramar_cita' => $this->toolReprogramarCita((int)($args['cita_id'] ?? 0), $args['nueva_fecha'] ?? '', $user),
            'marcar_no_asistio' => $this->toolMarcarNoAsistio((int)($args['cita_id'] ?? 0), $user),
            'pasar_en_espera'  => $this->toolPasarEnEspera((int)($args['cita_id'] ?? 0), $user),
            'pasar_en_consulta' => $this->toolPasarEnConsulta((int)($args['cita_id'] ?? 0), $user),
            'finalizar_cita'   => $this->toolFinalizarCita((int)($args['cita_id'] ?? 0), $user),
            default            => ['success' => false, 'error' => "Función desconocida: {$name}"],
        };
    }

    private function toolConfirmarCita(int $citaId, User $user): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];
        if ($cita->estado !== 'pendiente') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo se pueden confirmar citas pendientes."];
        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes confirmar una cita cuya fecha ya pasó.'];

        return $this->aplicarTransicion($cita, 'confirmada', $user, 'Cita confirmada por el médico.');
    }

    private function toolCancelarCita(int $citaId, User $user, string $comentario = ''): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];

        $permitidos = ['pendiente', 'confirmada', 'en_espera'];
        if (!in_array($cita->estado, $permitidos)) {
            return ['success' => false, 'error' => "No se puede cancelar una cita en estado '{$cita->estado}'."];
        }

        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes cancelar una cita cuya fecha ya pasó.'];

        $comentarioFinal = $comentario ?: 'Cancelada por el médico a través del asistente IA.';

        return $this->aplicarTransicion($cita, 'cancelada', $user, $comentarioFinal);
    }

    private function toolReprogramarCita(int $citaId, string $nuevaFecha, User $user): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];

        $permitidos = ['pendiente', 'confirmada'];
        if (!in_array($cita->estado, $permitidos)) {
            return ['success' => false, 'error' => "No se puede reprogramar una cita en estado '{$cita->estado}'."];
        }

        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes reprogramar una cita cuya fecha ya pasó.'];

        try {
            $fecha = \Carbon\Carbon::parse($nuevaFecha);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => "Formato de fecha inválido: '{$nuevaFecha}'. Usa formato Y-m-d H:i (ej. 2026-07-15 10:00)."];
        }

        if ($fecha->lessThan(now()->subMinutes(2))) {
            return ['success' => false, 'error' => 'La nueva fecha debe ser actual o futura.'];
        }

        $comentarioFinal = "Reprogramada vía asistente IA. Nueva fecha: {$fecha->format('d/m/Y H:i')}.";

        return $this->aplicarTransicion($cita, 'reprogramada', $user, $comentarioFinal, ['fecha_reprogramada' => $fecha->format('Y-m-d H:i:s'), 'reprogramacion_rechazada' => null]);
    }

    private function toolMarcarNoAsistio(int $citaId, User $user): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];

        $permitidos = ['pendiente', 'confirmada', 'en_espera'];
        if (!in_array($cita->estado, $permitidos)) {
            return ['success' => false, 'error' => "No se puede marcar como no asistió una cita en estado '{$cita->estado}'."];
        }

        if (!$cita->fecha_hora->isToday()) {
            return ['success' => false, 'error' => 'Solo puedes marcar como no asistió citas del día de hoy.'];
        }

        return $this->aplicarTransicion($cita, 'no_asistio', $user, 'Paciente no asistió a la cita.');
    }

    private function toolPasarEnEspera(int $citaId, User $user): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];
        if ($cita->estado !== 'confirmada') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo se pueden pasar a espera citas confirmadas."];
        if (!$cita->fecha_hora->isToday()) return ['success' => false, 'error' => 'Solo puedes pasar a espera citas del día de hoy.'];

        return $this->aplicarTransicion($cita, 'en_espera', $user, 'Paciente en sala de espera.');
    }

    private function toolPasarEnConsulta(int $citaId, User $user): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];
        if ($cita->estado !== 'en_espera') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo se pueden pasar a consulta citas en espera."];
        if (!$cita->fecha_hora->isToday()) return ['success' => false, 'error' => 'Solo puedes pasar a consulta citas del día de hoy.'];

        return $this->aplicarTransicion($cita, 'en_consulta', $user, 'Inicio de consulta médica.');
    }

    private function toolFinalizarCita(int $citaId, User $user): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->medico_id !== $user->id) return ['success' => false, 'error' => 'No eres el médico asignado a esta cita.'];
        if ($cita->estado !== 'en_consulta') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo se pueden finalizar citas en consulta."];
        if (!$cita->fecha_hora->isToday()) return ['success' => false, 'error' => 'Solo puedes finalizar citas del día de hoy.'];

        return $this->aplicarTransicion($cita, 'finalizada', $user, 'Consulta finalizada.');
    }

    private function aplicarTransicion(CitaMedica $cita, string $nuevoEstado, User $user, string $comentario, array $extraData = []): array
    {
        $estadoAnterior = $cita->estado;

        $transitions = [
            'pendiente'    => ['confirmada', 'cancelada', 'reprogramada', 'no_asistio'],
            'confirmada'   => ['en_espera', 'cancelada', 'reprogramada', 'no_asistio'],
            'en_espera'    => ['en_consulta', 'cancelada', 'no_asistio'],
            'en_consulta'  => ['finalizada'],
            'finalizada'   => [],
            'cancelada'    => [],
            'no_asistio'   => [],
            'reprogramada' => [],
        ];

        if (!isset($transitions[$estadoAnterior]) || !in_array($nuevoEstado, $transitions[$estadoAnterior])) {
            return ['success' => false, 'error' => "Transición no válida de '{$estadoAnterior}' a '{$nuevoEstado}'."];
        }

        try {
            DB::beginTransaction();

            $updateData = array_merge(['estado' => $nuevoEstado], $extraData);
            $cita->update($updateData);

            CitaHistorial::create([
                'cita_id'         => $cita->id,
                'user_id'         => $user->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo'    => $nuevoEstado,
                'comentario'      => $comentario,
            ]);

            try {
                broadcast(new CitaEstadoActualizado($cita->id, $nuevoEstado, $estadoAnterior))->toOthers();
            } catch (\Throwable $e) {
                report($e);
            }

            $mensajesChat = [
                'confirmada' => '✅ Cita confirmada por ' . $user->name . '.',
                'cancelada'  => '❌ Cita cancelada. ' . $comentario,
                'finalizada' => '🏁 Consulta finalizada.',
            ];
            if (isset($mensajesChat[$nuevoEstado])) {
                try {
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
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            try {
                if ($cita->paciente) {
                    $cita->paciente->notify(new CitaEstadoNotificacion($cita, 'estado', $estadoAnterior, $nuevoEstado));
                }
                if ($cita->medico && $user->id !== $cita->medico_id) {
                    $cita->medico->notify(new CitaEstadoNotificacion($cita, 'estado', $estadoAnterior, $nuevoEstado));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Cita #{$cita->id} cambiada de '{$estadoAnterior}' a '{$nuevoEstado}'.",
                'cita_id' => $cita->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tool transition error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al actualizar el estado de la cita.'];
        }
    }
}
