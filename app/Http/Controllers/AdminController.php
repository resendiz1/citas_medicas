<?php

namespace App\Http\Controllers;

use App\Models\CitaHistorial;
use App\Models\CitaMedica;
use App\Models\ConsultaMedica;
use App\Models\IaChatMensaje;
use App\Models\MedicoPerfil;
use App\Models\Mensaje;
use App\Models\RecetaDocumento;
use App\Models\TipoMedico;
use App\Models\User;
use App\Events\CitaEstadoActualizado;
use App\Events\MensajeEnviado;
use App\Events\MedicoAprobado;
use App\Notifications\CitaEstadoNotificacion;
use App\Notifications\MedicoRegistroNotificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ========== MÉDICOS ==========

    public function medicos(Request $request)
    {
        $query = User::where('role', 'medico')->with('medicoPerfil.tipoMedico');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $medicos = $query->orderBy('name')->paginate(15);
        return view('admin.medicos.index', compact('medicos'));
    }

    public function medicosShow($id)
    {
        $user = User::where('role', 'medico')->with('medicoPerfil.tipoMedico', 'medicoPerfil.documentos', 'horarios', 'bloqueos')->findOrFail($id);
        $perfil = $user->medicoPerfil;
        $documentos = optional($perfil)->documentos ?? collect();

        return view('admin.medicos.show', compact('user', 'perfil', 'documentos'));
    }

    public function medicoChatIAHistorial(Request $request, $medicoId)
    {
        $medico = User::where('role', 'medico')->findOrFail($medicoId);

        $mensajes = IaChatMensaje::forAdminMedico($request->user()->id, $medico->id)
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

    public function medicoChatIA(Request $request, $medicoId)
    {
        $request->validate(['message' => 'required|string']);

        $medico = User::where('role', 'medico')->with('medicoPerfil.tipoMedico')->findOrFail($medicoId);

        $admin = $request->user();

        $userMessage = $request->input('message');

        IaChatMensaje::create([
            'user_id'   => $admin->id,
            'medico_id' => $medico->id,
            'role'      => 'user',
            'content'   => $userMessage,
        ]);

        $context = "Eres un asistente virtual de salud para administradores. ";
        $context .= "Responde preguntas sobre los pacientes, citas y actividad del médico indicado. ";
        $context .= "Usa SOLO la información proporcionada abajo. No inventes datos.\n\n";
        $context .= "IMPORTANTE: Puedes MODIFICAR el estado de las citas usando las herramientas disponibles. ";
        $context .= "Para acciones destructivas (cancelar, no_asistio), SIEMPRE pregunta primero al usuario si está seguro antes de ejecutar la herramienta. ";
        $context .= "Para las demás acciones, puedes ejecutarlas directamente.\n\n";

        $especialidad = optional($medico->medicoPerfil)->tipoMedico->nombre_tipo_medico ?? 'No asignada';
        $cedula = optional($medico->medicoPerfil)->cedula_profesional ?? 'No registrada';
        $telefono = $medico->telefono ?? 'No registrado';

        $context .= "Médico: {$medico->name}\n";
        $context .= "Especialidad: {$especialidad}\n";
        $context .= "Cédula: {$cedula}\n";
        $context .= "Email: {$medico->email}\n";
        $context .= "Teléfono: {$telefono}\n\n";

        $citas = CitaMedica::where('medico_id', $medico->id)
            ->with(['paciente', 'consultaMedica.diagnosticos', 'consultaMedica.medicamentos', 'recetas.medicamentos'])
            ->orderBy('fecha_hora', 'desc')
            ->get();

        if ($citas->isEmpty()) {
            $context .= "El médico no tiene citas registradas.\n";
        } else {
            $context .= "Total de citas: {$citas->count()}\n\n";
            foreach ($citas as $cita) {
                $context .= "--- Cita #{$cita->id} ---\n";
                $context .= "Paciente: {$cita->paciente->name}\n";
                $context .= "Fecha: {$cita->fecha_hora->format('d/m/Y H:i')}\n";
                $context .= "Estado: {$cita->estado}\n";
                if ($cita->motivo) $context .= "Motivo: {$cita->motivo}\n";

                if ($consulta = $cita->consultaMedica) {
                    if ($consulta->motivo_consulta) $context .= "Motivo consulta: {$consulta->motivo_consulta}\n";
                    if ($consulta->diagnosticos->count()) {
                        $context .= "Diagnósticos:\n";
                        foreach ($consulta->diagnosticos as $dx) {
                            $context .= "  - {$dx->descripcion}" . ($dx->codigo_cie10 ? " (CIE-10: {$dx->codigo_cie10})" : '') . "\n";
                        }
                    }
                    if ($consulta->medicamentos->count()) {
                        $context .= "Medicamentos:\n";
                        foreach ($consulta->medicamentos as $med) {
                            $context .= "  - {$med->nombre_generico}" . ($med->dosis ? " {$med->dosis}" : '') . ($med->frecuencia ? " c/{$med->frecuencia}" : '') . "\n";
                        }
                    }
                    $vitals = [];
                    if ($consulta->presion_arterial) $vitals[] = "PA: {$consulta->presion_arterial}";
                    if ($consulta->temperatura) $vitals[] = "Temp: {$consulta->temperatura}°C";
                    if ($consulta->frecuencia_cardiaca) $vitals[] = "FC: {$consulta->frecuencia_cardiaca} lpm";
                    if ($consulta->peso) $vitals[] = "Peso: {$consulta->peso} kg";
                    if ($vitals) $context .= "Signos vitales: " . implode(', ', $vitals) . "\n";
                }

                if ($cita->recetas->count()) {
                    foreach ($cita->recetas as $receta) {
                        $context .= "Receta {$receta->id}:\n";
                        if ($receta->diagnostico) $context .= "  Diagnóstico: {$receta->diagnostico}\n";
                        foreach ($receta->medicamentos as $med) {
                            $context .= "  - {$med->medicamento}" . ($med->dosis ? " {$med->dosis}" : '') . ($med->frecuencia ? " c/{$med->frecuencia}" : '') . "\n";
                        }
                    }
                }
                $context .= "\n";
            }
        }

        $context .= "\nIMPORTANTE: Esto es solo informativo administrativo.";

        $messages = [['role' => 'system', 'content' => $context]];

        $history = IaChatMensaje::forAdminMedico($admin->id, $medico->id)
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
                'tools'       => $this->getAdminToolsArray(),
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

                    $result = $this->executeAdminTool($toolCall['function']['name'], $arguments, $admin, $medico);

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
                'user_id'   => $admin->id,
                'medico_id' => $medico->id,
                'role'      => 'assistant',
                'content'   => $reply,
            ]);

            return response()->json(['reply' => $reply]);
        } catch (\Exception $e) {
            Log::error('OpenRouter exception: ' . $e->getMessage());
            return response()->json(['error' => 'Error de conexión con el asistente.'], 500);
        }
    }

    public function medicosAprobar($id)
    {
        $medico = User::where('role', 'medico')->findOrFail($id);

        if (!$medico->medicoPerfil) {
            $tipoId = TipoMedico::first()?->id ?? 1;
            MedicoPerfil::create([
                'user_id'        => $medico->id,
                'tipo_medico_id' => $tipoId,
                'activo'         => true,
                'aprobado'       => true,
            ]);
        } else {
            $medico->medicoPerfil->update(['aprobado' => true]);
        }

        try {
            $medico->notify(new MedicoRegistroNotificacion($medico, 'aprobado'));
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            broadcast(new MedicoAprobado($medico->id));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->back()->with('success', 'Médico aprobado correctamente. Se le ha notificado por correo.');
    }

    public function medicosCreate()
    {
        $tiposMedico = TipoMedico::all();
        return view('admin.medicos.create', compact('tiposMedico'));
    }

    public function medicosStore(Request $request)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email',
            'password'           => 'required|string|min:8',
            'tipo_medico_id'     => 'required|exists:tipo_medicos,id',
            'telefono'           => 'nullable|string|max:20',
            'cedula_profesional' => 'nullable|string|max:50',
            'universidad'        => 'nullable|string|max:255',
            'experiencia_anios'  => 'nullable|integer|min:0',
            'descripcion'        => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'medico',
            'telefono' => $data['telefono'] ?? null,
        ]);

        MedicoPerfil::create([
            'user_id'            => $user->id,
            'tipo_medico_id'     => $data['tipo_medico_id'],
            'cedula_profesional' => $data['cedula_profesional'] ?? null,
            'universidad'        => $data['universidad'] ?? null,
            'experiencia_anios'  => $data['experiencia_anios'] ?? null,
            'descripcion'        => $data['descripcion'] ?? null,
            'activo'             => $request->boolean('activo', true),
            'aprobado'           => true,
        ]);

        return redirect()->route('admin.medicos')->with('success', 'Médico creado correctamente.');
    }

    public function medicosEdit($id)
    {
        $medico = User::where('role', 'medico')->with('medicoPerfil')->findOrFail($id);
        $tiposMedico = TipoMedico::all();
        return view('admin.medicos.edit', compact('medico', 'tiposMedico'));
    }

    public function medicosUpdate(Request $request, $id)
    {
        $medico = User::where('role', 'medico')->findOrFail($id);

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => 'required|email|unique:users,email,' . $id,
            'password'           => 'nullable|string|min:8',
            'tipo_medico_id'     => 'required|exists:tipo_medicos,id',
            'telefono'           => 'nullable|string|max:20',
            'cedula_profesional' => 'nullable|string|max:50',
            'universidad'        => 'nullable|string|max:255',
            'experiencia_anios'  => 'nullable|integer|min:0',
            'descripcion'        => 'nullable|string|max:1000',
        ]);

        $userData = [
            'name'     => $data['name'],
            'email'    => $data['email'],
            'telefono' => $data['telefono'] ?? null,
        ];

        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $medico->update($userData);

        $medico->medicoPerfil()->updateOrCreate(
            ['user_id' => $medico->id],
            [
                'tipo_medico_id'     => $data['tipo_medico_id'],
                'cedula_profesional' => $data['cedula_profesional'] ?? null,
                'universidad'        => $data['universidad'] ?? null,
                'experiencia_anios'  => $data['experiencia_anios'] ?? null,
                'descripcion'        => $data['descripcion'] ?? null,
                'activo'             => $request->boolean('activo', true),
            ]
        );

        return redirect()->route('admin.medicos')->with('success', 'Médico actualizado correctamente.');
    }

    public function medicosDestroy($id)
    {
        $medico = User::where('role', 'medico')->findOrFail($id);
        $medico->delete();

        return redirect()->route('admin.medicos')->with('success', 'Médico eliminado correctamente.');
    }

    // ========== CITAS ==========

    public function citas(Request $request)
    {
        $query = CitaMedica::with('paciente', 'medico.medicoPerfil.tipoMedico');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('paciente', fn($p) => $p->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('medico', fn($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        $citas = $query->orderBy('fecha_hora')->paginate(20);
        return view('admin.citas.index', compact('citas'));
    }

    public function citasDestroy($id)
    {
        $cita = CitaMedica::with('consultaMedica.dolores', 'recetas.medicamentos', 'recetas.documentos', 'historiales')
            ->findOrFail($id);

        foreach ($cita->recetas as $receta) {
            foreach ($receta->documentos as $doc) {
                if ($doc->ruta_archivo && Storage::disk('public')->exists($doc->ruta_archivo)) {
                    Storage::disk('public')->delete($doc->ruta_archivo);
                }
                $doc->delete();
            }
            $receta->medicamentos()->delete();
            $receta->delete();
        }

        if ($cita->consultaMedica) {
            $cita->consultaMedica->sintomasRegistrados()->delete();
            $cita->consultaMedica->dolores()->delete();
            $cita->consultaMedica()->delete();
        }

        $cita->historiales()->delete();

        $cita->delete();

        return redirect()->route('admin.citas')->with('success', 'Cita eliminada correctamente.');
    }

    // ========== PACIENTES ==========

    public function pacientes(Request $request)
    {
        $query = User::where('role', 'paciente')
            ->with('contactosEmergencia', 'alergias', 'enfermedadesImportantes');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pacientes = $query->orderBy('name')->paginate(15);
        return view('admin.pacientes.index', compact('pacientes'));
    }

    public function pacientesCreate()
    {
        $contactos = \App\Models\ContactoEmergencia::all();
        $alergias = \App\Models\Alergia::all();
        $enfermedades = \App\Models\EnfermedadImportante::all();

        return view('admin.pacientes.create', compact('contactos', 'alergias', 'enfermedades'));
    }

    public function pacientesStore(Request $request)
    {
        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email',
            'password'                    => 'required|string|min:8',
            'fecha_nacimiento'             => 'nullable|date',
            'telefono'                    => 'nullable|string|max:20',
            'direccion'                   => 'nullable|string|max:500',
            'observaciones'               => 'nullable|string|max:1000',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'paciente';

        User::create($data);

        return redirect()->route('admin.pacientes')->with('success', 'Paciente creado correctamente.');
    }

    public function pacientesEdit($id)
    {
        $paciente = User::where('role', 'paciente')->findOrFail($id);
        $contactos = \App\Models\ContactoEmergencia::all();
        $alergias = \App\Models\Alergia::all();
        $enfermedades = \App\Models\EnfermedadImportante::all();

        return view('admin.pacientes.edit', compact('paciente', 'contactos', 'alergias', 'enfermedades'));
    }

    public function pacientesUpdate(Request $request, $id)
    {
        $paciente = User::where('role', 'paciente')->findOrFail($id);

        $data = $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email,' . $id,
            'password'                    => 'nullable|string|min:8',
            'fecha_nacimiento'             => 'nullable|date',
            'telefono'                    => 'nullable|string|max:20',
            'direccion'                   => 'nullable|string|max:500',
            'observaciones'               => 'nullable|string|max:1000',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $paciente->update($data);

        return redirect()->route('admin.pacientes')->with('success', 'Paciente actualizado correctamente.');
    }

    public function pacientesDestroy($id)
    {
        $paciente = User::where('role', 'paciente')->findOrFail($id);
        $paciente->delete();

        return redirect()->route('admin.pacientes')->with('success', 'Paciente eliminado correctamente.');
    }

    public function reset()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('dolores')->delete();
        DB::table('sintomas')->delete();
        DB::table('diagnosticos')->delete();
        DB::table('consulta_medicamentos')->delete();
        DB::table('receta_medicamentos')->delete();
        DB::table('receta_documentos')->delete();
        DB::table('recetas')->delete();
        DB::table('consulta_medicas')->delete();
        DB::table('cita_historiales')->delete();
        DB::table('mensajes')->delete();
        DB::table('ia_chat_mensajes')->delete();
        DB::table('citas_medicas')->delete();
        DB::table('medico_horarios')->delete();
        DB::table('medico_bloqueos')->delete();
        DB::table('medico_documentos')->delete();
        DB::table('medico_perfiles')->delete();
        DB::table('user_alergias')->delete();
        DB::table('user_enfermedades_importantes')->delete();
        DB::table('contactos_emergencia')->delete();
        DB::table('alergias')->delete();
        DB::table('enfermedades_importantes')->delete();
        DB::table('notifications')->delete();
        DB::table('push_subscriptions')->delete();
        User::where('role', '!=', 'admin')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->seedReferenceData();

        return redirect()->route('dashboard')->with('success', 'Base de datos restablecida. Solo se conservó el usuario administrador.');
    }

    private function seedReferenceData(): void
    {
        if (TipoMedico::count() === 0) {
            DB::statement('ALTER TABLE tipo_medicos AUTO_INCREMENT = 1');
            $tipos = ['Medicina General','Cardiología','Pediatría','Dermatología','Ginecología','Neurología','Traumatología','Oftalmología','Otorrinolaringología','Psiquiatría'];
            foreach ($tipos as $t) { TipoMedico::create(['nombre_tipo_medico' => $t]); }
        }
        if (\App\Models\Alergia::count() === 0) {
            $alergias = [
                ['nombre' => 'Penicilina', 'descripcion' => 'Alergia a antibióticos tipo penicilina'],
                ['nombre' => 'Polen', 'descripcion' => 'Alergia estacional al polen de plantas'],
                ['nombre' => 'Frutos secos', 'descripcion' => 'Alergia a nueces, almendras, cacahuates, etc.'],
                ['nombre' => 'Lácteos', 'descripcion' => 'Intolerancia o alergia a productos lácteos'],
                ['nombre' => 'Ácaros', 'descripcion' => 'Alergia a ácaros del polvo'],
            ];
            foreach ($alergias as $a) { \App\Models\Alergia::create($a); }
        }
        if (\App\Models\EnfermedadImportante::count() === 0) {
            $enfermedades = [
                ['nombre' => 'Diabetes tipo 2', 'descripcion' => 'Enfermedad metabólica crónica'],
                ['nombre' => 'Hipertensión arterial', 'descripcion' => 'Presión arterial elevada de forma crónica'],
                ['nombre' => 'Asma', 'descripcion' => 'Enfermedad inflamatoria crónica de las vías respiratorias'],
                ['nombre' => 'Cardiopatía isquémica', 'descripcion' => 'Enfermedad de las arterias coronarias'],
                ['nombre' => 'Hipotiroidismo', 'descripcion' => 'Glándula tiroides poco activa'],
            ];
            foreach ($enfermedades as $e) { \App\Models\EnfermedadImportante::create($e); }
        }
    }

    private function getAdminToolsArray(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'confirmar_cita',
                    'description' => 'Confirma una cita pendiente. Estado requerido: pendiente. La cita no debe haber pasado.',
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
                    'description' => 'Cancela una cita. Estados: pendiente, confirmada, en_espera. No citas pasadas. Pregunta confirmación.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita a cancelar'],
                            'comentario' => ['type' => 'string', 'description' => 'Motivo (opcional)'],
                        ],
                        'required' => ['cita_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'reprogramar_cita',
                    'description' => 'Reprograma una cita. Estados: pendiente, confirmada. Cita no pasada. Nueva fecha futura.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cita_id' => ['type' => 'integer', 'description' => 'ID de la cita'],
                            'nueva_fecha' => ['type' => 'string', 'description' => 'Nueva fecha en formato Y-m-d H:i (futura)'],
                        ],
                        'required' => ['cita_id', 'nueva_fecha'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'marcar_no_asistio',
                    'description' => 'Marca cita como no asistió. Estados: pendiente, confirmada, en_espera. Solo mismo día. Pregunta confirmación.',
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
                    'description' => 'Paciente en sala de espera. Estado: confirmada. Solo el mismo día de la cita.',
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
                    'description' => 'Paciente en consulta. Estado: en_espera. Solo el mismo día de la cita.',
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
                    'description' => 'Finaliza consulta. Estado: en_consulta. Solo el mismo día de la cita.',
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

    private function executeAdminTool(string $name, array $args, User $admin, User $medico): array
    {
        return match ($name) {
            'confirmar_cita'   => $this->adminToolConfirmarCita((int)($args['cita_id'] ?? 0), $admin),
            'cancelar_cita'    => $this->adminToolCancelarCita((int)($args['cita_id'] ?? 0), $admin, $args['comentario'] ?? ''),
            'reprogramar_cita' => $this->adminToolReprogramarCita((int)($args['cita_id'] ?? 0), $args['nueva_fecha'] ?? '', $admin),
            'marcar_no_asistio' => $this->adminToolMarcarNoAsistio((int)($args['cita_id'] ?? 0), $admin),
            'pasar_en_espera'  => $this->adminToolPasarEnEspera((int)($args['cita_id'] ?? 0), $admin),
            'pasar_en_consulta' => $this->adminToolPasarEnConsulta((int)($args['cita_id'] ?? 0), $admin),
            'finalizar_cita'   => $this->adminToolFinalizarCita((int)($args['cita_id'] ?? 0), $admin),
            default            => ['success' => false, 'error' => "Función desconocida: {$name}"],
        };
    }

    private function adminToolConfirmarCita(int $citaId, User $admin): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->estado !== 'pendiente') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo se pueden confirmar citas pendientes."];
        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes confirmar una cita cuya fecha ya pasó.'];

        return $this->adminAplicarTransicion($cita, 'confirmada', $admin, 'Cita confirmada por el administrador.');
    }

    private function adminToolCancelarCita(int $citaId, User $admin, string $comentario = ''): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];

        $permitidos = ['pendiente', 'confirmada', 'en_espera'];
        if (!in_array($cita->estado, $permitidos)) {
            return ['success' => false, 'error' => "No se puede cancelar una cita en estado '{$cita->estado}'."];
        }

        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes cancelar una cita cuya fecha ya pasó.'];

        $comentarioFinal = $comentario ?: 'Cancelada por el administrador.';

        return $this->adminAplicarTransicion($cita, 'cancelada', $admin, $comentarioFinal);
    }

    private function adminToolReprogramarCita(int $citaId, string $nuevaFecha, User $admin): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];

        $permitidos = ['pendiente', 'confirmada'];
        if (!in_array($cita->estado, $permitidos)) {
            return ['success' => false, 'error' => "No se puede reprogramar una cita en estado '{$cita->estado}'."];
        }

        if ($cita->fecha_hora->isPast()) return ['success' => false, 'error' => 'No puedes reprogramar una cita cuya fecha ya pasó.'];

        try {
            $fecha = \Carbon\Carbon::parse($nuevaFecha);
        } catch (\Exception $e) {
            return ['success' => false, 'error' => "Formato de fecha inválido: '{$nuevaFecha}'. Usa Y-m-d H:i."];
        }

        if ($fecha->lessThan(now()->subMinutes(2))) {
            return ['success' => false, 'error' => 'La nueva fecha debe ser actual o futura.'];
        }

        $comentarioFinal = "Reprogramada por administrador. Nueva fecha: {$fecha->format('d/m/Y H:i')}.";

        return $this->adminAplicarTransicion($cita, 'reprogramada', $admin, $comentarioFinal, ['fecha_reprogramada' => $fecha->format('Y-m-d H:i:s'), 'reprogramacion_rechazada' => null]);
    }

    private function adminToolMarcarNoAsistio(int $citaId, User $admin): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];

        $permitidos = ['pendiente', 'confirmada', 'en_espera'];
        if (!in_array($cita->estado, $permitidos)) {
            return ['success' => false, 'error' => "No se puede marcar como no asistió una cita en estado '{$cita->estado}'."];
        }

        if (!$cita->fecha_hora->isToday()) {
            return ['success' => false, 'error' => 'Solo puedes marcar como no asistió citas del día de hoy.'];
        }

        return $this->adminAplicarTransicion($cita, 'no_asistio', $admin, 'Paciente no asistió.');
    }

    private function adminToolPasarEnEspera(int $citaId, User $admin): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->estado !== 'confirmada') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo se pueden pasar a espera citas confirmadas."];
        if (!$cita->fecha_hora->isToday()) return ['success' => false, 'error' => 'Solo puedes pasar a espera citas del día de hoy.'];

        return $this->adminAplicarTransicion($cita, 'en_espera', $admin, 'Paciente en sala de espera.');
    }

    private function adminToolPasarEnConsulta(int $citaId, User $admin): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->estado !== 'en_espera') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo desde en_espera."];
        if (!$cita->fecha_hora->isToday()) return ['success' => false, 'error' => 'Solo puedes pasar a consulta citas del día de hoy.'];

        return $this->adminAplicarTransicion($cita, 'en_consulta', $admin, 'Inicio de consulta.');
    }

    private function adminToolFinalizarCita(int $citaId, User $admin): array
    {
        $cita = CitaMedica::find($citaId);
        if (!$cita) return ['success' => false, 'error' => "Cita #{$citaId} no encontrada."];
        if ($cita->estado !== 'en_consulta') return ['success' => false, 'error' => "La cita está en estado '{$cita->estado}'. Solo desde en_consulta."];
        if (!$cita->fecha_hora->isToday()) return ['success' => false, 'error' => 'Solo puedes finalizar citas del día de hoy.'];

        return $this->adminAplicarTransicion($cita, 'finalizada', $admin, 'Consulta finalizada.');
    }

    private function adminAplicarTransicion(CitaMedica $cita, string $nuevoEstado, User $user, string $comentario, array $extraData = []): array
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
            Log::error('Admin tool transition error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => 'Error al actualizar el estado de la cita.'];
        }
    }
}
