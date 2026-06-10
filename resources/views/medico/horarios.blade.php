@extends('layouts.app')

@section('title', 'Horarios - ' . $medico->name)

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Horarios de {{ $medico->name }}</h4>
        <a href="{{ $user->esAdmin() ? route('admin.medicos') : route('dashboard') }}" class="neu-btn neu-btn-sm" style="color:var(--yellow)">&larr; Volver</a>
    </div>

    <div class="neu-card p-4 mb-4">
        <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Intervalo entre citas</h5>
        <form action="{{ $user->esAdmin() ? route('admin.medicos.horarios.intervalo', $medico->id) : route('medico.horarios.intervalo') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            @if ($user->esAdmin())
                <input type="hidden" name="medico_id" value="{{ $medico->id }}">
            @endif
            <div class="col-auto">
                <label class="form-label">Minutos por cita</label>
                <select name="intervalo_minutos" class="neu-select form-select" onchange="this.form.submit()">
                    @foreach ([15, 20, 30, 45, 60, 90, 120] as $mins)
                        <option value="{{ $mins }}" {{ ($intervaloMinutos ?? 30) == $mins ? 'selected' : '' }}>{{ $mins }} min</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="neu-btn neu-btn-sm">Guardar</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Agregar horario</h5>
                <form action="{{ $user->esAdmin() ? route('admin.medicos.horarios.store', $medico->id) : route('medico.horarios.store') }}" method="POST">
                    @csrf
                    @if ($user->esAdmin())
                        <input type="hidden" name="medico_id" value="{{ $medico->id }}">
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Día</label>
                        <select name="dia_semana" class="neu-select form-select" required>
                            @foreach ($dias as $i => $d)
                                <option value="{{ $i }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Inicio</label>
                            <input type="time" name="hora_inicio" class="neu-input form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fin</label>
                            <input type="time" name="hora_fin" class="neu-input form-control" required>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="activo" class="form-check-input" id="activo" value="1" checked>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                    <button type="submit" class="neu-btn neu-btn-primary neu-btn-sm">Guardar</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Horarios registrados</h5>
                @if ($horarios->isEmpty())
                    <p class="text-muted mb-0">Sin horarios registrados.</p>
                @else
                    <div class="table-responsive">
                        <table class="table neu-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Activo</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($horarios as $h)
                                    <tr>
                                        <td style="color:var(--text-emphasis);font-weight:500">{{ $dias[$h->dia_semana] }}</td>
                                        <td>{{ substr($h->hora_inicio, 0, 5) }}</td>
                                        <td>{{ substr($h->hora_fin, 0, 5) }}</td>
                                        <td>
                                            @if ($h->activo)
                                                <span class="neu-badge" style="background:#00b894;color:#fff">Sí</span>
                                            @else
                                                <span class="neu-badge" style="background:#ff4444;color:#fff">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ $user->esAdmin() ? route('admin.medicos.horarios.destroy', [$medico->id, $h->id]) : route('medico.horarios.destroy', $h->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este horario?')">
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
