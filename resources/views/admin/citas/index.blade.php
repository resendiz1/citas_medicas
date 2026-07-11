@extends('layouts.app')

@section('title', 'Todas las Citas')

@section('content')
<div class="container">
    <h4 class="mb-4 fw-bold">Todas las Citas</h4>

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto flex-grow-1">
                <input type="text" name="search" class="form-control" placeholder="Buscar por paciente o médico..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <select name="estado" class="neu-select form-select" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmada" {{ request('estado') === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                    <option value="en_espera" {{ request('estado') === 'en_espera' ? 'selected' : '' }}>En espera</option>
                    <option value="en_consulta" {{ request('estado') === 'en_consulta' ? 'selected' : '' }}>En consulta</option>
                    <option value="finalizada" {{ request('estado') === 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                    <option value="cancelada" {{ request('estado') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                    <option value="no_asistio" {{ request('estado') === 'no_asistio' ? 'selected' : '' }}>No asistió</option>
                    <option value="reprogramada" {{ request('estado') === 'reprogramada' ? 'selected' : '' }}>Reprogramada</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa fa-search me-1"></i>Buscar</button>
                @if (request('search') || request('estado'))
                    <a href="{{ route('admin.citas') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-rotate-left me-1"></i>Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card shadow-2 p-4">
        <div class="table-responsive" style="overflow:visible">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Motivo</th>
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
                            <td class="text-muted">{{ Str::limit($cita->motivo, 40) }}</td>
                            <td>
                                @switch($cita->estado)
                                    @case('pendiente') <span class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-clock me-1"></i>Pendiente</span> @break
                                    @case('confirmada') <span class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-check me-1"></i>Confirmada</span> @break
                                    @case('en_espera') <span class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-hourglass-half me-1"></i>En espera</span> @break
                                    @case('en_consulta') <span class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-stethoscope me-1"></i>En consulta</span> @break
                                    @case('finalizada') <span class="btn btn-primary btn-sm"><i class="fa fa-circle-check me-1"></i>Finalizada</span> @break
                                    @case('cancelada') <span class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-xmark me-1"></i>Cancelada</span> @break
                                    @case('no_asistio') <span class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-user-slash me-1"></i>No asistió</span> @break
                                    @case('reprogramada') <span class="badge" style="border:2px solid #9370db;color:#9370db;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-calendar me-1"></i>Reprogramada</span> @break
                                @endswitch
                            </td>
                            <td>
                                <div data-drop-wrap="{{ $cita->id }}">
                                    <button onclick="toggleDrop({{ $cita->id }})" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" style="border:none;background:transparent;box-shadow:none">
                                        <i class="fa fa-ellipsis-vertical"></i>
                                    </button>
                                    <div data-drop-menu="{{ $cita->id }}" class="shadow-2" style="display:none;position:fixed;min-width:190px;font-size:0.85rem;z-index:99999;background:#fff;border:1px solid rgba(0,0,0,.15);border-radius:0.375rem;padding:0.5rem 0">
                                    <ul style="list-style:none;padding:0;margin:0">
                                        @if ($cita->estado === 'pendiente')
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="confirmada">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-check-circle fa-fw me-1" style="color:#00b894"></i> Confirmar</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Cancelar esta cita?')">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="cancelada">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-circle-xmark fa-fw me-1" style="color:#ff4444"></i> Cancelar</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Marcar como no asistió?')">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="no_asistio">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-user-slash fa-fw me-1" style="color:#dc143c"></i> No asistió</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
                                                    <i class="fa fa-calendar fa-fw me-1" style="color:#9370db"></i> Reprogramar
                                                </button>
                                            </li>
                                        @elseif ($cita->estado === 'confirmada')
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="en_espera">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-clock fa-fw me-1" style="color:#ffa500"></i> En espera</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Cancelar esta cita?')">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="cancelada">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-circle-xmark fa-fw me-1" style="color:#ff4444"></i> Cancelar</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Marcar como no asistió?')">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="no_asistio">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-user-slash fa-fw me-1" style="color:#dc143c"></i> No asistió</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
                                                    <i class="fa fa-calendar fa-fw me-1" style="color:#9370db"></i> Reprogramar
                                                </button>
                                            </li>
                                        @elseif ($cita->estado === 'en_espera')
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="en_consulta">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-stethoscope fa-fw me-1" style="color:#1e90ff"></i> En consulta</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Cancelar esta cita?')">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="cancelada">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-circle-xmark fa-fw me-1" style="color:#ff4444"></i> Cancelar</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Marcar como no asistió?')">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="no_asistio">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-user-slash fa-fw me-1" style="color:#dc143c"></i> No asistió</button>
                                                </form>
                                            </li>
                                        @elseif ($cita->estado === 'en_consulta')
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="finalizada">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-check-double fa-fw me-1" style="color:#555"></i> Finalizar</button>
                                                </form>
                                            </li>
                                        @elseif ($cita->estado === 'reprogramada')
                                            <li>
                                                <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="estado" value="confirmada">
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-check-circle fa-fw me-1" style="color:#00b894"></i> Confirmar reprogramación</button>
                                                </form>
                                            </li>
                                        @else
                                            <li><span class="dropdown-item-text text-muted" style="font-size:0.75rem">Sin acciones</span></li>
                                        @endif
                                        <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
                                        <li>
                                            <form action="{{ route('admin.citas.destroy', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Eliminar permanentemente esta cita? También se eliminarán recetas, consultas e historial asociados.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item"><i class="fa fa-trash-can fa-fw me-1" style="color:#ff4444"></i> Eliminar</button>
                                            </form>
                                        </li>
                                    </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @if (in_array($cita->estado, ['pendiente', 'confirmada']))
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
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-mdb-dismiss="modal"><i class="fa fa-xmark me-1"></i>Cancelar</button>
                                            <button type="submit" class="btn btn-secondary btn-sm"><i class="fa fa-calendar-check me-1"></i>Guardar reprogramación</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr><td colspan="7" class="text-center py-5"><div class="d-flex flex-column align-items-center gap-2"><i class="fa fa-calendar-xmark fa-2x text-muted opacity-50"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No hay citas registradas.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($citas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {{ $citas->appends(request()->query())->links() }}
            </div>
        @endif
        <br><br>
    </div>
</div>
@endsection


