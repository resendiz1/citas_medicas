@extends('layouts.app')

@section('title', 'Mi Historial Clínico')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Mi Historial Clínico</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i><span class="btn-text">Volver</span></a>
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
                    @case('finalizada') <span class="btn btn-primary btn-sm"><i class="fa fa-circle-check me-1"></i>Finalizada</span> @break
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

        @if ($consulta->motivo_consulta || $consulta->fecha_inicio_sintomas || $consulta->tiempo_evolucion || $consulta->forma_inicio || $consulta->evolucion || $consulta->descripcion_padecimiento)
        <div class="mb-3">
            <strong class="text-muted small">Motivo y padecimiento actual</strong>
            @if ($consulta->motivo_consulta)<div class="mt-1"><strong class="text-muted small">Motivo:</strong> {{ $consulta->motivo_consulta }}</div>@endif
            <div class="row g-2 mt-1">
                @if ($consulta->fecha_inicio_sintomas)<div class="col-12 col-md-3"><strong class="text-muted small">Inicio:</strong> {{ \Carbon\Carbon::parse($consulta->fecha_inicio_sintomas)->format('d/m/Y') }}</div>@endif
                @if ($consulta->tiempo_evolucion)<div class="col-12 col-md-3"><strong class="text-muted small">Evolución:</strong> {{ $consulta->tiempo_evolucion }}</div>@endif
                @if ($consulta->forma_inicio)<div class="col-12 col-md-3"><strong class="text-muted small">Forma inicio:</strong> {{ $consulta->forma_inicio === 'subito' ? 'Súbito' : 'Gradual' }}</div>@endif
                @if ($consulta->evolucion)<div class="col-12 col-md-3"><strong class="text-muted small">Evolución:</strong> {{ ucfirst($consulta->evolucion) }}</div>@endif
            </div>
            @if ($consulta->descripcion_padecimiento)<div class="mt-1"><strong class="text-muted small">Descripción:</strong> {{ $consulta->descripcion_padecimiento }}</div>@endif
        </div>
        @endif

        @if ($consulta->sintomasRegistrados->count())
        <div class="mb-3">
            <strong class="text-muted small">Síntomas registrados</strong>
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead><tr><th>Síntoma</th><th>Ubicación</th><th>Intensidad</th><th>Inicio</th><th>Duración</th><th>Frecuencia</th></tr></thead>
                    <tbody>
                        @foreach ($consulta->sintomasRegistrados as $s)
                        <tr><td>{{ $s->nombre }}</td><td>{{ $s->ubicacion ?? '—' }}</td><td>{{ $s->intensidad_categoria ? ucfirst($s->intensidad_categoria) : '—' }}</td><td>{{ $s->inicio ?? '—' }}</td><td>{{ $s->duracion ?? '—' }}</td><td>{{ $s->frecuencia ? ucfirst($s->frecuencia) : '—' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @elseif ($consulta->dolores->count())
        <div class="mb-3">
            <strong class="text-muted small">Dolores (previo)</strong>
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead><tr><th>Ubicación</th><th>Intensidad</th><th>Duración</th></tr></thead>
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
            <strong class="text-muted small">Signos vitales y antropometría</strong>
            <div class="row g-2 mt-1">
                @if ($consulta->presion_arterial)<div class="col-12 col-md-3"><strong class="text-muted small">Presión arterial:</strong> {{ $consulta->presion_arterial }} mmHg</div>@endif
                @if ($consulta->temperatura)<div class="col-12 col-md-3"><strong class="text-muted small">Temperatura:</strong> {{ $consulta->temperatura }} °C</div>@endif
                @if ($consulta->frecuencia_cardiaca)<div class="col-12 col-md-3"><strong class="text-muted small">Frec. cardíaca:</strong> {{ $consulta->frecuencia_cardiaca }} lpm</div>@endif
                @if ($consulta->frecuencia_respiratoria)<div class="col-12 col-md-3"><strong class="text-muted small">Frec. respiratoria:</strong> {{ $consulta->frecuencia_respiratoria }} rpm</div>@endif
                @if ($consulta->saturacion_oxigeno)<div class="col-12 col-md-3"><strong class="text-muted small">Saturación O₂:</strong> {{ $consulta->saturacion_oxigeno }} %</div>@endif
                @if ($consulta->peso)<div class="col-12 col-md-3"><strong class="text-muted small">Peso:</strong> {{ $consulta->peso }} kg</div>@endif
                @if ($consulta->estatura)<div class="col-12 col-md-3"><strong class="text-muted small">Estatura:</strong> {{ $consulta->estatura }} cm</div>@endif
                @if ($consulta->imc)<div class="col-12 col-md-3"><strong class="text-muted small">IMC:</strong> {{ $consulta->imc }} kg/m²</div>@endif
            </div>
        </div>
        @endif

        @if ($consulta->exploracion_estado_general || $consulta->exploracion_cabeza_cuello || $consulta->exploracion_respiratorio || $consulta->exploracion_cardiovascular || $consulta->exploracion_abdomen || $consulta->exploracion_extremidades || $consulta->exploracion_neurologico || $consulta->exploracion_hallazgos || $consulta->exploracion_sin_hallazgos)
        <div class="mb-3">
            <strong class="text-muted small">Exploración física</strong>
            @if ($consulta->exploracion_sin_hallazgos)<p class="text-muted fst-italic mt-1 mb-1">Sin hallazgos relevantes</p>@endif
            @if ($consulta->exploracion_estado_general)<div class="mt-1"><strong class="text-muted small">Estado general:</strong> {{ $consulta->exploracion_estado_general }}</div>@endif
            @if ($consulta->exploracion_cabeza_cuello)<div class="mt-1"><strong class="text-muted small">Cabeza y cuello:</strong> {{ $consulta->exploracion_cabeza_cuello }}</div>@endif
            @if ($consulta->exploracion_respiratorio)<div class="mt-1"><strong class="text-muted small">Respiratorio:</strong> {{ $consulta->exploracion_respiratorio }}</div>@endif
            @if ($consulta->exploracion_cardiovascular)<div class="mt-1"><strong class="text-muted small">Cardiovascular:</strong> {{ $consulta->exploracion_cardiovascular }}</div>@endif
            @if ($consulta->exploracion_abdomen)<div class="mt-1"><strong class="text-muted small">Abdomen:</strong> {{ $consulta->exploracion_abdomen }}</div>@endif
            @if ($consulta->exploracion_extremidades)<div class="mt-1"><strong class="text-muted small">Extremidades:</strong> {{ $consulta->exploracion_extremidades }}</div>@endif
            @if ($consulta->exploracion_neurologico)<div class="mt-1"><strong class="text-muted small">Neurológico:</strong> {{ $consulta->exploracion_neurologico }}</div>@endif
            @if ($consulta->exploracion_hallazgos)<div class="mt-1"><strong class="text-muted small">Hallazgos:</strong> {{ $consulta->exploracion_hallazgos }}</div>@endif
            @if ($consulta->observaciones)<div class="mt-1"><strong class="text-muted small">Observaciones:</strong> {{ $consulta->observaciones }}</div>@endif
        </div>
        @elseif ($consulta->exploracion_fisica || $consulta->observaciones)
        <div class="mb-3">
            <strong class="text-muted small">Exploración y observaciones (previo)</strong>
            @if ($consulta->exploracion_fisica)<div class="mt-1"><strong class="text-muted small">Exploración física:</strong><br>{{ $consulta->exploracion_fisica }}</div>@endif
            @if ($consulta->observaciones)<div class="mt-1"><strong class="text-muted small">Observaciones:</strong><br>{{ $consulta->observaciones }}</div>@endif
        </div>
        @endif

        @if ($consulta->diagnostico_probable || $consulta->diagnostico_diferencial || $consulta->diagnostico_final || $consulta->codigo_cie10 || $consulta->pronostico || $consulta->resumen_clinico)
        <div class="mb-3">
            <strong class="text-muted small">Evaluación</strong>
            @if ($consulta->resumen_clinico)<div class="mt-1"><strong class="text-muted small">Resumen clínico:</strong><br>{{ $consulta->resumen_clinico }}</div>@endif
            <div class="row g-2 mt-1">
                @if ($consulta->diagnostico_probable)<div class="col-12 col-md-6"><strong class="text-muted small">Diagnóstico probable:</strong><br>{{ $consulta->diagnostico_probable }}</div>@endif
                @if ($consulta->diagnostico_diferencial)<div class="col-12 col-md-6"><strong class="text-muted small">Diagnósticos diferenciales:</strong><br>{{ $consulta->diagnostico_diferencial }}</div>@endif
                @if ($consulta->diagnostico_final)<div class="col-12 col-md-6"><strong class="text-muted small">Diagnóstico definitivo:</strong><br>{{ $consulta->diagnostico_final }}</div>@endif
                @if ($consulta->codigo_cie10)<div class="col-12 col-md-3"><strong class="text-muted small">CIE-10:</strong><br>{{ $consulta->codigo_cie10 }}</div>@endif
                @if ($consulta->pronostico)<div class="col-12 col-md-3"><strong class="text-muted small">Pronóstico:</strong><br>{{ ucfirst(str_replace('_', ' ', $consulta->pronostico)) }}</div>@endif
            </div>
        </div>
        @endif

        @if ($consulta->plan_medicamentos || $consulta->plan_estudios || $consulta->plan_procedimientos || $consulta->plan_recomendaciones || $consulta->plan_signos_alarma || $consulta->plan_referencia || $consulta->plan_seguimiento_fecha || $consulta->plan_incapacidad)
        <div class="mb-3">
            <strong class="text-muted small">Plan y tratamiento</strong>
            <div class="row g-2 mt-1">
                @if ($consulta->plan_medicamentos)<div class="col-12 col-md-6"><strong class="text-muted small">Medicamentos:</strong><br>{{ $consulta->plan_medicamentos }}</div>@endif
                @if ($consulta->plan_estudios)<div class="col-12 col-md-6"><strong class="text-muted small">Estudios:</strong><br>{{ $consulta->plan_estudios }}</div>@endif
                @if ($consulta->plan_procedimientos)<div class="col-12 col-md-6"><strong class="text-muted small">Procedimientos:</strong><br>{{ $consulta->plan_procedimientos }}</div>@endif
                @if ($consulta->plan_recomendaciones)<div class="col-12 col-md-6"><strong class="text-muted small">Recomendaciones:</strong><br>{{ $consulta->plan_recomendaciones }}</div>@endif
                @if ($consulta->plan_signos_alarma)<div class="col-12 col-md-6"><strong class="text-muted small">Signos de alarma:</strong><br>{{ $consulta->plan_signos_alarma }}</div>@endif
                @if ($consulta->plan_referencia)<div class="col-12 col-md-6"><strong class="text-muted small">Referencia:</strong><br>{{ $consulta->plan_referencia }}</div>@endif
                @if ($consulta->plan_seguimiento_fecha)<div class="col-12 col-md-3"><strong class="text-muted small">Seguimiento:</strong><br>{{ \Carbon\Carbon::parse($consulta->plan_seguimiento_fecha)->format('d/m/Y') }}</div>@endif
                @if ($consulta->plan_incapacidad)<div class="col-12 col-md-3"><strong class="text-muted small">Incapacidad:</strong><br>{{ $consulta->plan_incapacidad }}</div>@endif
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
                <a href="{{ route('recetas.show', $receta->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-prescription me-1"></i><span class="btn-text">Abrir</span></a>
            </div>
            @if ($receta->diagnostico)<p class="mb-1 small"><strong>Diagnóstico:</strong> {{ $receta->diagnostico }}</p>@endif
            @if ($receta->indicaciones_generales)<p class="mb-1 small"><strong>Indicaciones:</strong> {{ $receta->indicaciones_generales }}</p>@endif
            @if ($receta->medicamentos->count())
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr><th>Medicamento</th><th>Genérico</th><th>Comercial</th><th>Presentación</th><th>Forma farm.</th><th>Dosis</th><th>Vía</th><th>Frecuencia</th><th>Duración</th><th>Cant.</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($receta->medicamentos as $med)
                        <tr><td>{{ $med->medicamento }}</td><td>{{ $med->nombre_generico ?? '—' }}</td><td>{{ $med->nombre_comercial ?? '—' }}</td><td>{{ $med->presentacion ?? '—' }}</td><td>{{ $med->forma_farmaceutica ?? '—' }}</td><td>{{ $med->dosis ?? '—' }}</td><td>{{ $med->via_administracion ?? '—' }}</td><td>{{ $med->frecuencia ?? '—' }}</td><td>{{ $med->duracion ?? '—' }}</td><td>{{ $med->cantidad_total ?? '—' }}</td></tr>
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
