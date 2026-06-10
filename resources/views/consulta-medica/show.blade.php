@extends('layouts.app')

@section('title', 'Detalle de Consulta')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Detalle de Consulta Médica</h4>
        <div>
            <span class="text-muted small me-3">Paciente: {{ $cita->paciente->name }}</span>
            <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm">Volver</a>
        </div>
    </div>

    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Motivo y síntomas</h6>
        <div class="row g-3">
            @if ($consulta->motivo_consulta)
            <div class="col-12"><strong class="text-muted small">Motivo de consulta:</strong><br>{{ $consulta->motivo_consulta }}</div>
            @endif
            @if ($consulta->sintomas)
            <div class="col-md-8"><strong class="text-muted small">Síntomas:</strong><br>{{ $consulta->sintomas }}</div>
            @endif
            @if ($consulta->tiempo_evolucion)
            <div class="col-md-4"><strong class="text-muted small">Tiempo de evolución:</strong><br>{{ $consulta->tiempo_evolucion }}</div>
            @endif
        </div>
    </div>

    @if ($consulta->dolores->count())
    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Dolores</h6>
        <div class="table-responsive">
            <table class="table table-sm neu-table mb-0">
                <thead>
                    <tr>
                        <th>Ubicación</th>
                        <th>Intensidad</th>
                        <th>Duración</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consulta->dolores as $dolor)
                    <tr>
                        <td>{{ $dolor->ubicacion }}</td>
                        <td>{{ $dolor->intensidad }}</td>
                        <td>{{ $dolor->duracion }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br><br><br><br>
    </div>
    @endif

    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Signos vitales</h6>
        <div class="row g-3">
            @if ($consulta->presion_arterial)<div class="col-md-3"><strong class="text-muted small">Presión arterial:</strong><br>{{ $consulta->presion_arterial }} mmHg</div>@endif
            @if ($consulta->temperatura)<div class="col-md-3"><strong class="text-muted small">Temperatura:</strong><br>{{ $consulta->temperatura }} °C</div>@endif
            @if ($consulta->frecuencia_cardiaca)<div class="col-md-3"><strong class="text-muted small">Frec. cardíaca:</strong><br>{{ $consulta->frecuencia_cardiaca }} lpm</div>@endif
            @if ($consulta->frecuencia_respiratoria)<div class="col-md-3"><strong class="text-muted small">Frec. respiratoria:</strong><br>{{ $consulta->frecuencia_respiratoria }} rpm</div>@endif
            @if ($consulta->saturacion_oxigeno)<div class="col-md-3"><strong class="text-muted small">Saturación O₂:</strong><br>{{ $consulta->saturacion_oxigeno }} %</div>@endif
            @if ($consulta->peso)<div class="col-md-3"><strong class="text-muted small">Peso:</strong><br>{{ $consulta->peso }} kg</div>@endif
            @if ($consulta->estatura)<div class="col-md-3"><strong class="text-muted small">Estatura:</strong><br>{{ $consulta->estatura }} cm</div>@endif
            @if ($consulta->imc)<div class="col-md-3"><strong class="text-muted small">IMC:</strong><br>{{ $consulta->imc }}</div>@endif
        </div>
    </div>

    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Exploración y diagnóstico</h6>
        @if ($consulta->exploracion_fisica)<div class="mb-3"><strong class="text-muted small">Exploración física:</strong><br>{{ $consulta->exploracion_fisica }}</div>@endif
        @if ($consulta->observaciones)<div class="mb-3"><strong class="text-muted small">Observaciones del médico:</strong><br>{{ $consulta->observaciones }}</div>@endif
        <div class="row g-3">
            @if ($consulta->diagnostico_probable)<div class="col-md-6"><strong class="text-muted small">Diagnóstico probable:</strong><br>{{ $consulta->diagnostico_probable }}</div>@endif
            @if ($consulta->diagnostico_final)<div class="col-md-6"><strong class="text-muted small">Diagnóstico final:</strong><br>{{ $consulta->diagnostico_final }}</div>@endif
            @if ($consulta->codigo_cie10)<div class="col-md-4"><strong class="text-muted small">Código CIE-10:</strong><br>{{ $consulta->codigo_cie10 }}</div>@endif
        </div>
    </div>
</div>
@endsection
