@extends('layouts.app')

@section('title', 'Todas las Citas')

@section('content')
<div class="container">
    <h4 class="mb-4 fw-bold">Todas las Citas</h4>

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto flex-grow-1">
                <input type="text" name="search" class="neu-input form-control" placeholder="Buscar por paciente o médico..." value="{{ request('search') }}">
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
                <button type="submit" class="neu-btn neu-btn-sm">Buscar</button>
                @if (request('search') || request('estado'))
                    <a href="{{ route('admin.citas') }}" class="neu-btn neu-btn-sm" style="background:#ff4444;color:#fff">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="neu-card p-4">
        <div class="table-responsive">
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
                                    @case('pendiente') <span class="neu-badge" style="background:var(--yellow);color:#121212">Pendiente</span> @break
                                    @case('confirmada') <span class="neu-badge" style="background:#00b894;color:#fff">Confirmada</span> @break
                                    @case('en_espera') <span class="neu-badge" style="background:#ffa500;color:#121212">En espera</span> @break
                                    @case('en_consulta') <span class="neu-badge" style="background:#1e90ff;color:#fff">En consulta</span> @break
                                    @case('finalizada') <span class="neu-badge" style="background:#555;color:#fff">Finalizada</span> @break
                                    @case('cancelada') <span class="neu-badge" style="background:#ff4444;color:#fff">Cancelada</span> @break
                                    @case('no_asistio') <span class="neu-badge" style="background:#dc143c;color:#fff">No asistió</span> @break
                                    @case('reprogramada') <span class="neu-badge" style="background:#9370db;color:#fff">Reprogramada</span> @break
                                @endswitch
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if ($cita->estado === 'pendiente')
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="confirmada">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Confirmar</button>
                                        </form>
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="cancelada">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
                                        </form>
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="no_asistio">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#dc143c;color:#fff">No asistió</button>
                                        </form>
                                        <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#9370db;color:#fff" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
                                            Reprogramar
                                        </button>
                                    @elseif ($cita->estado === 'confirmada')
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="en_espera">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ffa500;color:#121212">En espera</button>
                                        </form>
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="cancelada">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
                                        </form>
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="no_asistio">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#dc143c;color:#fff">No asistió</button>
                                        </form>
                                        <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#9370db;color:#fff" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
                                            Reprogramar
                                        </button>
                                    @elseif ($cita->estado === 'en_espera')
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="en_consulta">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">En consulta</button>
                                        </form>
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="cancelada">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
                                        </form>
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="no_asistio">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#dc143c;color:#fff">No asistió</button>
                                        </form>
                                    @elseif ($cita->estado === 'en_consulta')
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="finalizada">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#555;color:#fff">Finalizar</button>
                                        </form>
                                    @elseif ($cita->estado === 'reprogramada')
                                        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="estado" value="confirmada">
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Confirmar</button>
                                        </form>
                                    @else
                                        <span class="text-muted" style="font-size:0.75rem">—</span>
                                    @endif
                                    <form action="{{ route('admin.citas.destroy', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar permanentemente esta cita? También se eliminarán recetas, consultas e historial asociados.')">
                                        @csrf @method('DELETE')
                                        <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                            @if (in_array($cita->estado, ['pendiente', 'confirmada']))
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
                                                        <input type="datetime-local" name="fecha_reprogramada" class="neu-input form-control js-flatpickr-simple" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="neu-btn neu-btn-sm" data-mdb-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="neu-btn neu-btn-sm" style="background:#9370db;color:#fff">Guardar reprogramación</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                            <form action="{{ route('citas.estado', $cita->id) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <input type="hidden" name="estado" value="reprogramada">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small">Selecciona la nueva fecha y hora</label>
                                                        <input type="datetime-local" name="fecha_reprogramada" class="neu-input form-control js-flatpickr-simple" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="neu-btn neu-btn-sm" data-mdb-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="neu-btn neu-btn-sm" style="background:#9370db;color:#fff">Guardar reprogramación</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay citas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($citas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {{ $citas->appends(request()->query())->links() }}
            </div>
        @endif
        <br><br><br><br>
    </div>
</div>
@endsection
