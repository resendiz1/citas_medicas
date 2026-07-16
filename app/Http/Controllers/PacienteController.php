<?php

namespace App\Http\Controllers;

use App\Events\CitaCreada;
use App\Events\MensajeEnviado;
use App\Models\Alergia;
use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\ContactoEmergencia;
use App\Models\EnfermedadImportante;
use App\Models\IaChatMensaje;
use App\Models\MedicoBloqueo;
use App\Models\MedicoHorario;
use App\Models\Mensaje;
use App\Models\User;
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

    public function chatIAIndex()
    {
        return view('paciente.chat-ia');
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

        $context = "Eres un asistente virtual de salud que ayuda a pacientes a entender sus citas, medicamentos, tratamientos y más. ";
        $context .= "Responde SOLO con información basada en los datos clínicos del paciente proporcionados abajo. ";
        $context .= "Si no sabes la respuesta, di que no tienes esa información. NO inventes datos médicos.\n\n";
        $context .= "Tienes estas herramientas disponibles:\n";
        $context .= "- crear_cita: Agenda una nueva cita médica con un doctor\n";
        $context .= "- cancelar_cita: Cancela una cita pendiente del paciente\n";
        $context .= "- listar_medicos: Lista los médicos registrados, con filtro opcional por nombre\n";
        $context .= "- ver_perfil_medico: Ver el perfil completo de un médico (especialidad, cédula, etc.)\n";
        $context .= "- ver_horarios_medico: Ver los horarios disponibles de un médico\n";
        $context .= "- actualizar_perfil: Actualiza tus datos personales (nombre, email, teléfono, etc.)\n";
        $context .= "- listar_consultas: Lista todas tus consultas médicas\n";
        $context .= "- ver_detalle_consulta: Ver el detalle completo de una consulta (diagnósticos, medicamentos, signos vitales)\n";
        $context .= "Para acciones destructivas (cancelar_cita), SIEMPRE pregunta primero al usuario si está seguro antes de ejecutar la herramienta.\n";
        $context .= "CRÍTICO: Cuando necesites ejecutar una acción, DEBES usar la herramienta(function calling) del sistema. NUNCA escribas etiquetas como <tool_call>, <tool_calls>, <arg_key>, ni nada similar en tu respuesta de texto. ";
        $context .= "Si necesitas llamar una herramienta, hazlo ÚNICAMENTE a través del mecanismo de function call del API, nunca como texto. ";
        $context .= "NUNCA muestres código, etiquetas HTML, XML, markdown de código, ni nada entre <> en tus respuestas. Responde solo en lenguaje natural.\n\n";

        $edad = $user->fecha_nacimiento ? now()->diffInYears($user->fecha_nacimiento) : 'No registrada';
        $context .= "Paciente: {$user->name}\nEdad: {$edad}\n\n";

        $citasConDatos = $user->citasComoPaciente->filter(fn($c) => $c->consultaMedica || $c->recetas->count());

        if ($citasConDatos->isEmpty()) {
            $context .= "El paciente no tiene consultas ni recetas registradas en el sistema.\n";
        } else {
            foreach ($citasConDatos as $cita) {
                $context .= "--- Cita #{$cita->id} del {$cita->fecha_hora->format('d/m/Y')} con Dr. {$cita->medico->name} ---\n";
                $context .= "Estado: {$cita->estado}\n";

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

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.api_key'),
                'Content-Type'  => 'application/json',
            ])->timeout(120)->post(config('services.openrouter.url') . '/chat/completions', [
                'model'       => config('services.openrouter.model'),
                'messages'    => $messages,
                'tools'       => $this->getPatientToolsArray(),
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

                    $result = $this->executePatientChatTool($toolCall['function']['name'], $arguments, $user);

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
                    Log::warning('OpenRouter unexpected follow-up response', ['body' => $responseNext->body()]);
                    return response()->json(['error' => 'Respuesta inesperada del asistente.'], 500);
                }
            }

            $reply = $choice['content'] ?? null;
            if ($reply === null) {
                Log::warning('OpenRouter unexpected response (no content)', ['body' => $response->body()]);
                return response()->json(['error' => 'Respuesta inesperada del asistente. Intenta de nuevo.'], 500);
            }

            $reply = preg_replace('/<[^>]+>/', '', $reply ?? '');
            $reply = trim($reply);

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

    private function getPatientToolsArray(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'crear_cita',
                    'description' => 'Agenda una nueva cita médica para el paciente con el médico, fecha y motivo especificados. Valida disponibilidad automáticamente.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'medico_id' => ['type' => 'integer', 'description' => 'ID del médico con quien agendar la cita'],
                            'fecha_hora' => ['type' => 'string', 'description' => 'Fecha y hora de la cita en formato YYYY-MM-DD HH:MM (ej. 2026-07-20 10:00)'],
                            'motivo' => ['type' => 'string', 'description' => 'Motivo o razón de la cita'],
                        ],
                        'required' => ['medico_id', 'fecha_hora', 'motivo'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cancelar_cita',
                    'description' => 'Cancela una cita propia del paciente que esté en estado pendiente. Pregunta confirmación antes.',
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
                    'name' => 'listar_medicos',
                    'description' => 'Lista los médicos registrados en el sistema. Opcionalmente filtra por nombre.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'search' => ['type' => 'string', 'description' => 'Texto para buscar por nombre del médico (opcional)'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'ver_perfil_medico',
                    'description' => 'Obtiene el perfil completo de un médico: datos personales, especialidad, cédula, universidad, experiencia, descripción.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'medico_id' => ['type' => 'integer', 'description' => 'ID del médico'],
                        ],
                        'required' => ['medico_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'ver_horarios_medico',
                    'description' => 'Obtiene los horarios disponibles de un médico (días y horas de atención).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'medico_id' => ['type' => 'integer', 'description' => 'ID del médico'],
                        ],
                        'required' => ['medico_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'actualizar_perfil',
                    'description' => 'Actualiza los datos personales del paciente (nombre, email, teléfono, dirección, fecha de nacimiento, observaciones).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Nombre completo'],
                            'email' => ['type' => 'string', 'description' => 'Correo electrónico'],
                            'telefono' => ['type' => 'string', 'description' => 'Teléfono'],
                            'direccion' => ['type' => 'string', 'description' => 'Dirección'],
                            'fecha_nacimiento' => ['type' => 'string', 'description' => 'Fecha de nacimiento (YYYY-MM-DD)'],
                            'observaciones' => ['type' => 'string', 'description' => 'Observaciones adicionales'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'listar_consultas',
                    'description' => 'Lista todas las consultas médicas del paciente con su información básica (fecha, doctor, motivo, estado).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'ver_detalle_consulta',
                    'description' => 'Obtiene el detalle completo de una consulta médica: motivo, síntomas, signos vitales, diagnósticos, medicamentos, dolores, exploración, recomendaciones.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita/consulta'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
        ];
    }

    private function executePatientChatTool(string $name, array $arguments, $user): array
    {
        return match ($name) {
            'crear_cita' => $this->pacienteToolCrearCita(
                (int)($arguments['medico_id'] ?? 0),
                $arguments['fecha_hora'] ?? '',
                $arguments['motivo'] ?? '',
                $user
            ),
            'cancelar_cita' => $this->pacienteToolCancelarCita(
                (int)($arguments['cita_id'] ?? 0),
                $user,
                $arguments['comentario'] ?? ''
            ),
            'listar_medicos' => $this->pacienteToolListarMedicos(
                $arguments['search'] ?? ''
            ),
            'ver_perfil_medico' => $this->pacienteToolVerPerfilMedico(
                (int)($arguments['medico_id'] ?? 0)
            ),
            'ver_horarios_medico' => $this->pacienteToolVerHorariosMedico(
                (int)($arguments['medico_id'] ?? 0)
            ),
            'actualizar_perfil' => $this->pacienteToolActualizarPerfil(
                $arguments,
                $user
            ),
            'listar_consultas' => $this->pacienteToolListarConsultas(
                $user
            ),
            'ver_detalle_consulta' => $this->pacienteToolVerDetalleConsulta(
                (int)($arguments['cita_id'] ?? 0),
                $user
            ),
            default => ['success' => false, 'error' => "Tool '$name' no encontrada."],
        };
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

    private function pacienteToolCrearCita(int $medicoId, string $fechaHora, string $motivo, $user): array
    {
        try {
            $fecha = \Carbon\Carbon::parse($fechaHora);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Formato de fecha inválido. Usa YYYY-MM-DD HH:MM.'];
        }

        if ($fecha->lessThan(now()->subMinutes(2))) {
            return ['success' => false, 'error' => 'La fecha y hora deben ser actuales o futuras.'];
        }

        $medico = User::where('role', 'medico')->with('medicoPerfil')->find($medicoId);
        if (!$medico || !$medico->medicoPerfil || !$medico->medicoPerfil->activo || !$medico->medicoPerfil->aprobado) {
            return ['success' => false, 'error' => 'El médico seleccionado no está disponible.'];
        }

        $diaSemana = $fecha->dayOfWeek;
        $hora = $fecha->format('H:i');

        $horarios = MedicoHorario::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horarios->isEmpty()) {
            return ['success' => false, 'error' => 'El médico no trabaja en la fecha seleccionada.'];
        }

        $enHorario = false;
        foreach ($horarios as $horario) {
            if ($hora >= substr($horario->hora_inicio, 0, 5) && $hora <= substr($horario->hora_fin, 0, 5)) {
                $enHorario = true;
                break;
            }
        }

        if (!$enHorario) {
            return ['success' => false, 'error' => 'La hora seleccionada está fuera del horario del médico.'];
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
            return ['success' => false, 'error' => "La hora seleccionada no respeta el intervalo de {$intervalo} minutos del médico."];
        }

        $bloqueado = MedicoBloqueo::where('medico_id', $medicoId)
            ->where('fecha_inicio', '<=', $fechaHora)
            ->where('fecha_fin', '>=', $fechaHora)
            ->exists();

        if ($bloqueado) {
            return ['success' => false, 'error' => 'El médico tiene un bloqueo en la fecha seleccionada.'];
        }

        $conflicto = CitaMedica::where('medico_id', $medicoId)
            ->where('fecha_hora', $fechaHora)
            ->whereIn('estado', ['pendiente', 'confirmada', 'en_espera', 'en_consulta'])
            ->exists();

        if ($conflicto) {
            return ['success' => false, 'error' => 'El médico ya tiene una cita en ese horario.'];
        }

        try {
            DB::beginTransaction();

            $cita = CitaMedica::create([
                'medico_id'   => $medicoId,
                'paciente_id' => $user->id,
                'fecha_hora'  => $fechaHora,
                'motivo'      => $motivo,
                'estado'      => 'pendiente',
            ]);

            CitaHistorial::create([
                'cita_id'         => $cita->id,
                'user_id'         => $user->id,
                'estado_anterior' => null,
                'estado_nuevo'    => 'pendiente',
                'comentario'      => 'Cita creada a través del asistente IA.',
            ]);

            DB::commit();

            try {
                broadcast(new CitaCreada($medicoId, [
                    'cita_id'  => $cita->id,
                    'paciente' => $user->name,
                    'fecha'    => $cita->fecha_hora->format('d/m/Y H:i'),
                    'motivo'   => $cita->motivo,
                ]))->toOthers();

                broadcast(new CitaCreada($user->id, [
                    'cita_id' => $cita->id,
                    'medico'  => $medico->name,
                    'fecha'   => $cita->fecha_hora->format('d/m/Y H:i'),
                    'motivo'  => $cita->motivo,
                ]))->toOthers();

                $mensaje = Mensaje::create([
                    'cita_id' => $cita->id,
                    'user_id' => $user->id,
                    'mensaje' => '🟢 Se ha agendado una cita para el ' . $cita->fecha_hora->format('d/m/Y H:i') . '. Motivo: ' . $cita->motivo,
                ]);

                broadcast(new MensajeEnviado(
                    [
                        'id'         => $mensaje->id,
                        'user_id'    => $mensaje->user_id,
                        'nombre'     => $user->name,
                        'mensaje'    => $mensaje->mensaje,
                        'created_at' => $mensaje->created_at->format('d/m/Y H:i'),
                    ],
                    $cita->id
                ))->toOthers();

                $medico->notify(new CitaEstadoNotificacion($cita, 'creada'));
                $user->notify(new CitaEstadoNotificacion($cita, 'creada'));
            } catch (\Throwable $e) {
                Log::error('Patient tool crear_cita post-creation error', ['error' => $e->getMessage()]);
            }

            return [
                'success' => true,
                'message' => "Cita #{$cita->id} agendada correctamente con Dr. {$medico->name} para el {$cita->fecha_hora->format('d/m/Y H:i')}.",
                'cita_id' => $cita->id,
                'medico'  => $medico->name,
                'fecha'   => $cita->fecha_hora->format('d/m/Y H:i'),
                'estado'  => 'pendiente',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Patient tool crear_cita error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al crear la cita.'];
        }
    }

    private function pacienteToolListarMedicos(string $search = ''): array
    {
        $query = User::where('role', 'medico')
            ->whereHas('medicoPerfil', fn($q) => $q->where('activo', true));

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $medicos = $query->with('medicoPerfil.tipoMedico')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'telefono']);

        if ($medicos->isEmpty()) {
            return ['success' => true, 'medicos' => [], 'message' => 'No se encontraron médicos.'];
        }

        $result = $medicos->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->name,
            'email' => $m->email,
            'telefono' => $m->telefono,
            'especialidad' => optional($m->medicoPerfil->tipoMedico)->nombre_tipo_medico ?? 'No asignada',
            'descripcion' => $m->medicoPerfil->descripcion ?? '',
            'experiencia_anios' => $m->medicoPerfil->experiencia_anios,
        ]);

        return ['success' => true, 'medicos' => $result, 'total' => $result->count()];
    }

    private function pacienteToolVerPerfilMedico(int $medicoId): array
    {
        $medico = User::where('role', 'medico')
            ->with('medicoPerfil.tipoMedico', 'medicoPerfil.documentos')
            ->find($medicoId);

        if (!$medico) {
            return ['success' => false, 'error' => 'Médico no encontrado.'];
        }

        $perfil = $medico->medicoPerfil;

        return [
            'success' => true,
            'medico' => [
                'id' => $medico->id,
                'name' => $medico->name,
                'email' => $medico->email,
                'telefono' => $medico->telefono,
                'direccion' => $medico->direccion,
                'especialidad' => optional($perfil->tipoMedico)->nombre_tipo_medico ?? 'No asignada',
                'cedula_profesional' => $perfil->cedula_profesional ?? 'No registrada',
                'universidad' => $perfil->universidad ?? 'No registrada',
                'experiencia_anios' => $perfil->experiencia_anios,
                'descripcion' => $perfil->descripcion ?? 'Sin descripción',
                'documentos' => $perfil->documentos->map(fn($d) => [
                    'nombre' => $d->nombre,
                    'tipo' => $d->tipo,
                ]),
            ],
        ];
    }

    private function pacienteToolVerHorariosMedico(int $medicoId): array
    {
        $medico = User::where('role', 'medico')->find($medicoId);
        if (!$medico) {
            return ['success' => false, 'error' => 'Médico no encontrado.'];
        }

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

        $horarios = MedicoHorario::where('medico_id', $medicoId)
            ->where('activo', true)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn($h) => [
                'dia' => $dias[$h->dia_semana] ?? "Día {$h->dia_semana}",
                'dia_semana' => $h->dia_semana,
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
            ]);

        if ($horarios->isEmpty()) {
            return ['success' => true, 'horarios' => [], 'message' => 'El médico no tiene horarios disponibles registrados.'];
        }

        $intervalo = optional($medico->medicoPerfil)->intervalo_minutos;

        return [
            'success' => true,
            'medico' => $medico->name,
            'intervalo_minutos' => $intervalo,
            'horarios' => $horarios,
        ];
    }

    private function pacienteToolActualizarPerfil(array $data, $user): array
    {
        $allowed = array_intersect_key($data, array_flip(['name', 'email', 'telefono', 'direccion', 'fecha_nacimiento', 'observaciones']));

        if (empty($allowed)) {
            return ['success' => false, 'error' => 'No se proporcionaron campos válidos para actualizar.'];
        }

        if (isset($allowed['email']) && $allowed['email'] !== $user->email) {
            $exists = User::where('email', $allowed['email'])->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return ['success' => false, 'error' => 'El email ya está en uso por otro usuario.'];
            }
        }

        try {
            $user->update($allowed);
            return [
                'success' => true,
                'message' => 'Perfil actualizado correctamente.',
                'campos_actualizados' => array_keys($allowed),
            ];
        } catch (\Exception $e) {
            Log::error('Patient profile update error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al actualizar el perfil.'];
        }
    }

    private function pacienteToolListarConsultas($user): array
    {
        $citas = $user->citasComoPaciente()
            ->with('medico', 'consultaMedica')
            ->whereHas('consultaMedica')
            ->orderBy('fecha_hora', 'desc')
            ->get(['id', 'medico_id', 'fecha_hora', 'estado', 'motivo']);

        if ($citas->isEmpty()) {
            return ['success' => true, 'consultas' => [], 'message' => 'No tienes consultas médicas registradas.'];
        }

        $result = $citas->map(fn($c) => [
            'cita_id' => $c->id,
            'fecha' => $c->fecha_hora->format('d/m/Y H:i'),
            'medico' => $c->medico->name,
            'motivo' => $c->motivo ?? $c->consultaMedica->motivo_consulta ?? 'No especificado',
            'estado' => $c->estado,
        ]);

        return ['success' => true, 'consultas' => $result, 'total' => $result->count()];
    }

    private function pacienteToolVerDetalleConsulta(int $citaId, $user): array
    {
        $cita = CitaMedica::where('id', $citaId)
            ->where('paciente_id', $user->id)
            ->with([
                'medico.medicoPerfil.tipoMedico',
                'consultaMedica.diagnosticos',
                'consultaMedica.dolores',
                'consultaMedica.sintomasRegistrados',
                'consultaMedica.medicamentos',
                'recetas.medicamentos',
            ])
            ->first();

        if (!$cita) {
            return ['success' => false, 'error' => 'Consulta no encontrada o no te pertenece.'];
        }

        $consulta = $cita->consultaMedica;

        if (!$consulta) {
            return ['success' => false, 'error' => 'Esta cita no tiene consulta médica registrada.'];
        }

        $vitals = [];
        if ($consulta->presion_arterial) $vitals['presion_arterial'] = $consulta->presion_arterial;
        if ($consulta->temperatura) $vitals['temperatura'] = $consulta->temperatura . '°C';
        if ($consulta->frecuencia_cardiaca) $vitals['frecuencia_cardiaca'] = $consulta->frecuencia_cardiaca . ' lpm';
        if ($consulta->frecuencia_respiratoria) $vitals['frecuencia_respiratoria'] = $consulta->frecuencia_respiratoria . ' rpm';
        if ($consulta->saturacion_oxigeno) $vitals['saturacion_oxigeno'] = $consulta->saturacion_oxigeno . '%';
        if ($consulta->peso) $vitals['peso'] = $consulta->peso . ' kg';
        if ($consulta->estatura) $vitals['estatura'] = $consulta->estatura . ' m';
        if ($consulta->imc) $vitals['imc'] = $consulta->imc;

        return [
            'success' => true,
            'consulta' => [
                'cita_id' => $cita->id,
                'fecha' => $cita->fecha_hora->format('d/m/Y H:i'),
                'medico' => $cita->medico->name,
                'especialidad' => optional($cita->medico->medicoPerfil->tipoMedico)->nombre_tipo_medico ?? 'No asignada',
                'estado' => $cita->estado,
                'motivo_consulta' => $consulta->motivo_consulta,
                'sintomas' => $consulta->sintomas,
                'tiempo_evolucion' => $consulta->tiempo_evolucion,
                'forma_inicio' => $consulta->forma_inicio,
                'diagnosticos' => $consulta->diagnosticos->map(fn($dx) => [
                    'descripcion' => $dx->descripcion,
                    'codigo_cie10' => $dx->codigo_cie10,
                    'tipo' => $dx->tipo,
                    'es_principal' => $dx->es_principal,
                ]),
                'medicamentos' => $consulta->medicamentos->map(fn($m) => [
                    'nombre_generico' => $m->nombre_generico,
                    'nombre_comercial' => $m->nombre_comercial,
                    'dosis' => $m->dosis,
                    'frecuencia' => $m->frecuencia,
                    'duracion' => $m->duracion,
                    'indicaciones' => $m->indicaciones,
                ]),
                'dolores' => $consulta->dolores->map(fn($d) => [
                    'ubicacion' => $d->ubicacion,
                    'intensidad' => $d->intensidad,
                    'duracion' => $d->duracion,
                ]),
                'signos_vitales' => $vitals,
                'exploracion_fisica' => $consulta->exploracion_fisica,
                'exploracion_hallazgos' => $consulta->exploracion_hallazgos,
                'diagnostico_probable' => $consulta->diagnostico_probable,
                'diagnostico_final' => $consulta->diagnostico_final,
                'pronostico' => $consulta->pronostico,
                'resumen_clinico' => $consulta->resumen_clinico,
                'plan_recomendaciones' => $consulta->plan_recomendaciones,
                'plan_signos_alarma' => $consulta->plan_signos_alarma,
                'plan_medicamentos' => $consulta->plan_medicamentos,
                'plan_estudios' => $consulta->plan_estudios,
                'plan_procedimientos' => $consulta->plan_procedimientos,
                'plan_referencia' => $consulta->plan_referencia,
                'plan_seguimiento_fecha' => $consulta->plan_seguimiento_fecha?->format('d/m/Y'),
                'plan_incapacidad' => $consulta->plan_incapacidad,
                'recetas' => $cita->recetas->map(fn($r) => [
                    'fecha_emision' => $r->fecha_emision?->format('d/m/Y'),
                    'diagnostico' => $r->diagnostico,
                    'indicaciones_generales' => $r->indicaciones_generales,
                    'medicamentos' => $r->medicamentos->map(fn($m) => [
                        'medicamento' => $m->medicamento,
                        'dosis' => $m->dosis,
                        'frecuencia' => $m->frecuencia,
                        'duracion' => $m->duracion,
                        'via_administracion' => $m->via_administracion,
                        'indicaciones' => $m->indicaciones,
                    ]),
                ]),
            ],
        ];
    }
}
