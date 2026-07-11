@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-2 p-4 d-flex align-items-center gap-3">
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
        </div>
    </div>

    @if ($user->esAdmin() || $user->esRecepcionista())
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
                <a href="{{ $user->esRecepcionista() ? '#citas-section' : route('admin.citas') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-calendar-check fa-xl"></i></div>
                        <h5>Citas</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalCitas }}</p>
                    </div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ $user->esRecepcionista() ? '#citas-section' : route('admin.citas', ['estado' => 'pendiente']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-clock fa-xl"></i></div>
                        <h5>Pendientes</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $citasPendientes }}</p>
                    </div>
                </a>
            </div>
            @if ($user->esRecepcionista())
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-2 p-4 text-center">
                    <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa fa-calendar-day fa-xl"></i></div>
                    <h5>Hoy</h5>
                    <p class="display-6 mb-0 fw-bold">{{ $citasHoy }}</p>
                </div>
            </div>
            @endif
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
        @if ($user->esRecepcionista())
        <div class="card shadow-2 p-4 mt-4" id="citas-section">
            <h5 class="mb-3 fw-bold" style="color:#1266f1">Todas las Citas</h5>
                <div class="table-responsive">
                    <table class="table neu-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($citas as $cita)
                            <tr>
                                <td>{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                                <td>{{ $cita->paciente->name }}</td>
                                <td>{{ $cita->medico->name }}</td>
                                <td class="text-muted">{{ $cita->medico->medicoPerfil->tipoMedico->nombre_tipo_medico ?? '—' }}</td>
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
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-5"><div class="d-flex flex-column align-items-center gap-2"><i class="fa fa-calendar-xmark fa-2x text-muted opacity-50"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No hay citas.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($citas instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-3 d-flex justify-content-center">
                    {{ $citas->links() }}
                </div>
            @endif
            <br><br>
        </div>
        @endif
    @else
        @if ($user->esPaciente())
        <div class="mb-4">
            <h5 class="fw-bold mb-3" style="color:var(--text-primary)">Médicos Disponibles</h5>
            <div class="row g-3">
                @forelse ($medicos as $medico)
                <div class="col-12 col-md-4 col-lg-3">
                    <a href="{{ route('paciente.medicos.show', $medico->id) }}" class="text-decoration-none">
                        <div class="card shadow-2 p-3 text-center h-100" style="border-radius:12px;cursor:pointer;">
                            <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center"
                                 style="width:56px;height:56px;background:#1266f1;color:#121212;font-size:1.4rem;font-weight:bold;overflow:hidden">
                                @if ($medico->foto_url)
                                    <img src="{{ Storage::url($medico->foto_url) }}" alt="Foto"
                                         style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                                         onclick="window.open('{{ Storage::url($medico->foto_url) }}','_blank')">
                                @else
                                    {{ strtoupper(substr($medico->name, 0, 1)) }}
                                @endif
                            </div>
                            <h6 class="mb-1" style="font-size:0.85rem;color:var(--text-emphasis)">{{ $medico->name }}</h6>
                            <small class="text-muted">{{ optional(optional($medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? 'General' }}</small>
                        </div>
                    </a>
                </div>
                @empty
                <div class="col-12"><div class="d-flex flex-column align-items-center py-4"><i class="fa fa-user-doctor fa-2x text-muted opacity-50 mb-2"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No hay médicos registrados.</p></div></div>
                @endforelse
            </div>
        </div>
        @endif

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

</div>
@endsection
