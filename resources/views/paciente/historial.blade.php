@extends('layouts.app')

@section('title', 'Mi Historial Clínico')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Mi Historial Clínico</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i>Volver</a>
    </div>

    @forelse ($citas as $cita)
    <div class="card shadow-2 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="fw-bold mb-1" style="color:#1266f1">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</h6>
                <small class="text-muted">{{ $cita->medico->name }} — {{ optional(optional($cita->medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? 'General' }}</small>
            </div>
            <div>
                @switch($cita->estado)
                    @case('finalizada') <span class="badge" style="border:2px solid #555;color:#555;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-check me-1"></i>Finalizada</span> @break
                    @case('en_consulta') <span class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-stethoscope me-1"></i>En consulta</span> @break
                    @case('cancelada') <span class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-xmark me-1"></i>Cancelada</span> @break
                    @case('no_asistio') <span class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-user-slash me-1"></i>No asistió</span> @break
                    @default <span class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-clock me-1"></i>{{ ucfirst($cita->estado) }}</span>
                @endswitch
            </div>
        </div>

        <p class="mb-0 text-muted"><strong>Motivo:</strong> {{ $cita->motivo }}</p>

        @if ($cita->consultaMedica)
        @php $consulta = $cita->consultaMedica; @endphp
        <hr class="my-3">

        @if ($consulta->motivo_consulta || $consulta->sintomas || $consulta->tiempo_evolucion)
        <div class="mb-3">
            <strong class="text-muted small">Motivo y síntomas</strong>
            <div class="row g-2 mt-1">
                @if ($consulta->motivo_consulta)<div class="col-12"><strong class="text-muted small">Motivo:</strong> {{ $consulta->motivo_consulta }}</div>@endif
                @if ($consulta->sintomas)<div class="col-12 col-md-8"><strong class="text-muted small">Síntomas:</strong> {{ $consulta->sintomas }}</div>@endif
                @if ($consulta->tiempo_evolucion)<div class="col-12 col-md-4"><strong class="text-muted small">Tiempo evolución:</strong> {{ $consulta->tiempo_evolucion }}</div>@endif
            </div>
        </div>
        @endif

        @if ($consulta->dolores->count())
        <div class="mb-3">
            <strong class="text-muted small">Dolores</strong>
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr><th>Ubicación</th><th>Intensidad</th><th>Duración</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($consulta->dolores as $dolor)
                        <tr><td>{{ $dolor->ubicacion }}</td><td>{{ $dolor->intensidad }}</td><td>{{ $dolor->duracion }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if ($consulta->presion_arterial || $consulta->temperatura || $consulta->frecuencia_cardiaca || $consulta->frecuencia_respiratoria || $consulta->saturacion_oxigeno || $consulta->peso || $consulta->estatura || $consulta->imc)
        <div class="mb-3">
            <strong class="text-muted small">Signos vitales</strong>
            <div class="row g-2 mt-1">
                @if ($consulta->presion_arterial)<div class="col-12 col-md-3"><strong class="text-muted small">Presión arterial:</strong> {{ $consulta->presion_arterial }} mmHg</div>@endif
                @if ($consulta->temperatura)<div class="col-12 col-md-3"><strong class="text-muted small">Temperatura:</strong> {{ $consulta->temperatura }} °C</div>@endif
                @if ($consulta->frecuencia_cardiaca)<div class="col-12 col-md-3"><strong class="text-muted small">Frec. cardíaca:</strong> {{ $consulta->frecuencia_cardiaca }} lpm</div>@endif
                @if ($consulta->frecuencia_respiratoria)<div class="col-12 col-md-3"><strong class="text-muted small">Frec. respiratoria:</strong> {{ $consulta->frecuencia_respiratoria }} rpm</div>@endif
                @if ($consulta->saturacion_oxigeno)<div class="col-12 col-md-3"><strong class="text-muted small">Saturación O₂:</strong> {{ $consulta->saturacion_oxigeno }} %</div>@endif
                @if ($consulta->peso)<div class="col-12 col-md-3"><strong class="text-muted small">Peso:</strong> {{ $consulta->peso }} kg</div>@endif
                @if ($consulta->estatura)<div class="col-12 col-md-3"><strong class="text-muted small">Estatura:</strong> {{ $consulta->estatura }} cm</div>@endif
                @if ($consulta->imc)<div class="col-12 col-md-3"><strong class="text-muted small">IMC:</strong> {{ $consulta->imc }}</div>@endif
            </div>
        </div>
        @endif

        @if ($consulta->exploracion_fisica || $consulta->observaciones || $consulta->diagnostico_probable || $consulta->diagnostico_final || $consulta->codigo_cie10)
        <div>
            <strong class="text-muted small">Exploración y diagnóstico</strong>
            <div class="mt-1">
                @if ($consulta->exploracion_fisica)<div class="mb-2"><strong class="text-muted small">Exploración física:</strong><br>{{ $consulta->exploracion_fisica }}</div>@endif
                @if ($consulta->observaciones)<div class="mb-2"><strong class="text-muted small">Observaciones:</strong><br>{{ $consulta->observaciones }}</div>@endif
                <div class="row g-2">
                    @if ($consulta->diagnostico_probable)<div class="col-12 col-md-6"><strong class="text-muted small">Diagnóstico probable:</strong><br>{{ $consulta->diagnostico_probable }}</div>@endif
                    @if ($consulta->diagnostico_final)<div class="col-12 col-md-6"><strong class="text-muted small">Diagnóstico final:</strong><br>{{ $consulta->diagnostico_final }}</div>@endif
                    @if ($consulta->codigo_cie10)<div class="col-12 col-md-4"><strong class="text-muted small">Código CIE-10:</strong><br>{{ $consulta->codigo_cie10 }}</div>@endif
                </div>
            </div>
        </div>
        @endif
        @endif

        @if ($cita->recetas->count())
        <hr class="my-3">
        <strong class="text-muted small">Recetas</strong>
        @foreach ($cita->recetas as $receta)
        <div class="mt-2 p-3" style="background:rgba(0,0,0,0.02);border-radius:8px">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold" style="font-size:0.85rem">Receta del {{ $receta->fecha_emision->format('d/m/Y') }}</span>
                <a href="{{ route('recetas.show', $receta->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-prescription me-1"></i>Abrir</a>
            </div>
            @if ($receta->diagnostico)<p class="mb-1 small"><strong>Diagnóstico:</strong> {{ $receta->diagnostico }}</p>@endif
            @if ($receta->indicaciones_generales)<p class="mb-1 small"><strong>Indicaciones:</strong> {{ $receta->indicaciones_generales }}</p>@endif
            @if ($receta->medicamentos->count())
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr><th>Medicamento</th><th>Dosis</th><th>Frecuencia</th><th>Duración</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($receta->medicamentos as $med)
                        <tr><td>{{ $med->medicamento }}</td><td>{{ $med->dosis ?? '—' }}</td><td>{{ $med->frecuencia ?? '—' }}</td><td>{{ $med->duracion ?? '—' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endforeach
        @endif
    </div>
    @empty
    <div class="card shadow-2 p-4">
        <div class="d-flex flex-column align-items-center py-4">
            <i class="fa fa-calendar-xmark fa-2x text-muted opacity-50 mb-2"></i>
            <p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No tienes citas registradas.</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
