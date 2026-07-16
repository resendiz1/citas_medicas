@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="row mb-4">
        <div class="col-12{{ $user->esMedico() || $user->esPaciente() ? ' col-lg-8' : '' }}">
            <div class="card shadow-2 p-4 h-100">
                <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:#1266f1;color:#121212;font-size:1.2rem;font-weight:bold;overflow:hidden">
                    @if ($user->foto_url)
                        <img src="{{ Storage::url($user->foto_url) }}" alt="Foto"
                             style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                             onclick="window.open('{{ Storage::url($user->foto_url) }}','_blank')">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <h3 class="mb-1">Bienvenido, {{ $user->name }}</h3>
                    <p class="mb-0 text-muted">
                        Rol:
                        @switch($user->role)
                            @case('admin') Administrador @break
                            @case('medico') Médico @break
                            @case('paciente') Paciente @break
                        @endswitch
                    </p>
                </div>
            </div>
            @if ($user->esPaciente())
            <div class="mt-3 border-top pt-3">
                <h6 class="fw-bold mb-2" style="color:var(--text-primary);font-size:0.85rem"><i class="fa fa-user-doctor me-1"></i>Médicos Disponibles</h6>
                <div class="row g-2">
                    @forelse ($medicos as $medico)
                    <div class="col-12 col-md-4">
                        <a href="{{ route('paciente.medicos.show', $medico->id) }}" class="text-decoration-none">
                            <div class="card shadow-2 p-2 h-100 d-flex flex-row align-items-center gap-2" style="border-radius:12px;cursor:pointer;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:36px;height:36px;background:#1266f1;color:#121212;font-size:0.85rem;font-weight:bold;overflow:hidden">
                                    @if ($medico->foto_url)
                                        <img src="{{ Storage::url($medico->foto_url) }}" alt="Foto"
                                             style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                                             onclick="window.open('{{ Storage::url($medico->foto_url) }}','_blank')">
                                    @else
                                        {{ strtoupper(substr($medico->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h6 class="mb-0" style="font-size:0.78rem;color:var(--text-emphasis);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $medico->name }}</h6>
                                    <small class="text-muted" style="font-size:0.65rem">{{ optional(optional($medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? 'General' }}</small>
                                </div>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="col-12"><div class="d-flex flex-column align-items-center py-2"><i class="fa fa-user-doctor fa-lg text-muted opacity-50 mb-1"></i><p class="fw-bold text-muted mb-0" style="font-size:0.85rem">No hay médicos registrados.</p></div></div>
                    @endforelse
                </div>
            </div>
            @endif
            </div>
        </div>
        @if ($user->esMedico() || $user->esPaciente())
        <div class="col-12 col-lg-4 mt-3 mt-lg-0">
            <div class="card shadow-2 p-2 h-100">
                <h6 class="fw-bold mb-2" style="color:#1266f1;font-size:0.85rem"><i class="fa fa-calendar me-1"></i>Calendario de Citas</h6>
                <div id="role-calendar" style="max-width:100%;font-size:0.7rem"></div>
            </div>
        </div>
        @endif
    </div>

    @push('head')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
    <style>
    #role-calendar .fc-daygrid-day-frame { min-height: 24px !important; }
    #role-calendar .fc-daygrid-day-number { font-size: 0.7rem !important; padding: 2px !important; }
    #role-calendar .fc-daygrid-day-events { min-height: auto !important; }
    #role-calendar .fc-daygrid-event { font-size: 0.6rem !important; padding: 0 2px !important; margin: 0 !important; border-radius: 2px !important; }
    #role-calendar .fc-header-toolbar { margin-bottom: 0.4rem !important; }
    #role-calendar .fc-toolbar-title { font-size: 0.85rem !important; }
    #role-calendar .fc-button { font-size: 0.7rem !important; padding: 0.3rem 0.6rem !important; }
    #role-calendar .fc-col-header-cell-cushion { font-size: 0.65rem !important; padding: 2px 0 !important; }
    #role-calendar .fc-scrollgrid { border: none !important; }
    #role-calendar .fc-theme-standard td, #role-calendar .fc-theme-standard th { border-color: rgba(0,0,0,0.06) !important; }
    #role-calendar .fc-day-other .fc-daygrid-day-top { opacity: 0.3; }
    </style>
    @endpush

    @if ($user->esAdmin())
        <div class="row g-4">
            @if ($user->esAdmin())
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ route('admin.pacientes') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-users fa-xl"></i></div>
                        <h5>Pacientes</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalPacientes }}</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ route('admin.medicos') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-user-doctor fa-xl"></i></div>
                        <h5>Médicos</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalMedicos }}</p>
                    </div>
                </a>
            </div>
            @if ($medicosPendientes > 0)
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ route('admin.medicos') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center" style="border:2px solid #ffc107 !important">
                        <div class="stat-icon mx-auto mb-3" style="color:#ffc107"><i class="fa fa-clock fa-xl"></i></div>
                        <h5>Por aprobar</h5>
                        <p class="display-6 mb-0 fw-bold" style="color:#ffc107">{{ $medicosPendientes }}</p>
                    </div>
                </a>
            </div>
            @endif
            @endif
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ route('admin.citas') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-calendar-check fa-xl"></i></div>
                        <h5>Citas</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalCitas }}</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ route('admin.citas', ['estado' => 'pendiente']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-clock fa-xl"></i></div>
                        <h5>Pendientes</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $citasPendientes }}</p>
                    </div>
                </a>
            </div>
        </div>
        @if ($user->esAdmin() && $medicosPendientes > 0)
        <div class="mt-4">
            <div class="card shadow-2 p-4">
                <h5 class="fw-bold mb-3" style="color:#ffc107"><i class="fa fa-clock me-2"></i>Médicos pendientes de aprobación</h5>
                <div class="table-responsive">
                    <table class="table neu-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Registro</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($medicosPendientesList as $medico)
                                <tr>
                                    <td class="fw-bold">{{ $medico->name }}</td>
                                    <td class="text-muted">{{ $medico->email }}</td>
                                    <td class="text-muted" style="font-size:0.82rem">{{ $medico->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <form action="{{ route('admin.medicos.aprobar', $medico->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar a {{ $medico->name }}?')">
                                                <i class="fa fa-check me-1"></i><span class="btn-text">Aprobar</span>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.medicos.destroy', $medico->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Rechazar y eliminar a {{ $medico->name }}?\n\nSe eliminará permanentemente.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" style="color:#ff4444;border-color:#ff4444">
                                                <i class="fa fa-xmark me-1"></i><span class="btn-text">Rechazar</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
        @if ($user->esAdmin())
        <div class="mt-4">
            <div class="card shadow-2 p-4">
                <div class="d-flex align-items-center gap-3">
                    <form action="{{ route('admin.reset') }}" method="POST" onsubmit="return confirm('¿Estás seguro de restablecer la base de datos?\n\nSe eliminarán TODOS los pacientes, médicos, citas, recetas, consultas y mensajes. Solo se conservará tu cuenta de administrador.\n\nEsta acción no se puede deshacer.')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fa fa-trash-can me-1"></i><span class="btn-text">Restablecer BD</span>
                        </button>
                    </form>
                    <span class="text-muted small">Elimina todos los datos excepto tu cuenta de administrador</span>
                </div>
            </div>
        </div>
        @endif
    @else
        @if ($user->esMedico())
        @php $medPerfil = $user->medicoPerfil; @endphp
        @if (!$medPerfil || !$medPerfil->aprobado)
        <div id="pending-approval-alert" class="alert alert-warning d-flex align-items-center gap-3 mb-4" role="alert" style="border:2px solid #ffc107;background:rgba(255,193,7,0.08);border-radius:16px;padding:18px 20px">
            <i class="fa fa-clock fa-lg" style="color:#ffc107"></i>
            <div>
                <strong style="color:#856404">Registro pendiente de aprobación</strong><br>
                <span style="color:#856404;font-size:0.88rem">Tu cuenta está siendo revisada por un administrador. Recibirás un correo cuando sea aprobada y los pacientes podrán ver tu perfil.</span>
            </div>
        </div>
        @endif
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Mis Citas</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('medico.perfil') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-user me-1"></i><span class="btn-text">Mi Perfil</span></a>
            </div>
        </div>
        @elseif ($user->esPaciente())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Mis Citas</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('paciente.perfil') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-user me-1"></i><span class="btn-text">Mi Perfil</span></a>
                <a href="{{ route('paciente.historial') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-clock-rotate-left me-1"></i><span class="btn-text">Mi Historial</span></a>
                <a href="{{ route('citas.create') }}" class="btn btn-primary neu-btn-sm"><i class="fa fa-calendar-plus me-1"></i><span class="btn-text">+ Nueva cita</span></a>
            </div>
        </div>
        @else
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Mis Citas</h5>
        </div>
        @endif
        <div class="card shadow-2 p-4">
            @if ($citas->isEmpty())
                <div class="d-flex flex-column align-items-center py-4"><i class="fa fa-calendar-xmark fa-2x text-muted opacity-50 mb-2"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No tienes citas registradas.</p></div>
            @else
                <div class="table-responsive">
                    <table class="table neu-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Fecha Cita</th>
                                @if ($user->esMedico())<th>Solicitada</th>@endif
                                <th>{{ $user->esMedico() ? 'Paciente' : 'Médico' }}</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($citas as $cita)
                                <tr>
                                    <td>{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                                    @if ($user->esMedico())<td style="font-size:0.8rem">{{ $cita->created_at->format('d/m/Y H:i') }}</td>@endif
                                    <td>{{ $user->esMedico() ? $cita->paciente->name : $cita->medico->name }}</td>
                                    <td class="text-muted">{{ Str::limit($cita->motivo, 40) }}</td>
                                    <td data-cita-id="{{ $cita->id }}">
                                        @switch($cita->estado)
                                            @case('pendiente') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-clock me-1"></i>Pendiente</span> @break
                                            @case('confirmada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-check me-1"></i>Confirmada</span> @break
                                            @case('en_espera') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-hourglass-half me-1"></i>En espera</span> @break
                                            @case('en_consulta') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-stethoscope me-1"></i>En consulta</span> @break
                                            @case('finalizada') <span id="estado-badge-{{ $cita->id }}" class="btn btn-primary btn-sm"><i class="fa fa-circle-check me-1"></i>Finalizada</span> @break
                                            @case('cancelada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-xmark me-1"></i>Cancelada</span> @break
                                            @case('no_asistio') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-user-slash me-1"></i>No asistió</span> @break
                                            @case('reprogramada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #9370db;color:#9370db;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-calendar me-1"></i>Reprogramada</span> @break
                                        @endswitch
                                    </td>
                                    <td data-cita-acciones="{{ $cita->id }}">
                                        @include('dashboard._acciones', ['cita' => $cita])
                                    </td>
                                    @if ($user->esMedico() && in_array($cita->estado, ['pendiente', 'confirmada']))
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="modal fade" id="reprogramarModal-{{ $cita->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0">
                                                        <h6 class="modal-title fw-bold">Reprogramar Cita</h6>
                                                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('citas.estado', $cita->id) }}" method="POST">
                                                        @csrf @method('PUT')
                                                        <div class="modal-body">
                                                            <input type="hidden" name="estado" value="reprogramada">
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small">Selecciona la nueva fecha y hora</label>
                                                                <input type="datetime-local" name="fecha_reprogramada" class="form-control js-flatpickr-simple" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-mdb-dismiss="modal"><i class="fa fa-xmark me-1"></i><span class="btn-text">Cancelar</span></button>
                                                            <button type="submit" class="btn btn-secondary btn-sm"><i class="fa fa-calendar-check me-1"></i><span class="btn-text">Guardar reprogramación</span></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <br><br>
        </div>
    @endif

@if ($user->esMedico() || $user->esPaciente())
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calEl = document.getElementById('role-calendar');
    if (!calEl) { console.error('role-calendar not found'); return; }

    var userRole = '{{ auth()->user()->role }}';
    var csrfToken = '{{ csrf_token() }}';

    var estadoLabels = {
        'pendiente': 'Pendiente',
        'confirmada': 'Confirmada',
        'en_espera': 'En espera',
        'en_consulta': 'En consulta',
        'finalizada': 'Finalizada',
        'cancelada': 'Cancelada',
        'no_asistio': 'No asistió',
        'reprogramada': 'Reprogramada'
    };

    var events = [
        @foreach ($citas as $cita)
        {
            id: '{{ $cita->id }}',
            title: '{{ $cita->estado }}',
            start: '{{ $cita->fecha_hora->format('Y-m-d') }}',
            allDay: true,
            updateUrl: '{{ route('citas.estado', ['cita' => $cita->id]) }}',
            @switch($cita->estado)
                @case('pendiente') color: '#1266f1', @break
                @case('confirmada') color: '#00b894', @break
                @case('en_espera') color: '#ffa500', @break
                @case('en_consulta') color: '#1e90ff', @break
                @case('finalizada') color: '#6c757d', @break
                @case('cancelada') color: '#ff4444', @break
                @case('no_asistio') color: '#dc143c', @break
                @case('reprogramada') color: '#9370db', @break
                @default color: '#1266f1',
            @endswitch
        },
        @endforeach
    ];

    function canDrag(estado) {
        if (userRole === 'medico') {
            return estado === 'pendiente' || estado === 'confirmada';
        }
        return false;
    }

    try {
        var calendar = new FullCalendar.Calendar(calEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            height: 260,
            contentHeight: 220,
            events: events,
            editable: true,
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            titleFormat: { year: 'numeric', month: 'long' },
            dayHeaderFormat: { weekday: 'short' },
            buttonText: {
                today: 'Hoy',
                month: 'Mes'
            },
            dayCellClassNames: function() { return ['fc-day-compact']; },
            eventDidMount: function(info) {
                info.el.setAttribute('title', estadoLabels[info.event.title] || info.event.title);
                if (!canDrag(info.event.title)) {
                    info.el.style.cursor = 'default';
                }
            },
            eventDrop: function(info) {
                var estado = info.event.title;
                if (!canDrag(estado)) { info.revert(); return; }

                var fechaLocal = new Date(info.event.start).toLocaleDateString('es-MX', {
                    year: 'numeric', month: 'long', day: 'numeric'
                });

                if (!confirm('Reprogramar esta cita para el ' + fechaLocal + '?')) {
                    info.revert();
                    return;
                }

                var formData = new URLSearchParams();
                formData.append('estado', 'reprogramada');
                formData.append('fecha_reprogramada', info.event.start.toISOString().slice(0, 16));
                formData.append('_token', csrfToken);
                formData.append('_method', 'PUT');

                fetch(info.event.extendedProps.updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData.toString()
                })
                .then(function(r) {
                    if (r.redirected) { window.location.href = r.url; return null; }
                    if (!r.ok) { return r.json().then(function(d) { throw new Error(d.error || 'Error'); }); }
                    return r.json();
                })
                .then(function(data) {
                    if (data && data.success) { location.reload(); }
                    else { info.revert(); }
                })
                .catch(function(err) {
                    info.revert();
                    alert(err.message || 'Error al reprogramar');
                });
            }
        });

        calendar.render();
    } catch(e) {
        calEl.innerHTML = '<div class="text-danger p-3">Error al cargar el calendario: ' + e.message + '</div>';
    }
});
</script>
@endif

</div>
@endsection
