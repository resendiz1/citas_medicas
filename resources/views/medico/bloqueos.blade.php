@extends('layouts.app')

@section('title', 'Bloqueos - ' . $medico->name)

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Bloqueos de {{ $medico->name }}</h4>
        <a href="{{ $user->esAdmin() ? route('admin.medicos') : route('dashboard') }}" class="neu-btn neu-btn-sm" style="color:var(--yellow)">&larr; Volver</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Nuevo bloqueo</h5>
                <form action="{{ $user->esAdmin() ? route('admin.medicos.bloqueos.store', $medico->id) : route('medico.bloqueos.store') }}" method="POST">
                    @csrf
                    @if ($user->esAdmin())
                        <input type="hidden" name="medico_id" value="{{ $medico->id }}">
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" name="fecha_inicio" class="neu-input form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" name="fecha_fin" class="neu-input form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <textarea name="motivo" class="neu-input form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <button type="submit" class="neu-btn neu-btn-primary neu-btn-sm">Guardar</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Bloqueos registrados</h5>
                @if ($bloqueos->isEmpty())
                    <p class="text-muted mb-0">Sin bloqueos registrados.</p>
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
                                                <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <br><br><br><br>
            </div>
        </div>
    </div>
</div>
@endsection
