@extends('layouts.app')

@section('title', 'Detalle de Consulta')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Detalle de Consulta Médica</h4>
        <div>
            <span class="text-muted small me-3">Paciente: {{ $cita->paciente->name }}</span>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i><span class="btn-text">Volver</span></a>
        </div>
    </div>

    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Datos de la consulta</h6>
        <div class="row g-3">
            <div class="col-12 col-md-4"><strong class="text-muted small">Paciente:</strong><br>{{ $cita->paciente->name }}</div>
            <div class="col-12 col-md-4"><strong class="text-muted small">Médico:</strong><br>{{ $cita->medico->name }}</div>
            <div class="col-12 col-md-4"><strong class="text-muted small">Fecha y hora:</strong><br>{{ $cita->fecha_hora->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    @if ($consulta->motivo_consulta || $consulta->fecha_inicio_sintomas || $consulta->tiempo_evolucion || $consulta->forma_inicio || $consulta->evolucion || $consulta->descripcion_padecimiento)
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Motivo y padecimiento actual</h6>
        @if ($consulta->motivo_consulta)<div class="mb-2"><strong class="text-muted small">Motivo de consulta:</strong><br>{{ $consulta->motivo_consulta }}</div>@endif
        <div class="row g-3">
            @if ($consulta->fecha_inicio_sintomas)<div class="col-12 col-md-3"><strong class="text-muted small">Inicio de síntomas:</strong><br>{{ \Carbon\Carbon::parse($consulta->fecha_inicio_sintomas)->format('d/m/Y') }}</div>@endif
            @if ($consulta->tiempo_evolucion)<div class="col-12 col-md-3"><strong class="text-muted small">Tiempo de evolución:</strong><br>{{ $consulta->tiempo_evolucion }}</div>@endif
            @if ($consulta->forma_inicio)<div class="col-12 col-md-3"><strong class="text-muted small">Forma de inicio:</strong><br>{{ $consulta->forma_inicio === 'subito' ? 'Súbito' : 'Gradual' }}</div>@endif
            @if ($consulta->evolucion)<div class="col-12 col-md-3"><strong class="text-muted small">Evolución:</strong><br>{{ ucfirst($consulta->evolucion) }}</div>@endif
        </div>
        @if ($consulta->descripcion_padecimiento)<div class="mt-2"><strong class="text-muted small">Descripción del padecimiento:</strong><br>{{ $consulta->descripcion_padecimiento }}</div>@endif
    </div>
    @endif

    @if ($consulta->sintomasRegistrados->count())
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Síntomas registrados</h6>
        <div class="table-responsive">
            <table class="table table-sm neu-table mb-0">
                <thead>
                    <tr>
                        <th>Síntoma</th>
                        <th>Ubicación</th>
                        <th>Intensidad</th>
                        <th>Fecha de inicio</th>
                        <th>Duración</th>
                        <th>Frecuencia</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consulta->sintomasRegistrados as $s)
                    <tr>
                        <td>{{ $s->nombre }}</td>
                        <td>{{ $s->ubicacion ?? '—' }}</td>
                        <td>{{ $s->intensidad_categoria ? ucfirst($s->intensidad_categoria) : '—' }}</td>
                        <td>{{ $s->inicio ?? '—' }}</td>
                        <td>{{ $s->duracion ?? '—' }}</td>
                        <td>{{ $s->frecuencia ? ucfirst($s->frecuencia) : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if ($consulta->presion_arterial || $consulta->temperatura || $consulta->frecuencia_cardiaca || $consulta->frecuencia_respiratoria || $consulta->saturacion_oxigeno || $consulta->peso || $consulta->estatura || $consulta->imc)
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Signos vitales y antropometría</h6>
        <div class="row g-3">
            @if ($consulta->presion_arterial)<div class="col-12 col-md-3"><strong class="text-muted small">Presión arterial:</strong><br>{{ $consulta->presion_arterial }} mmHg</div>@endif
            @if ($consulta->temperatura)<div class="col-12 col-md-3"><strong class="text-muted small">Temperatura:</strong><br>{{ $consulta->temperatura }} °C</div>@endif
            @if ($consulta->frecuencia_cardiaca)<div class="col-12 col-md-3"><strong class="text-muted small">Frec. cardíaca:</strong><br>{{ $consulta->frecuencia_cardiaca }} lpm</div>@endif
            @if ($consulta->frecuencia_respiratoria)<div class="col-12 col-md-3"><strong class="text-muted small">Frec. respiratoria:</strong><br>{{ $consulta->frecuencia_respiratoria }} rpm</div>@endif
            @if ($consulta->saturacion_oxigeno)<div class="col-12 col-md-3"><strong class="text-muted small">Saturación O₂:</strong><br>{{ $consulta->saturacion_oxigeno }} %</div>@endif
            @if ($consulta->peso)<div class="col-12 col-md-3"><strong class="text-muted small">Peso:</strong><br>{{ $consulta->peso }} kg</div>@endif
            @if ($consulta->estatura)<div class="col-12 col-md-3"><strong class="text-muted small">Estatura:</strong><br>{{ $consulta->estatura }} cm</div>@endif
            @if ($consulta->imc)<div class="col-12 col-md-3"><strong class="text-muted small">IMC:</strong><br>{{ $consulta->imc }} kg/m²</div>@endif
        </div>
    </div>
    @endif

    @if ($consulta->exploracion_estado_general || $consulta->exploracion_cabeza_cuello || $consulta->exploracion_respiratorio || $consulta->exploracion_cardiovascular || $consulta->exploracion_abdomen || $consulta->exploracion_extremidades || $consulta->exploracion_neurologico || $consulta->exploracion_hallazgos || $consulta->exploracion_sin_hallazgos || $consulta->exploracion_fisica)
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Exploración física</h6>
        @if ($consulta->exploracion_sin_hallazgos)<p class="text-muted fst-italic mb-2">Sin hallazgos relevantes</p>@endif
        @if ($consulta->exploracion_estado_general)<div class="mb-2"><strong class="text-muted small">Estado general:</strong><br>{{ $consulta->exploracion_estado_general }}</div>@endif
        @if ($consulta->exploracion_cabeza_cuello)<div class="mb-2"><strong class="text-muted small">Cabeza y cuello:</strong><br>{{ $consulta->exploracion_cabeza_cuello }}</div>@endif
        @if ($consulta->exploracion_respiratorio)<div class="mb-2"><strong class="text-muted small">Aparato respiratorio:</strong><br>{{ $consulta->exploracion_respiratorio }}</div>@endif
        @if ($consulta->exploracion_cardiovascular)<div class="mb-2"><strong class="text-muted small">Aparato cardiovascular:</strong><br>{{ $consulta->exploracion_cardiovascular }}</div>@endif
        @if ($consulta->exploracion_abdomen)<div class="mb-2"><strong class="text-muted small">Abdomen:</strong><br>{{ $consulta->exploracion_abdomen }}</div>@endif
        @if ($consulta->exploracion_extremidades)<div class="mb-2"><strong class="text-muted small">Extremidades:</strong><br>{{ $consulta->exploracion_extremidades }}</div>@endif
        @if ($consulta->exploracion_neurologico)<div class="mb-2"><strong class="text-muted small">Sistema neurológico:</strong><br>{{ $consulta->exploracion_neurologico }}</div>@endif
        @if ($consulta->exploracion_hallazgos)<div class="mb-2"><strong class="text-muted small">Hallazgos adicionales:</strong><br>{{ $consulta->exploracion_hallazgos }}</div>@endif
        @if ($consulta->exploracion_fisica)<div class="mb-2"><strong class="text-muted small">Exploración física (libre):</strong><br>{{ $consulta->exploracion_fisica }}</div>@endif
    </div>
    @endif

    @if ($consulta->diagnosticos->count())
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Diagnósticos</h6>
        <div class="table-responsive">
            <table class="table table-sm neu-table mb-0">
                <thead>
                    <tr>
                        <th>Diagnóstico</th>
                        <th>Código CIE-10</th>
                        <th>Tipo</th>
                        <th>Principal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consulta->diagnosticos as $d)
                    <tr>
                        <td>{{ $d->descripcion }}</td>
                        <td>{{ $d->codigo_cie10 ?? '—' }}</td>
                        <td>{{ $d->tipo ? ucfirst($d->tipo) : '—' }}</td>
                        <td>@if ($d->es_principal)<i class="fa fa-check text-success"></i>@else—@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if ($consulta->resumen_clinico || $consulta->pronostico)
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Evaluación</h6>
        @if ($consulta->resumen_clinico)<div class="mb-3"><strong class="text-muted small">Resumen clínico:</strong><br>{{ $consulta->resumen_clinico }}</div>@endif
        @if ($consulta->pronostico)<div class="col-12 col-md-3"><strong class="text-muted small">Pronóstico:</strong><br>{{ ucfirst(str_replace('_', ' ', $consulta->pronostico)) }}</div>@endif
    </div>
    @endif

    @if ($consulta->medicamentos->count())
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Medicamentos recetados</h6>
        <div class="table-responsive">
            <table class="table table-sm neu-table mb-0">
                <thead>
                    <tr>
                        <th>Genérico</th>
                        <th>Comercial</th>
                        <th>Concentración</th>
                        <th>Presentación</th>
                        <th>Forma farm.</th>
                        <th>Dosis</th>
                        <th>Vía</th>
                        <th>Frecuencia</th>
                        <th>Duración</th>
                        <th>Cant.</th>
                        <th>Indicaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($consulta->medicamentos as $m)
                    <tr>
                        <td>{{ $m->nombre_generico }}</td>
                        <td>{{ $m->nombre_comercial ?? '—' }}</td>
                        <td>{{ $m->concentracion ?? '—' }}</td>
                        <td>{{ $m->presentacion ?? '—' }}</td>
                        <td>{{ $m->forma_farmaceutica ?? '—' }}</td>
                        <td>{{ $m->dosis ?? '—' }}</td>
                        <td>{{ $m->via_administracion ? ucfirst($m->via_administracion) : '—' }}</td>
                        <td>{{ $m->frecuencia ?? '—' }}</td>
                        <td>{{ $m->duracion ?? '—' }}</td>
                        <td>{{ $m->cantidad_total ?? '—' }}</td>
                        <td>{{ $m->indicaciones ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if ($consulta->plan_medicamentos || $consulta->plan_estudios || $consulta->plan_procedimientos || $consulta->plan_recomendaciones || $consulta->plan_signos_alarma || $consulta->plan_referencia || $consulta->plan_seguimiento_fecha || $consulta->plan_incapacidad)
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Plan y tratamiento</h6>
        <div class="row g-3">
            @if ($consulta->plan_medicamentos)<div class="col-12 col-md-6"><strong class="text-muted small">Medicamentos indicados:</strong><br>{{ $consulta->plan_medicamentos }}</div>@endif
            @if ($consulta->plan_estudios)<div class="col-12 col-md-6"><strong class="text-muted small">Estudios solicitados:</strong><br>{{ $consulta->plan_estudios }}</div>@endif
            @if ($consulta->plan_procedimientos)<div class="col-12 col-md-6"><strong class="text-muted small">Procedimientos:</strong><br>{{ $consulta->plan_procedimientos }}</div>@endif
            @if ($consulta->plan_recomendaciones)<div class="col-12 col-md-6"><strong class="text-muted small">Recomendaciones generales:</strong><br>{{ $consulta->plan_recomendaciones }}</div>@endif
            @if ($consulta->plan_signos_alarma)<div class="col-12 col-md-6"><strong class="text-muted small">Signos de alarma:</strong><br>{{ $consulta->plan_signos_alarma }}</div>@endif
            @if ($consulta->plan_referencia)<div class="col-12 col-md-6"><strong class="text-muted small">Referencia a especialista:</strong><br>{{ $consulta->plan_referencia }}</div>@endif
            @if ($consulta->plan_seguimiento_fecha)<div class="col-12 col-md-3"><strong class="text-muted small">Seguimiento sugerido:</strong><br>{{ \Carbon\Carbon::parse($consulta->plan_seguimiento_fecha)->format('d/m/Y') }}</div>@endif
            @if ($consulta->plan_incapacidad)<div class="col-12 col-md-3"><strong class="text-muted small">Incapacidad:</strong><br>{{ $consulta->plan_incapacidad }}</div>@endif
        </div>
    </div>
    @endif

    @if ($consulta->observaciones)
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Observaciones generales</h6>
        <p class="mb-0">{{ $consulta->observaciones }}</p>
    </div>
    @endif
</div>
@endsection
