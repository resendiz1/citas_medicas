@extends('layouts.app')

@section('title', 'Bloqueos - ' . $medico->name)

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:#1266f1">Bloqueos de {{ $medico->name }}</h4>
        <a href="{{ $user->esAdmin() ? route('admin.medicos') : route('dashboard') }}" class="btn btn-outline-secondary btn-sm">&larr; Volver</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card shadow-2 p-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Nuevo bloqueo</h5>
                <form action="{{ $user->esAdmin() ? route('admin.medicos.bloqueos.store', $medico->id) : route('medico.bloqueos.store') }}" method="POST">
                    @csrf
                    @if ($user->esAdmin())
                        <input type="hidden" name="medico_id" value="{{ $medico->id }}">
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" name="fecha_fin" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary neu-btn-sm"><i class="fa fa-floppy-disk me-1"></i>Guardar</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-2 p-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Bloqueos registrados</h5>
                @if ($bloqueos->isEmpty())
                    <div class="d-flex flex-column align-items-center py-4"><i class="fa fa-ban fa-2x text-muted opacity-50 mb-2"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">Sin bloqueos registrados.</p></div>
                @else
                    <div class="table-responsive">
                        <table class="table neu-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Motivo</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bloqueos as $b)
                                    <tr>
                                        <td style="color:var(--text-emphasis);font-weight:500">{{ \Carbon\Carbon::parse($b->fecha_inicio)->format('d/m/Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($b->fecha_fin)->format('d/m/Y') }}</td>
                                        <td class="text-muted">{{ $b->motivo ?? '—' }}</td>
                                        <td>
                                            <form action="{{ $user->esAdmin() ? route('admin.medicos.bloqueos.destroy', [$medico->id, $b->id]) : route('medico.bloqueos.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este bloqueo?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm"><i class="fa fa-trash-can me-1"></i>Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <br><br>
            </div>
        </div>
    </div>
</div>
@endsection
