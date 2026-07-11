@extends('layouts.app')

@section('title', 'Historial de Citas')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-2 p-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:64px;height:64px;background:#1266f1;color:#121212;font-size:1.5rem;font-weight:bold">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <div>
                        <h3 class="mb-1">Historial de Citas</h3>
                        <p class="mb-0 text-muted">Todas tus citas registradas en el sistema</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('medico.perfil') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i><span class="btn-text">Volver al perfil</span></a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-2 p-4 mb-4">
        @if ($citas->isEmpty())
            <div class="d-flex flex-column align-items-center py-5">
                <i class="fa fa-calendar fa-3x text-muted opacity-50 mb-3"></i>
                <p class="fw-bold text-muted mb-0" style="font-size:1.1rem">Sin citas registradas.</p>
            </div>
        @else
            <div class="table-responsive" style="overflow:visible">
                <table class="table neu-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($citas as $cita)
                        <tr>
                            <td>{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>{{ $cita->paciente->name }}</td>
                            <td class="text-muted">{{ Str::limit($cita->motivo, 40) }}</td>
                            <td data-cita-id="{{ $cita->id }}">
                                @switch($cita->estado)
                                    @case('pendiente') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid var(--yellow);color:var(--yellow);background:transparent;padding:0.5rem 0.75rem">Pendiente</span> @break
                                    @case('confirmada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem">Confirmada</span> @break
                                    @case('en_espera') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem">En espera</span> @break
                                    @case('en_consulta') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem">En consulta</span> @break
                                    @case('finalizada') <span id="estado-badge-{{ $cita->id }}" class="btn btn-primary btn-sm">Finalizada</span> @break
                                    @case('cancelada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem">Cancelada</span> @break
                                    @case('no_asistio') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem">No asistió</span> @break
                                    @case('reprogramada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #9370db;color:#9370db;background:transparent;padding:0.5rem 0.75rem">Reprogramada</span> @break
                                @endswitch
                            </td>
                            <td data-cita-acciones="{{ $cita->id }}">
                                @include('dashboard._acciones', ['cita' => $cita])
                            </td>
                            @if (auth()->user()->esMedico() && in_array($cita->estado, ['pendiente', 'confirmada']))
                            <td colspan="5" class="p-0 border-0">
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
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($citas instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">{{ $citas->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
