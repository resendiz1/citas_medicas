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
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.pacientes') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa-solid fa-users fa-xl"></i></div>
                        <h5>Pacientes</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalPacientes }}</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('admin.medicos') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa-solid fa-user-doctor fa-xl"></i></div>
                        <h5>Médicos</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalMedicos }}</p>
                    </div>
                </a>
            </div>
            @endif
            <div class="col-md-6 col-lg-3">
                <a href="{{ $user->esRecepcionista() ? '#citas-section' : route('admin.citas') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa-solid fa-calendar-check fa-xl"></i></div>
                        <h5>Citas</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $totalCitas }}</p>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a href="{{ $user->esRecepcionista() ? '#citas-section' : route('admin.citas', ['estado' => 'pendiente']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-2 p-4 text-center">
                        <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa-solid fa-clock fa-xl"></i></div>
                        <h5>Pendientes</h5>
                        <p class="display-6 mb-0 fw-bold">{{ $citasPendientes }}</p>
                    </div>
                </a>
            </div>
            @if ($user->esRecepcionista())
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-2 p-4 text-center">
                    <div class="stat-icon mx-auto mb-3" style="color:#1266f1"><i class="fa-solid fa-calendar-day fa-xl"></i></div>
                    <h5>Hoy</h5>
                    <p class="display-6 mb-0 fw-bold">{{ $citasHoy }}</p>
                </div>
            </div>
            @endif
        </div>
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
                                        @case('pendiente') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-clock me-1"></i>Pendiente</span> @break
                                        @case('confirmada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-circle-check me-1"></i>Confirmada</span> @break
                                        @case('en_espera') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-hourglass-half me-1"></i>En espera</span> @break
                                        @case('en_consulta') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem"><i class="fa-solid fa-stethoscope me-1"></i>En consulta</span> @break
                                        @case('finalizada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #555;color:#555;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-circle-check me-1"></i>Finalizada</span> @break
                                        @case('cancelada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-circle-xmark me-1"></i>Cancelada</span> @break
                                        @case('no_asistio') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-user-slash me-1"></i>No asistió</span> @break
                                        @case('reprogramada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #9370db;color:#9370db;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-calendar me-1"></i>Reprogramada</span> @break
                                    @endswitch
                                </td>
                                <td data-cita-acciones="{{ $cita->id }}">
                                    @include('dashboard._acciones', ['cita' => $cita])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No hay citas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($citas instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-3 d-flex justify-content-center">
                    {{ $citas->links() }}
                </div>
            @endif
            <br><br><br><br>
        </div>
        @endif
    @else
        @if ($user->esPaciente())
        <div class="mb-4">
            <h5 class="fw-bold mb-3" style="color:var(--text-primary)">Médicos Disponibles</h5>
            <div class="row g-3">
                @forelse ($medicos as $medico)
                <div class="col-md-4 col-lg-3">
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
                <div class="col-12"><p class="text-muted mb-0">No hay médicos registrados.</p></div>
                @endforelse
            </div>
        </div>
        @endif

        @if ($user->esMedico())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Mis Citas</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('medico.perfil') }}" class="btn btn-outline-secondary btn-sm" style="background:#1266f1;color:#121212"><i class="fa-regular fa-user me-1"></i>Mi Perfil</a>
            </div>
        </div>
        @elseif ($user->esPaciente())
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Mis Citas</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('paciente.perfil') }}" class="btn btn-outline-secondary btn-sm" style="background:#1266f1;color:#121212"><i class="fa-regular fa-user me-1"></i>Mi Perfil</a>
                <a href="{{ route('citas.create') }}" class="btn btn-primary neu-btn-sm"><i class="fa-regular fa-calendar-plus me-1"></i>+ Nueva cita</a>
            </div>
        </div>
        @else
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold" style="color:var(--text-primary)">Mis Citas</h5>
        </div>
        @endif
        <div class="card shadow-2 p-4">
            @if ($citas->isEmpty())
                <p class="text-muted mb-0">No tienes citas registradas.</p>
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
                                            @case('pendiente') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-clock me-1"></i>Pendiente</span> @break
                                            @case('confirmada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-circle-check me-1"></i>Confirmada</span> @break
                                            @case('en_espera') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-hourglass-half me-1"></i>En espera</span> @break
                                            @case('en_consulta') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem"><i class="fa-solid fa-stethoscope me-1"></i>En consulta</span> @break
                                            @case('finalizada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #555;color:#555;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-circle-check me-1"></i>Finalizada</span> @break
                                            @case('cancelada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-circle-xmark me-1"></i>Cancelada</span> @break
                                            @case('no_asistio') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-user-slash me-1"></i>No asistió</span> @break
                                            @case('reprogramada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #9370db;color:#9370db;background:transparent;padding:0.5rem 0.75rem"><i class="fa-regular fa-calendar me-1"></i>Reprogramada</span> @break
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
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-mdb-dismiss="modal"><i class="fa-regular fa-xmark me-1"></i>Cancelar</button>
                                                            <button type="submit" class="btn btn-outline-secondary btn-sm" style="background:#9370db;color:#fff"><i class="fa-regular fa-calendar-check me-1"></i>Guardar reprogramación</button>
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
            <br><br><br><br>
        </div>
    @endif

</div>
@endsection
