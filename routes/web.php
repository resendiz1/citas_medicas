<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\MedicoBloqueoController;
use App\Http\Controllers\MedicoHorarioController;
use App\Http\Controllers\ConsultaMedicaController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\EstadisticaController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\BugReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/ayuda', function () { return view('ayuda'); })->name('ayuda');
    Route::get('/bug-report', [BugReportController::class, 'create'])->name('bug-report.create');
    Route::post('/bug-report', [BugReportController::class, 'store'])->name('bug-report.store');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/citas/check', [DashboardController::class, 'checkNuevas'])->name('dashboard.citas.check');

    Route::get('/notificaciones/poll', [NotificacionController::class, 'poll'])->name('notificaciones.poll');
    Route::get('/notificaciones/dropdown', [NotificacionController::class, 'dropdown'])->name('notificaciones.dropdown');
    Route::post('/notificaciones/{id}/leida', [NotificacionController::class, 'markAsRead'])->name('notificaciones.leida');
    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/chat-leidas', [NotificacionController::class, 'marcarChatLeidas'])->name('notificaciones.chat-leidas');
    Route::post('/user/logs/geo', [\App\Http\Controllers\UserLogController::class, 'storeGeo'])->name('user.logs.store');

    Route::middleware('role:paciente')->group(function () {
        Route::get('/paciente/perfil', [PacienteController::class, 'perfilShow'])->name('paciente.perfil');
        Route::get('/paciente/historial', [PacienteController::class, 'historial'])->name('paciente.historial');
        Route::put('/paciente/perfil', [PacienteController::class, 'perfilUpdate'])->name('paciente.perfil.update');
        Route::post('/paciente/contactos', [PacienteController::class, 'contactoStore'])->name('paciente.contactos.store');
        Route::put('/paciente/contactos/{contacto}', [PacienteController::class, 'contactoUpdate'])->name('paciente.contactos.update');
        Route::delete('/paciente/contactos/{contacto}', [PacienteController::class, 'contactoDestroy'])->name('paciente.contactos.destroy');
        Route::post('/paciente/alergias', [PacienteController::class, 'alergiaStore'])->name('paciente.alergias.store');
        Route::put('/paciente/alergias/{alergium}', [PacienteController::class, 'alergiaUpdate'])->name('paciente.alergias.update');
        Route::delete('/paciente/alergias/{alergium}', [PacienteController::class, 'alergiaDestroy'])->name('paciente.alergias.destroy');
        Route::post('/paciente/enfermedades', [PacienteController::class, 'enfermedadStore'])->name('paciente.enfermedades.store');
        Route::put('/paciente/enfermedades/{enfermedadImportante}', [PacienteController::class, 'enfermedadUpdate'])->name('paciente.enfermedades.update');
        Route::delete('/paciente/enfermedades/{enfermedadImportante}', [PacienteController::class, 'enfermedadDestroy'])->name('paciente.enfermedades.destroy');
        Route::get('/paciente/chat-ia', [PacienteController::class, 'chatIAIndex'])->name('paciente.chat-ia.index');
        Route::get('/paciente/chat-ia/historial', [PacienteController::class, 'chatIAHistorial'])->name('paciente.chat-ia.historial');
        Route::post('/paciente/chat-ia', [PacienteController::class, 'chatIA'])->name('paciente.chat-ia');
        Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
        Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
        Route::post('/citas/{cita}/reprogramacion/confirmar', [CitaController::class, 'confirmarReprogramacion'])->name('citas.reprogramacion.confirmar');
        Route::post('/citas/{cita}/reprogramacion/cancelar', [CitaController::class, 'cancelarReprogramacion'])->name('citas.reprogramacion.cancelar');
        Route::get('/medicos/{medico}', function ($medicoId) {
            $medico = App\Models\User::where('role', 'medico')
                ->whereHas('medicoPerfil', fn($q) => $q->where('aprobado', true))
                ->with('medicoPerfil.tipoMedico', 'medicoPerfil.documentos', 'horarios', 'bloqueos')
                ->findOrFail($medicoId);

            if (!$medico->medicoPerfil) {
                $medico->setRelation('medicoPerfil', new App\Models\MedicoPerfil());
            }

            return view('paciente.medico-show', compact('medico'));
        })->name('paciente.medicos.show');

    });

    Route::middleware('role:medico')->group(function () {
        Route::get('/medico/pacientes', [MedicoController::class, 'pacientesIndex'])->name('medico.pacientes.index');
        Route::get('/medico/pacientes/crear', [MedicoController::class, 'pacientesCreate'])->name('medico.pacientes.create');
        Route::post('/medico/pacientes', [MedicoController::class, 'pacientesStore'])->name('medico.pacientes.store');
        Route::get('/medico/pacientes/{id}/editar', [MedicoController::class, 'pacientesEdit'])->name('medico.pacientes.edit');
        Route::put('/medico/pacientes/{id}', [MedicoController::class, 'pacientesUpdate'])->name('medico.pacientes.update');
        Route::delete('/medico/pacientes/{id}', [MedicoController::class, 'pacientesDestroy'])->name('medico.pacientes.destroy');
        Route::get('/medico/pacientes/{id}', [MedicoController::class, 'pacienteShow'])->name('medico.paciente.show');
        Route::get('/medico/citas/crear', [MedicoController::class, 'citaCreate'])->name('medico.citas.create');
        Route::post('/medico/citas', [MedicoController::class, 'citaStore'])->name('medico.citas.store');
        Route::get('/medico/perfil', [MedicoController::class, 'perfilShow'])->name('medico.perfil');
        Route::put('/medico/perfil', [MedicoController::class, 'perfilUpdate'])->name('medico.perfil.update');
        Route::post('/medico/toggle-activo', [MedicoController::class, 'toggleActivo'])->name('medico.toggle-activo');
        Route::get('/citas/{id}/receta/crear', [RecetaController::class, 'create'])->name('recetas.create');
        Route::post('/citas/{id}/receta', [RecetaController::class, 'store'])->name('recetas.store');
        Route::get('/medico/horarios', [MedicoHorarioController::class, 'index'])->name('medico.horarios');
        Route::post('/medico/horarios', [MedicoHorarioController::class, 'store'])->name('medico.horarios.store');
        Route::post('/medico/horarios/intervalo', [MedicoHorarioController::class, 'updateIntervalo'])->name('medico.horarios.intervalo');
        Route::delete('/medico/horarios/{id}', [MedicoHorarioController::class, 'destroy'])->name('medico.horarios.destroy');
        Route::get('/medico/bloqueos', [MedicoBloqueoController::class, 'index'])->name('medico.bloqueos');
        Route::post('/medico/bloqueos', [MedicoBloqueoController::class, 'store'])->name('medico.bloqueos.store');
        Route::delete('/medico/bloqueos/{id}', [MedicoBloqueoController::class, 'destroy'])->name('medico.bloqueos.destroy');
        Route::get('/citas/{cita}/consulta-medica', [ConsultaMedicaController::class, 'create'])->name('consulta-medica.create');
        Route::post('/citas/{cita}/consulta-medica', [ConsultaMedicaController::class, 'store'])->name('consulta-medica.store');
        Route::post('/citas/{cita}/consulta-medica/generar-receta', [ConsultaMedicaController::class, 'generarReceta'])->name('consulta-medica.generar-receta');
        Route::get('/medico/historial-citas', [MedicoController::class, 'historialCitas'])->name('medico.historial-citas');
        Route::get('/medico/chat-ia', [MedicoController::class, 'chatIAIndex'])->name('medico.chat-ia');
        Route::get('/medico/chat-ia/historial', [MedicoController::class, 'chatIAHistorial'])->name('medico.chat-ia.historial');
        Route::post('/medico/chat-ia', [MedicoController::class, 'chatIASend'])->name('medico.chat-ia.send');
        Route::post('/medico/documentos', [MedicoController::class, 'documentosStore'])->name('medico.documentos.store');
        Route::delete('/medico/documentos/{id}', [MedicoController::class, 'documentosDestroy'])->name('medico.documentos.destroy');

    });

    Route::put('/citas/{cita}/estado', [CitaController::class, 'updateEstado'])->name('citas.estado');
    Route::get('/citas/estados/poll', [CitaController::class, 'estadosPoll'])->name('citas.estados.poll');
    Route::get('/citas/{cita}/acciones', [CitaController::class, 'acciones'])->name('citas.acciones');
    Route::get('/citas/{cita}', [CitaController::class, 'show'])->name('citas.show');
    Route::get('/chat/citas', [ChatController::class, 'citas'])->name('chat.citas');
    Route::get('/citas/{cita}/chat', [ChatController::class, 'mensajes'])->name('chat.mensajes');
    Route::post('/citas/{cita}/chat', [ChatController::class, 'send'])->name('chat.send');

    Route::get('/citas/{cita}/consulta-medica/detalle', [ConsultaMedicaController::class, 'show'])->name('consulta-medica.show');

    Route::get('/recetas/{id}', [RecetaController::class, 'show'])->name('recetas.show');
    Route::get('/recetas/documentos/{id}/descargar', [RecetaController::class, 'downloadDocumento'])->name('recetas.documento.download');
    Route::get('/medico/documentos/{id}/descargar', function ($id) {
        $doc = App\Models\MedicoDocumento::findOrFail($id);
        if (!Storage::disk('public')->exists($doc->ruta_archivo)) {
            abort(404, 'Archivo no encontrado.');
        }
        return Storage::disk('public')->download($doc->ruta_archivo, $doc->nombre_original);
    })->name('medico.documentos.download');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/citas', [AdminController::class, 'citas'])->name('citas');
        Route::delete('/citas/{id}', [AdminController::class, 'citasDestroy'])->name('citas.destroy');
        Route::get('/medicos', [AdminController::class, 'medicos'])->name('medicos');
        Route::get('/medicos/{medico}/chat-ia/historial', [AdminController::class, 'medicoChatIAHistorial'])->name('medicos.chat-ia.historial');
        Route::post('/medicos/{medico}/chat-ia', [AdminController::class, 'medicoChatIA'])->name('medicos.chat-ia');
        Route::get('/medicos/{id}', [AdminController::class, 'medicosShow'])->name('medicos.show');
        Route::post('/medicos/{id}/aprobar', [AdminController::class, 'medicosAprobar'])->name('medicos.aprobar');
        Route::get('/medicos/crear', [AdminController::class, 'medicosCreate'])->name('medicos.create');
        Route::post('/medicos', [AdminController::class, 'medicosStore'])->name('medicos.store');
        Route::get('/medicos/{id}/editar', [AdminController::class, 'medicosEdit'])->name('medicos.edit');
        Route::put('/medicos/{id}', [AdminController::class, 'medicosUpdate'])->name('medicos.update');
        Route::delete('/medicos/{id}', [AdminController::class, 'medicosDestroy'])->name('medicos.destroy');
        Route::get('/medicos/{medico}/horarios', [MedicoHorarioController::class, 'index'])->name('medicos.horarios');
        Route::post('/medicos/{medico}/horarios', [MedicoHorarioController::class, 'store'])->name('medicos.horarios.store');
        Route::post('/medicos/{medico}/horarios/intervalo', [MedicoHorarioController::class, 'updateIntervalo'])->name('medicos.horarios.intervalo');
        Route::delete('/medicos/{medico}/horarios/{id}', [MedicoHorarioController::class, 'destroy'])->name('medicos.horarios.destroy');
        Route::get('/medicos/{medico}/bloqueos', [MedicoBloqueoController::class, 'index'])->name('medicos.bloqueos');
        Route::post('/medicos/{medico}/bloqueos', [MedicoBloqueoController::class, 'store'])->name('medicos.bloqueos.store');
        Route::delete('/medicos/{medico}/bloqueos/{id}', [MedicoBloqueoController::class, 'destroy'])->name('medicos.bloqueos.destroy');
        Route::get('/pacientes', [AdminController::class, 'pacientes'])->name('pacientes');
        Route::get('/pacientes/crear', [AdminController::class, 'pacientesCreate'])->name('pacientes.create');
        Route::post('/pacientes', [AdminController::class, 'pacientesStore'])->name('pacientes.store');
        Route::get('/pacientes/{id}/editar', [AdminController::class, 'pacientesEdit'])->name('pacientes.edit');
        Route::put('/pacientes/{id}', [AdminController::class, 'pacientesUpdate'])->name('pacientes.update');
        Route::delete('/pacientes/{id}', [AdminController::class, 'pacientesDestroy'])->name('pacientes.destroy');
        Route::get('/bug-reports', [AdminController::class, 'bugReports'])->name('bug-reports');
        Route::post('/bug-reports/{id}/responder', [AdminController::class, 'bugReportResponder'])->name('bug-reports.responder');
        Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
        Route::post('/reset', [AdminController::class, 'reset'])->name('reset');
    });

    Route::get('/estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas.index');
    Route::get('/estadisticas/medico', [EstadisticaController::class, 'medico'])->name('estadisticas.medico');
    Route::get('/estadisticas/paciente', [EstadisticaController::class, 'paciente'])->name('estadisticas.paciente');
    Route::get('/estadisticas/admin/general', [EstadisticaController::class, 'adminGeneral'])->name('estadisticas.admin.general');
    Route::get('/estadisticas/admin/{medico}', [EstadisticaController::class, 'admin'])->name('estadisticas.admin');
});
