@extends('layouts.app')

@section('title', 'Ayuda y Soporte')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-2 p-4 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:56px;height:56px;background:#1266f1;color:#121212;font-size:1.4rem;font-weight:bold">
                    <i class="fa fa-headset"></i>
                </div>
                <div class="text-center">
                    <h3 class="mb-1">Manual de Usuario</h3>
                    <p class="mb-0 text-muted">&lt;JuanPancho's/&gt; — Sistema de Gestión de Citas Médicas</p>
                </div>
            </div>
        </div>
    </div>

    @include('partials._help-section')

    <div class="card shadow-2 p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color:#1266f1"><i class="fa fa-book me-2"></i>Manual de la Aplicación</h5>
        <p class="text-muted small mb-4">
            Este manual describe todas las funcionalidades del sistema de gestión de citas médicas, organizado por rol de usuario.
        </p>

        <div class="d-flex flex-column gap-2 mb-4">
            @if (auth()->user()->esPaciente())
            <a href="#manual-paciente" class="p-3 text-decoration-none" style="background:rgba(59,113,202,0.08);border-radius:12px;border-left:4px solid #3b71ca">
                <span class="fw-bold" style="color:#3b71ca"><i class="fa fa-user me-2"></i>Paciente</span>
                <small class="d-block text-muted mt-1">Agendar citas, ver historial, gestionar perfil</small>
            </a>
            @endif
            @if (auth()->user()->esMedico())
            <a href="#manual-medico" class="p-3 text-decoration-none" style="background:rgba(20,164,77,0.08);border-radius:12px;border-left:4px solid #14a44d">
                <span class="fw-bold" style="color:#14a44d"><i class="fa fa-user-doctor me-2"></i>Médico</span>
                <small class="d-block text-muted mt-1">Consultas, recetas, horarios, historial</small>
            </a>
            @endif
            @if (auth()->user()->esAdmin())
            <a href="#manual-admin" class="p-3 text-decoration-none" style="background:rgba(228,161,27,0.08);border-radius:12px;border-left:4px solid #e4a11b">
                <span class="fw-bold" style="color:#e4a11b"><i class="fa fa-shield-halved me-2"></i>Administrador</span>
                <small class="d-block text-muted mt-1">Gestión de médicos, pacientes y citas</small>
            </a>
            @endif
        </div>

        @if (auth()->user()->esPaciente())
        {{-- PACIENTE --}}
        <details class="mb-3" id="manual-paciente" style="border:1px solid rgba(59,113,202,0.2);border-radius:12px;overflow:hidden" open>
            <summary class="p-3 fw-bold" style="background:rgba(59,113,202,0.06);color:#3b71ca;cursor:pointer">
                <i class="fa fa-user me-2"></i>Guía para Paciente
            </summary>
            <div class="p-3" style="background:#fff">
                <div class="mb-3">
                    <h6 style="color:#3b71ca"><i class="fa fa-right-to-bracket me-1"></i>Registro e Inicio de Sesión</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>En la página principal, haz clic en <strong>"Registrarse"</strong>.</li>
                        <li>Selecciona el rol <strong>"Paciente"</strong>.</li>
                        <li>Completa tu nombre, correo electrónico y contraseña.</li>
                        <li>Recibirás un correo de bienvenida.</li>
                        <li>Inicia sesión con tu correo y contraseña desde <strong>"Iniciar Sesión"</strong>.</li>
                        <li>También puedes usar <strong>"Continuar con Google"</strong> para un registro más rápido.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#3b71ca"><i class="fa fa-calendar-plus me-1"></i>Agendar una Cita</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Desde el <strong>Dashboard</strong>, haz clic en <strong>"Nueva Cita"</strong>.</li>
                        <li>Selecciona un <strong>médico</strong> de la lista de disponibles.</li>
                        <li>Elige una <strong>fecha</strong> — el calendario mostrará los horarios disponibles.</li>
                        <li>Selecciona un <strong>horario</strong> (slots según el intervalo del médico).</li>
                        <li>Escribe el <strong>motivo</strong> de la consulta.</li>
                        <li>Haz clic en <strong>"Agendar Cita"</strong>.</li>
                        <li>Recibirás una notificación de confirmación.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#3b71ca"><i class="fa fa-eye me-1"></i>Ver Detalle de una Cita</h6>
                    <p class="small text-muted mb-0">Desde el Dashboard, en la lista de tus citas, haz clic en <strong>"Ver detalles"</strong>. Podrás ver la información completa de la cita, incluyendo recetas y consultas médicas si ya fueron realizadas.</p>
                </div>
                <div class="mb-3">
                    <h6 style="color:#3b71ca"><i class="fa fa-ban me-1"></i>Cancelar una Cita</h6>
                    <p class="small text-muted mb-0">Solo puedes cancelar citas en estado <strong>"Pendiente"</strong>. Desde el Dashboard, haz clic en <strong>"Cancelar"</strong> en la cita correspondiente. Las citas confirmadas o en proceso no pueden cancelarse por el paciente.</p>
                </div>
                <div class="mb-3">
                    <h6 style="color:#3b71ca"><i class="fa fa-message me-1"></i>Chat con el Médico</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Desde cualquier página, haz clic en el <strong>botón flotante de chat</strong> (esquina inferior derecha).</li>
                        <li>Selecciona la <strong>cita</strong> con la que deseas chatear del menú desplegable.</li>
                        <li>Escribe tu mensaje y presiona <strong>Enter</strong> o haz clic en <strong>Enviar</strong>.</li>
                        <li>Los mensajes nuevos se cargan automáticamente cada 2 segundos.</li>
                        <li>También puedes abrir el chat directamente desde el botón <strong>"Chat"</strong> en la lista de citas.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#3b71ca"><i class="fa fa-clock-rotate-left me-1"></i>Historial de Citas</h6>
                    <p class="small text-muted mb-0">En tu perfil de paciente, encontrarás la sección <strong>"Mis Citas"</strong> con todas tus citas ordenadas por fecha descendente. También puedes acceder desde <strong>"Mi Historial"</strong> en la página de perfil.</p>
                </div>
                <div class="mb-0">
                    <h6 style="color:#3b71ca"><i class="fa fa-user-pen me-1"></i>Completar tu Perfil</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Haz clic en tu nombre en la esquina superior derecha y selecciona <strong>"Mi Perfil"</strong>.</li>
                        <li>Haz clic en <strong>"Editar"</strong> para modificar tus datos.</li>
                        <li>Completa: teléfono, fecha de nacimiento, dirección y sube una foto.</li>
                        <li>Agrega <strong>contactos de emergencia</strong>, <strong>alergias</strong> y <strong>enfermedades importantes</strong> usando los botones <strong>"+ Agregar"</strong>.</li>
                        <li>Haz clic en <strong>"Guardar cambios"</strong>.</li>
                    </ol>
                </div>
            </div>
        </details>
        @endif

        @if (auth()->user()->esMedico())
        {{-- MÉDICO --}}
        <details class="mb-3" id="manual-medico" style="border:1px solid rgba(20,164,77,0.2);border-radius:12px;overflow:hidden" open>
            <summary class="p-3 fw-bold" style="background:rgba(20,164,77,0.06);color:#14a44d;cursor:pointer">
                <i class="fa fa-user-doctor me-2"></i>Guía para Médico
            </summary>
            <div class="p-3" style="background:#fff">
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-user-pen me-1"></i>Completar tu Perfil</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Ve a <strong>"Mi Perfil"</strong> desde el menú superior.</li>
                        <li>Haz clic en <strong>"Editar"</strong>.</li>
                        <li>Completa: especialidad, cédula profesional, teléfono, fecha de nacimiento, universidad, años de experiencia y descripción.</li>
                        <li>Sube tu <strong>foto de perfil</strong> (JPG, PNG, GIF o WebP, máximo 2MB).</li>
                        <li>En la sección <strong>"Mis Documentos"</strong>, sube tu cédula profesional y otros documentos (PDF o imágenes, máximo 20MB).</li>
                        <li>Haz clic en <strong>"Guardar cambios"</strong>.</li>
                        <li>Espera la <strong>aprobación del administrador</strong> para que los pacientes puedan ver tu perfil.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-clock me-1"></i>Gestionar Horarios</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Ve a <strong>"Mi Agenda → Horarios"</strong> en el menú.</li>
                        <li>Define tu <strong>intervalo de atención</strong> (15–120 minutos) — esto determina los slots disponibles para citas.</li>
                        <li>Agrega horarios seleccionando <strong>día</strong>, <strong>hora de inicio</strong> y <strong>hora de fin</strong>.</li>
                        <li>Marca el horario como <strong>activo</strong> para que esté disponible.</li>
                        <li>Puedes eliminar horarios existentes si es necesario.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-ban me-1"></i>Bloqueos de Disponibilidad</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Ve a <strong>"Mi Agenda → Bloqueos"</strong> en el menú.</li>
                        <li>Haz clic en <strong>"+ Nuevo Bloqueo"</strong>.</li>
                        <li>Selecciona la <strong>fecha de inicio</strong> y <strong>fecha de fin</strong> del bloqueo.</li>
                        <li>Escribe el <strong>motivo</strong> (ej. "Vacaciones", "Día personal").</li>
                        <li>Los pacientes no podrán agendar citas en ese período.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-stethoscope me-1"></i>Realizar una Consulta Médica</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Desde el Dashboard, en la lista de tus citas, busca una en estado <strong>"En espera"</strong>.</li>
                        <li>Haz clic en el botón de acciones y selecciona la opción para iniciar la consulta (la cita pasará automáticamente a <strong>"En consulta"</strong>).</li>
                        <li>Completa el formulario de consulta con:
                            <ul>
                                <li><strong>Síntomas</strong> — agrega tantos como necesites con el botón "+"</li>
                                <li><strong>Dolores</strong> — ubicación, intensidad (1-10), duración</li>
                                <li><strong>Signos vitales</strong> — presión, temperatura, frecuencia cardíaca, peso, talla (IMC se calcula automáticamente)</li>
                                <li><strong>Exploración física</strong> y <strong>diagnóstico</strong></li>
                                <li><strong>Medicamentos</strong> — nombre, dosis, frecuencia, duración. Marca <strong>"Incluir en receta"</strong> para los que el paciente necesitará</li>
                            </ul>
                        </li>
                        <li>Tienes dos opciones:
                            <ul>
                                <li><strong>"Guardar borrador"</strong> — guarda el progreso sin finalizar la cita</li>
                                <li><strong>"Finalizar consulta"</strong> — guarda y cambia el estado de la cita a <strong>"Finalizada"</strong>, generando automáticamente una receta con los medicamentos marcados</li>
                            </ul>
                        </li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-prescription me-1"></i>Recetas Médicas</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Durante una consulta, al marcar medicamentos con <strong>"Incluir en receta"</strong> y hacer clic en <strong>"Finalizar consulta"</strong>, la receta se genera automáticamente.</li>
                        <li>También puedes crear una receta manualmente desde el detalle de la cita (solo disponible el mismo día de la cita).</li>
                        <li>La receta incluye: diagnóstico, indicaciones, medicamentos (con dosis y frecuencia), notas y documentos adjuntos.</li>
                        <li>El paciente puede ver la receta desde el detalle de la cita.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-message me-1"></i>Chat con el Paciente</h6>
                    <p class="small text-muted mb-0">Usa el botón flotante de chat (esquina inferior derecha) para comunicarte con tus pacientes. Selecciona la cita del paciente en el menú desplegable. También puedes abrir el chat directamente desde el botón <strong>"Chat"</strong> en la lista de citas de tu Dashboard.</p>
                </div>
                <div class="mb-3">
                    <h6 style="color:#14a44d"><i class="fa fa-clock-rotate-left me-1"></i>Historial de Citas</h6>
                    <p class="small text-muted mb-0">Ve a <strong>"Historial de citas"</strong> en el menú superior para ver todas tus citas pasadas con paginación.</p>
                </div>
                <div class="mb-0">
                    <h6 style="color:#14a44d"><i class="fa fa-arrows-rotate me-1"></i>Cambiar Estado de una Cita</h6>
                    <p class="small text-muted mb-0">Desde el Dashboard, en la lista de citas, usa el menú de acciones para cambiar el estado. Las transiciones permitidas son: Pendiente → Confirmada → En espera → En consulta → Finalizada. También puedes cancelar o marcar como "No asistió" desde los estados permitidos.</p>
                </div>
            </div>
        </details>
        @endif

        @if (auth()->user()->esAdmin())
        {{-- ADMIN --}}
        <details class="mb-3" id="manual-admin" style="border:1px solid rgba(228,161,27,0.2);border-radius:12px;overflow:hidden" open>
            <summary class="p-3 fw-bold" style="background:rgba(228,161,27,0.06);color:#e4a11b;cursor:pointer">
                <i class="fa fa-shield-halved me-2"></i>Guía para Administrador
            </summary>
            <div class="p-3" style="background:#fff">
                <div class="mb-3">
                    <h6 style="color:#e4a11b"><i class="fa fa-user-doctor me-1"></i>Gestión de Médicos</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Ve a <strong>"Gestión → Médicos"</strong> en el menú.</li>
                        <li>Puedes <strong>crear</strong> médicos manualmente con el botón <strong>"+ Nuevo Médico"</strong>.</li>
                        <li>Usa el <strong>buscador</strong> para filtrar por nombre o email.</li>
                        <li>Haz clic en <strong>"Aprobar"</strong> para activar a un médico pendiente (recibirá una notificación por correo).</li>
                        <li>Puedes <strong>editar</strong> o <strong>eliminar</strong> médicos desde los botones de acción.</li>
                        <li>También puedes gestionar los <strong>horarios</strong> y <strong>bloqueos</strong> de cada médico desde la vista de detalle.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#e4a11b"><i class="fa fa-user me-1"></i>Gestión de Pacientes</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Ve a <strong>"Gestión → Pacientes"</strong> en el menú.</li>
                        <li>Puedes <strong>crear</strong> pacientes manualmente.</li>
                        <li>Usa el <strong>buscador</strong> para encontrar pacientes.</li>
                        <li>Puedes <strong>editar</strong> o <strong>eliminar</strong> pacientes desde los botones de acción.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#e4a11b"><i class="fa fa-calendar me-1"></i>Gestión de Citas</h6>
                    <ol class="small text-muted mb-0" style="line-height:1.8">
                        <li>Ve a <strong>"Gestión → Citas"</strong> en el menú.</li>
                        <li>Filtra por <strong>estado</strong> (pendiente, confirmada, en espera, etc.).</li>
                        <li>Usa el <strong>buscador</strong> para encontrar citas específicas.</li>
                        <li>Puedes <strong>cambiar el estado</strong> de cualquier cita desde el menú de acciones.</li>
                        <li>Puedes <strong>eliminar</strong> citas si es necesario.</li>
                    </ol>
                </div>
                <div class="mb-3">
                    <h6 style="color:#e4a11b"><i class="fa fa-chart-simple me-1"></i>Estadísticas</h6>
                    <p class="small text-muted mb-0">Ve a <strong>"Estadísticas"</strong> en el menú para ver gráficos y reportes generales del sistema, incluyendo citas por médico, pacientes atendidos y más.</p>
                </div>
                <div class="mb-0">
                    <h6 style="color:#e4a11b"><i class="fa fa-trash-can me-1"></i>Restablecer Base de Datos</h6>
                    <p class="small text-muted mb-0">En el Dashboard, al final de las tarjetas de estadísticas, encontrarás el botón <strong>"Restablecer BD"</strong>. Esto eliminará todos los datos del sistema (médicos, pacientes, citas, etc.) y solo conservará tu cuenta de administrador. Los datos de referencia (especialidades, alergias, enfermedades) se restauran automáticamente. Usa esta opción con precaución.</p>
                </div>
            </div>
        </details>
        @endif
    </div>

    <div class="card shadow-2 p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color:#1266f1"><i class="fa fa-diagram-project me-2"></i>Flujo de Estados de una Cita</h5>
        <div class="d-flex flex-wrap align-items-center gap-2 p-3" style="background:rgba(18,102,241,0.04);border-radius:12px">
            <span class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem">Pendiente</span>
            <i class="fa fa-arrow-right text-muted"></i>
            <span class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem">Confirmada</span>
            <i class="fa fa-arrow-right text-muted"></i>
            <span class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem">En espera</span>
            <i class="fa fa-arrow-right text-muted"></i>
            <span class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem">En consulta</span>
            <i class="fa fa-arrow-right text-muted"></i>
            <span class="badge" style="border:2px solid #636e72;color:#636e72;background:transparent;padding:0.5rem 0.75rem">Finalizada</span>
            <div class="w-100 mt-2"></div>
            <small class="text-muted">
                <i class="fa fa-arrow-right fa-rotate-90 me-1"></i> Desde Pendiente/Confirmada también se puede ir a
                <span class="badge bg-danger" style="padding:0.3rem 0.5rem">Cancelada</span>
                o
                <span class="badge bg-info" style="padding:0.3rem 0.5rem">Reprogramada</span>
                o
                <span class="badge bg-danger" style="padding:0.3rem 0.5rem;background:#dc143c">No asistió</span>
            </small>
        </div>
    </div>
</div>
@endsection
