<?php

namespace App\Http\Controllers;

use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\IaChatMensaje;
use App\Models\MedicoBloqueo;
use App\Models\MedicoDocumento;
use App\Models\MedicoHorario;
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
        $context .= "IMPORTANTE: Tienes estas herramientas disponibles:\n";
        $context .= "- Citas: confirmar_cita, cancelar_cita, reprogramar_cita, marcar_no_asistio, pasar_en_espera, pasar_en_consulta, finalizar_cita\n";
        $context .= "- Horarios: listar_horarios, crear_horario, eliminar_horario\n";
        $context .= "- Bloqueos: listar_bloqueos, crear_bloqueo, eliminar_bloqueo\n";
        $context .= "- Perfil: ver_mi_perfil, actualizar_perfil, actualizar_intervalo, listar_documentos, eliminar_documento\n";
        $context .= "- Pacientes: ver_perfil_paciente\n";
        $context .= "Para acciones destructivas (cancelar cita, no_asistio, eliminar horario, eliminar bloqueo, eliminar_documento), SIEMPRE pregunta primero al usuario si está seguro antes de ejecutar la herramienta. ";
        $context .= "CRÍTICO: Siempre debes llamar a la herramienta real para ejecutar cualquier acción. NUNCA digas 'Listo, ya se actualizó' o 'Hecho' sin haber llamado a la herramienta correspondiente. ";
        $context .= "Si el usuario te pide actualizar su perfil, cambiar intervalo, crear horario/bloqueo, etc., DEBES llamar a la función tool. ";
        $context .= "No confirmes una acción hasta que la herramienta se haya ejecutado y devuelto éxito. Las falsas confirmaciones (decir que se hizo sin llamar a la herramienta) son inaceptables.\n";
        $context .= "NUNCA muestres código, etiquetas HTML, XML, markdown de código, ni nada entre <> en tus respuestas. Responde solo en lenguaje natural.\n\n";

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

            Log::info('MedicoChat OpenRouter response', [
                'has_tool_calls' => isset($data['choices'][0]['message']['tool_calls']),
                'tool_count' => isset($data['choices'][0]['message']['tool_calls']) ? count($data['choices'][0]['message']['tool_calls']) : 0,
                'tool_names' => isset($data['choices'][0]['message']['tool_calls']) ? array_map(fn($tc) => $tc['function']['name'], $data['choices'][0]['message']['tool_calls']) : [],
                'content_preview' => substr($data['choices'][0]['message']['content'] ?? '', 0, 200),
            ]);

            $choice = $data['choices'][0]['message'] ?? null;
            if ($choice === null) {
                Log::warning('OpenRouter unexpected response', ['body' => $response->body()]);
                return response()->json(['error' => 'Respuesta inesperada del asistente.'], 500);
            }

            $maxRounds = 5;
            $round = 0;

            while (isset($choice['tool_calls']) && $round < $maxRounds) {
                $round++;
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

                $responseNext = Http::withHeaders([
                    'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                    'Content-Type'  => 'application/json',
                ])->timeout(120)->post(config('services.openrouter.url') . '/chat/completions', [
                    'model'      => config('services.openrouter.model'),
                    'messages'   => $messages,
                    'max_tokens' => 2048,
                ]);

                if ($responseNext->failed()) {
                    Log::error('OpenRouter follow-up API error', ['status' => $responseNext->status(), 'body' => $responseNext->body()]);
                    return response()->json(['error' => 'Error al procesar la acción.'], 500);
                }

                $dataNext = $responseNext->json();
                $choice = $dataNext['choices'][0]['message'] ?? null;
                if ($choice === null) {
                    return response()->json(['error' => 'Respuesta inesperada del asistente.'], 500);
                }

                Log::info('MedicoChat OpenRouter response (round ' . $round . ')', [
                    'has_tool_calls' => isset($choice['tool_calls']),
                    'tool_count' => isset($choice['tool_calls']) ? count($choice['tool_calls']) : 0,
                    'tool_names' => isset($choice['tool_calls']) ? array_map(fn($tc) => $tc['function']['name'], $choice['tool_calls']) : [],
                    'content_preview' => substr($choice['content'] ?? '', 0, 200),
                ]);
            }

            $reply = $choice['content'] ?? null;
            if ($reply === null) {
                if ($round > 0) {
                    $reply = 'Acción ejecutada.';
                } else {
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
        return array_merge([
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
            [
                'type' => 'function',
                'function' => [
                    'name' => 'listar_horarios',
                    'description' => 'Lista todos los horarios del médico con día, hora inicio, hora fin y estado activo/inactivo.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'crear_horario',
                    'description' => 'Crea un nuevo horario de trabajo. Día 0=Domingo, 1=Lunes, ..., 6=Sábado. hora_inicio y hora_fin en formato H:i (ej. 09:00, 17:30). hora_fin debe ser posterior a hora_inicio.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'dia_semana' => ['type' => 'integer', 'description' => 'Día de la semana: 0=Domingo, 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado'],
                            'hora_inicio' => ['type' => 'string', 'description' => 'Hora de inicio en formato H:i (ej. 09:00)'],
                            'hora_fin' => ['type' => 'string', 'description' => 'Hora de fin en formato H:i (ej. 17:00). Debe ser después de hora_inicio.'],
                        ],
                        'required' => ['dia_semana', 'hora_inicio', 'hora_fin'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'eliminar_horario',
                    'description' => 'Elimina un horario por su ID. Pregunta confirmación antes de ejecutar.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'horario_id' => ['type' => 'integer', 'description' => 'ID del horario a eliminar'],
                        ],
                        'required' => ['horario_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'listar_bloqueos',
                    'description' => 'Lista todos los bloqueos de disponibilidad del médico con fechas y motivo.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'crear_bloqueo',
                    'description' => 'Crea un bloqueo de disponibilidad. El médico no estará disponible entre fecha_inicio y fecha_fin. Las fechas deben ser actuales o futuras.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'fecha_inicio' => ['type' => 'string', 'description' => 'Fecha de inicio en formato Y-m-d (ej. 2026-07-20)'],
                            'fecha_fin' => ['type' => 'string', 'description' => 'Fecha de fin en formato Y-m-d (ej. 2026-07-25). Debe ser igual o posterior a fecha_inicio.'],
                            'motivo' => ['type' => 'string', 'description' => 'Motivo del bloqueo (opcional)'],
                        ],
                        'required' => ['fecha_inicio', 'fecha_fin'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'eliminar_bloqueo',
                    'description' => 'Elimina un bloqueo por su ID. Pregunta confirmación antes de ejecutar.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'bloqueo_id' => ['type' => 'integer', 'description' => 'ID del bloqueo a eliminar'],
                        ],
                        'required' => ['bloqueo_id'],
                    ],
                ],
            ],
        ], $this->getProfileToolsArray());
    }

    private function getProfileToolsArray(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'actualizar_perfil',
                    'description' => 'Actualiza los datos de tu perfil. Campos: nombre, email, teléfono, dirección, fecha de nacimiento, especialidad (tipo_medico_id), cédula profesional, universidad, años de experiencia, descripción.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Nombre completo'],
                            'email' => ['type' => 'string', 'description' => 'Correo electrónico'],
                            'telefono' => ['type' => 'string', 'description' => 'Teléfono de contacto'],
                            'direccion' => ['type' => 'string', 'description' => 'Dirección'],
                            'fecha_nacimiento' => ['type' => 'string', 'description' => 'Fecha de nacimiento en formato Y-m-d (ej. 1990-05-15)'],
                            'tipo_medico_id' => ['type' => 'integer', 'description' => 'ID de especialidad (1=Medicina General, 2=Cardiología, 3=Pediatría, 4=Dermatología, 5=Ginecología, 6=Neurología, 7=Traumatología, 8=Oftalmología, 9=Otorrinolaringología, 10=Psiquiatría)'],
                            'cedula_profesional' => ['type' => 'string', 'description' => 'Cédula profesional'],
                            'universidad' => ['type' => 'string', 'description' => 'Universidad de egreso'],
                            'experiencia_anios' => ['type' => 'integer', 'description' => 'Años de experiencia (0-100)'],
                            'descripcion' => ['type' => 'string', 'description' => 'Descripción profesional'],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'actualizar_intervalo',
                    'description' => 'Cambia el intervalo entre citas (minutos). Valores permitidos: 15, 20, 30, 45, 60, 90, 120.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'intervalo_minutos' => ['type' => 'integer', 'description' => 'Intervalo en minutos: 15, 20, 30, 45, 60, 90 o 120'],
                        ],
                        'required' => ['intervalo_minutos'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'listar_documentos',
                    'description' => 'Lista todos los documentos profesionales subidos.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'eliminar_documento',
                    'description' => 'Elimina un documento por su ID. Pregunta confirmación antes.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'documento_id' => ['type' => 'integer', 'description' => 'ID del documento a eliminar'],
                        ],
                        'required' => ['documento_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'ver_mi_perfil',
                    'description' => 'Muestra tus datos de perfil: nombre, email, teléfono, especialidad, cédula, universidad, experiencia, descripción, estado activo/inactivo.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'ver_perfil_paciente',
                    'description' => 'Muestra los datos de un paciente: información personal, alergias, enfermedades importantes y contactos de emergencia.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'paciente_id' => ['type' => 'integer', 'description' => 'ID del paciente'],
                        ],
                        'required' => ['paciente_id'],
                    ],
                ],
            ],
        ];
    }

    private function executeChatTool(string $name, array $args, User $user): array
    {
        return match ($name) {
            'confirmar_cita'    => $this->toolConfirmarCita((int)($args['cita_id'] ?? 0), $user),
            'cancelar_cita'     => $this->toolCancelarCita((int)($args['cita_id'] ?? 0), $user, $args['comentario'] ?? ''),
            'reprogramar_cita'  => $this->toolReprogramarCita((int)($args['cita_id'] ?? 0), $args['nueva_fecha'] ?? '', $user),
            'marcar_no_asistio' => $this->toolMarcarNoAsistio((int)($args['cita_id'] ?? 0), $user),
            'pasar_en_espera'   => $this->toolPasarEnEspera((int)($args['cita_id'] ?? 0), $user),
            'pasar_en_consulta' => $this->toolPasarEnConsulta((int)($args['cita_id'] ?? 0), $user),
            'finalizar_cita'    => $this->toolFinalizarCita((int)($args['cita_id'] ?? 0), $user),
            'listar_horarios'   => $this->toolListarHorarios($user),
            'crear_horario'     => $this->toolCrearHorario((int)($args['dia_semana'] ?? 0), $args['hora_inicio'] ?? '', $args['hora_fin'] ?? '', $user),
            'eliminar_horario'  => $this->toolEliminarHorario((int)($args['horario_id'] ?? 0), $user),
            'listar_bloqueos'   => $this->toolListarBloqueos($user),
            'crear_bloqueo'       => $this->toolCrearBloqueo($args['fecha_inicio'] ?? '', $args['fecha_fin'] ?? '', $args['motivo'] ?? '', $user),
            'eliminar_bloqueo'    => $this->toolEliminarBloqueo((int)($args['bloqueo_id'] ?? 0), $user),
            'actualizar_perfil'   => $this->toolActualizarPerfil($args, $user),
            'actualizar_intervalo' => $this->toolActualizarIntervalo((int)($args['intervalo_minutos'] ?? 0), $user),
            'listar_documentos'   => $this->toolListarDocumentos($user),
            'eliminar_documento'  => $this->toolEliminarDocumento((int)($args['documento_id'] ?? 0), $user),
            'ver_mi_perfil'       => $this->toolVerMiPerfil($user),
            'ver_perfil_paciente' => $this->toolVerPerfilPaciente((int)($args['paciente_id'] ?? 0), $user),
            default               => ['success' => false, 'error' => "Función desconocida: {$name}"],
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

    private function toolListarHorarios(User $user): array
    {
        $horarios = MedicoHorario::where('medico_id', $user->id)->orderBy('dia_semana')->orderBy('hora_inicio')->get();
        if ($horarios->isEmpty()) return ['success' => true, 'message' => 'No tienes horarios registrados.', 'data' => []];

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $result = [];
        foreach ($horarios as $h) {
            $result[] = [
                'id' => $h->id,
                'dia' => $dias[$h->dia_semana] ?? "Día {$h->dia_semana}",
                'dia_semana' => $h->dia_semana,
                'hora_inicio' => substr($h->hora_inicio, 0, 5),
                'hora_fin' => substr($h->hora_fin, 0, 5),
                'activo' => $h->activo,
            ];
        }
        return ['success' => true, 'message' => count($result) . ' horario(s) encontrado(s).', 'data' => $result];
    }

    private function toolCrearHorario(int $diaSemana, string $horaInicio, string $horaFin, User $user): array
    {
        if ($diaSemana < 0 || $diaSemana > 6) return ['success' => false, 'error' => 'Día inválido. Usa 0=Domingo a 6=Sábado.'];
        if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio) || !preg_match('/^\d{2}:\d{2}$/', $horaFin)) {
            return ['success' => false, 'error' => 'Formato de hora inválido. Usa H:i (ej. 09:00).'];
        }
        if ($horaFin <= $horaInicio) return ['success' => false, 'error' => 'hora_fin debe ser posterior a hora_inicio.'];

        try {
            MedicoHorario::create([
                'medico_id'   => $user->id,
                'dia_semana'  => $diaSemana,
                'hora_inicio' => $horaInicio,
                'hora_fin'    => $horaFin,
                'activo'      => true,
            ]);

            $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $dia = $dias[$diaSemana] ?? "Día {$diaSemana}";
            return ['success' => true, 'message' => "Horario creado: {$dia} de {$horaInicio} a {$horaFin}."];
        } catch (\Exception $e) {
            Log::error('Crear horario error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al crear el horario.'];
        }
    }

    private function toolEliminarHorario(int $horarioId, User $user): array
    {
        $horario = MedicoHorario::find($horarioId);
        if (!$horario) return ['success' => false, 'error' => "Horario #{$horarioId} no encontrado."];
        if ($horario->medico_id !== $user->id) return ['success' => false, 'error' => 'Este horario no te pertenece.'];

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $dia = $dias[$horario->dia_semana] ?? "Día {$horario->dia_semana}";
        $info = "{$dia} de {$horario->hora_inicio} a {$horario->hora_fin}";

        try {
            $horario->delete();
            return ['success' => true, 'message' => "Horario eliminado: {$info}."];
        } catch (\Exception $e) {
            Log::error('Eliminar horario error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al eliminar el horario.'];
        }
    }

    private function toolListarBloqueos(User $user): array
    {
        $bloqueos = MedicoBloqueo::where('medico_id', $user->id)->orderBy('fecha_inicio')->get();
        if ($bloqueos->isEmpty()) return ['success' => true, 'message' => 'No tienes bloqueos registrados.', 'data' => []];

        $result = [];
        foreach ($bloqueos as $b) {
            $result[] = [
                'id' => $b->id,
                'fecha_inicio' => $b->fecha_inicio->format('Y-m-d'),
                'fecha_fin' => $b->fecha_fin->format('Y-m-d'),
                'motivo' => $b->motivo ?? 'Sin motivo',
            ];
        }
        return ['success' => true, 'message' => count($result) . ' bloqueo(s) encontrado(s).', 'data' => $result];
    }

    private function toolCrearBloqueo(string $fechaInicio, string $fechaFin, string $motivo, User $user): array
    {
        try {
            $inicio = \Carbon\Carbon::parse($fechaInicio);
            $fin = \Carbon\Carbon::parse($fechaFin);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Formato de fecha inválido. Usa Y-m-d (ej. 2026-07-20).'];
        }

        if ($fin->lessThan($inicio)) return ['success' => false, 'error' => 'fecha_fin debe ser igual o posterior a fecha_inicio.'];
        if ($inicio->lessThan(now()->startOfDay())) return ['success' => false, 'error' => 'El bloqueo debe comenzar en una fecha actual o futura.'];

        try {
            MedicoBloqueo::create([
                'medico_id'    => $user->id,
                'fecha_inicio' => $inicio->format('Y-m-d') . ' 00:00:00',
                'fecha_fin'    => $fin->format('Y-m-d') . ' 23:59:59',
                'motivo'       => $motivo ?: null,
            ]);

            return [
                'success' => true,
                'message' => "Bloqueo creado del {$inicio->format('d/m/Y')} al {$fin->format('d/m/Y')}" . ($motivo ? ": {$motivo}" : '') . '.',
            ];
        } catch (\Exception $e) {
            Log::error('Crear bloqueo error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al crear el bloqueo.'];
        }
    }

    private function toolEliminarBloqueo(int $bloqueoId, User $user): array
    {
        $bloqueo = MedicoBloqueo::find($bloqueoId);
        if (!$bloqueo) return ['success' => false, 'error' => "Bloqueo #{$bloqueoId} no encontrado."];
        if ($bloqueo->medico_id !== $user->id) return ['success' => false, 'error' => 'Este bloqueo no te pertenece.'];

        $info = "{$bloqueo->fecha_inicio->format('d/m/Y')} - {$bloqueo->fecha_fin->format('d/m/Y')}";

        try {
            $bloqueo->delete();
            return ['success' => true, 'message' => "Bloqueo eliminado: {$info}."];
        } catch (\Exception $e) {
            Log::error('Eliminar bloqueo error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al eliminar el bloqueo.'];
        }
    }

    private function toolActualizarPerfil(array $args, User $user): array
    {
        $allowed = ['name', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'tipo_medico_id', 'cedula_profesional', 'universidad', 'experiencia_anios', 'descripcion'];
        $updateUser = [];
        $updatePerfil = [];

        if (isset($args['name'])) $updateUser['name'] = $args['name'];
        if (isset($args['email'])) $updateUser['email'] = $args['email'];
        if (isset($args['telefono'])) $updateUser['telefono'] = $args['telefono'];
        if (isset($args['direccion'])) $updateUser['direccion'] = $args['direccion'];
        if (isset($args['fecha_nacimiento'])) $updateUser['fecha_nacimiento'] = $args['fecha_nacimiento'];

        if (isset($args['tipo_medico_id'])) $updatePerfil['tipo_medico_id'] = (int)$args['tipo_medico_id'];
        if (isset($args['cedula_profesional'])) $updatePerfil['cedula_profesional'] = $args['cedula_profesional'];
        if (isset($args['universidad'])) $updatePerfil['universidad'] = $args['universidad'];
        if (isset($args['experiencia_anios'])) $updatePerfil['experiencia_anios'] = (int)$args['experiencia_anios'];
        if (isset($args['descripcion'])) $updatePerfil['descripcion'] = $args['descripcion'];

        if (empty($updateUser) && empty($updatePerfil)) {
            return ['success' => false, 'error' => 'No se proporcionaron campos para actualizar.'];
        }

        try {
            DB::beginTransaction();

            if (!empty($updateUser)) {
                $user->update($updateUser);
            }

            $perfil = $user->medicoPerfil;
            if ($perfil && !empty($updatePerfil)) {
                $perfil->update($updatePerfil);
            }

            DB::commit();

            $campos = array_merge(array_keys($updateUser), array_keys($updatePerfil));
            return ['success' => true, 'message' => 'Perfil actualizado: ' . implode(', ', $campos) . '.'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actualizar perfil tool error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al actualizar el perfil.'];
        }
    }

    private function toolActualizarIntervalo(int $intervaloMinutos, User $user): array
    {
        $permitidos = [15, 20, 30, 45, 60, 90, 120];
        if (!in_array($intervaloMinutos, $permitidos)) {
            return ['success' => false, 'error' => 'Intervalo inválido. Usa: 15, 20, 30, 45, 60, 90 o 120 minutos.'];
        }

        try {
            $perfil = $user->medicoPerfil;
            if (!$perfil) return ['success' => false, 'error' => 'No tienes un perfil de médico configurado.'];

            $perfil->update(['intervalo_minutos' => $intervaloMinutos]);
            return ['success' => true, 'message' => "Intervalo entre citas actualizado a {$intervaloMinutos} minutos."];
        } catch (\Exception $e) {
            Log::error('Actualizar intervalo tool error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al actualizar el intervalo.'];
        }
    }

    private function toolListarDocumentos(User $user): array
    {
        $perfil = $user->medicoPerfil;
        if (!$perfil || $perfil->documentos->isEmpty()) {
            return ['success' => true, 'message' => 'No tienes documentos subidos.', 'data' => []];
        }

        $result = [];
        foreach ($perfil->documentos as $doc) {
            $result[] = [
                'id' => $doc->id,
                'nombre' => $doc->nombre ?? $doc->nombre_original,
                'archivo' => $doc->nombre_original,
                'tipo' => $doc->tipo_mime,
                'tamano' => $doc->tamano,
                'subido' => $doc->created_at->format('d/m/Y'),
            ];
        }
        return ['success' => true, 'message' => count($result) . ' documento(s) encontrado(s).', 'data' => $result];
    }

    private function toolEliminarDocumento(int $documentoId, User $user): array
    {
        $doc = MedicoDocumento::find($documentoId);
        if (!$doc) return ['success' => false, 'error' => "Documento #{$documentoId} no encontrado."];
        if ($doc->medicoPerfil->user_id !== $user->id) return ['success' => false, 'error' => 'Este documento no te pertenece.'];

        $nombre = $doc->nombre ?? $doc->nombre_original;

        try {
            Storage::disk('public')->delete($doc->ruta_archivo);
            $doc->delete();
            return ['success' => true, 'message' => "Documento '{$nombre}' eliminado."];
        } catch (\Exception $e) {
            Log::error('Eliminar documento tool error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al eliminar el documento.'];
        }
    }

    private function toolVerMiPerfil(User $user): array
    {
        $perfil = $user->medicoPerfil;

        $data = [
            'nombre' => $user->name,
            'email' => $user->email,
            'telefono' => $user->telefono ?? 'No registrado',
            'direccion' => $user->direccion ?? 'No registrada',
            'fecha_nacimiento' => $user->fecha_nacimiento ? $user->fecha_nacimiento->format('d/m/Y') : 'No registrada',
        ];

        if ($perfil) {
            $data['especialidad'] = optional($perfil->tipoMedico)->nombre_tipo_medico ?? 'No asignada';
            $data['cedula_profesional'] = $perfil->cedula_profesional ?? 'No registrada';
            $data['universidad'] = $perfil->universidad ?? 'No registrada';
            $data['experiencia_anios'] = $perfil->experiencia_anios ?? 'No registrados';
            $data['descripcion'] = $perfil->descripcion ?? 'No registrada';
            $data['intervalo_minutos'] = $perfil->intervalo_minutos ?? 'No configurado';
            $data['activo'] = $perfil->activo ? 'Sí' : 'No';
            $data['documentos'] = $perfil->documentos->count();
        }

        return ['success' => true, 'message' => 'Datos del perfil:', 'data' => $data];
    }

    private function toolVerPerfilPaciente(int $pacienteId, User $user): array
    {
        $paciente = User::where('role', 'paciente')
            ->with(['contactosEmergencia', 'alergias', 'enfermedadesImportantes'])
            ->find($pacienteId);

        if (!$paciente) return ['success' => false, 'error' => "Paciente #{$pacienteId} no encontrado."];

        $data = [
            'nombre' => $paciente->name,
            'email' => $paciente->email,
            'telefono' => $paciente->telefono ?? 'No registrado',
            'direccion' => $paciente->direccion ?? 'No registrada',
            'fecha_nacimiento' => $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : 'No registrada',
        ];

        if ($paciente->alergias->count()) {
            $data['alergias'] = $paciente->alergias->pluck('nombre')->toArray();
        }

        if ($paciente->enfermedadesImportantes->count()) {
            $data['enfermedades'] = $paciente->enfermedadesImportantes->pluck('nombre')->toArray();
        }

        if ($paciente->contactosEmergencia->count()) {
            $data['contactos_emergencia'] = $paciente->contactosEmergencia->map(fn($c) => [
                'nombre' => $c->nombre_completo,
                'telefono' => $c->telefono,
                'parentesco' => $c->parentesco,
            ])->toArray();
        }

        return ['success' => true, 'message' => "Datos de {$paciente->name}:", 'data' => $data];
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
