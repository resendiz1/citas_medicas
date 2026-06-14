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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/citas/check', [DashboardController::class, 'checkNuevas'])->name('dashboard.citas.check');

    Route::get('/notificaciones/poll', [NotificacionController::class, 'poll'])->name('notificaciones.poll');

    Route::middleware('role:paciente')->group(function () {
        Route::get('/paciente/perfil', [PacienteController::class, 'perfilShow'])->name('paciente.perfil');
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
        Route::get('/citas/crear', [CitaController::class, 'create'])->name('citas.create');
        Route::post('/citas', [CitaController::class, 'store'])->name('citas.store');
        Route::post('/citas/{cita}/reprogramacion/confirmar', [CitaController::class, 'confirmarReprogramacion'])->name('citas.reprogramacion.confirmar');
        Route::post('/citas/{cita}/reprogramacion/cancelar', [CitaController::class, 'cancelarReprogramacion'])->name('citas.reprogramacion.cancelar');
        Route::get('/medicos/{medico}', function ($medicoId) {
            $medico = App\Models\User::where('role', 'medico')
                ->with('medicoPerfil.tipoMedico', 'medicoPerfil.documentos', 'horarios', 'bloqueos')
                ->findOrFail($medicoId);

            if (!$medico->medicoPerfil) {
                $medico->setRelation('medicoPerfil', new App\Models\MedicoPerfil());
            }

            return view('paciente.medico-show', compact('medico'));
        })->name('paciente.medicos.show');
    });

    Route::middleware('role:medico')->group(function () {
        Route::get('/medico/pacientes/{id}', [MedicoController::class, 'pacienteShow'])->name('medico.paciente.show');
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
        Route::get('/medicos/{id}', [AdminController::class, 'medicosShow'])->name('medicos.show');
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
    });

    Route::middleware('role:recepcionista')->group(function () {
        Route::get('/recepcionista/pacientes', [AdminController::class, 'pacientes'])->name('recepcionista.pacientes');
        Route::get('/recepcionista/pacientes/{id}', function ($id) {
            $paciente = App\Models\User::where('role', 'paciente')->with('contactosEmergencia', 'alergias', 'enfermedadesImportantes')->findOrFail($id);
            return view('recepcionista.paciente-show', compact('paciente'));
        })->name('recepcionista.pacientes.show');
    });
});
