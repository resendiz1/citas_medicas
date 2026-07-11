@extends('layouts.app')

@section('title', 'Detalle de Cita')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Detalle de Cita</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i><span class="btn-text">Volver</span></a>
    </div>

    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Información de la Cita</h6>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <strong class="text-muted small">Fecha y hora</strong><br>
                {{ $cita->fecha_hora->format('d/m/Y H:i') }}
            </div>
            <div class="col-12 col-md-4">
                <strong class="text-muted small">Estado</strong><br>
                @switch($cita->estado)
                    @case('pendiente') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-clock me-1"></i>Pendiente</span> @break
                    @case('confirmada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-check me-1"></i>Confirmada</span> @break
                    @case('en_espera') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ffa500;color:#ffa500;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-hourglass-half me-1"></i>En espera</span> @break
                    @case('en_consulta') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #1e90ff;color:#1e90ff;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-stethoscope me-1"></i>En consulta</span> @break
                    @case('finalizada') <span id="estado-badge-{{ $cita->id }}" class="btn btn-primary btn-sm"><i class="fa fa-circle-check me-1"></i>Finalizada</span> @break
                    @case('cancelada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-xmark me-1"></i>Cancelada</span> @break
                    @case('no_asistio') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #dc143c;color:#dc143c;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-user-slash me-1"></i>No asistió</span> @break
                    @case('reprogramada') <span id="estado-badge-{{ $cita->id }}" class="badge" style="border:2px solid #9370db;color:#9370db;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-calendar me-1"></i>Reprogramada</span> @break
                @endswitch
            </div>
            <div class="col-12 col-md-4">
                <strong class="text-muted small">Motivo</strong><br>
                {{ $cita->motivo }}
            </div>
            @if ($cita->fecha_reprogramada)
            <div class="col-12 col-md-4">
                <strong class="text-muted small">Fecha reprogramada</strong><br>
                {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}
            </div>
            @endif
            @if ($cita->notas_paciente)
            <div class="col-12">
                <strong class="text-muted small">Notas del paciente</strong><br>
                {{ $cita->notas_paciente }}
            </div>
            @endif
            @if ($cita->notas_medico)
            <div class="col-12">
                <strong class="text-muted small">Notas del médico</strong><br>
                {{ $cita->notas_medico }}
            </div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="card shadow-2 p-4 h-100">
                <h6 class="fw-bold mb-3" style="color:#1266f1">Paciente</h6>
                <div class="mb-2"><strong class="text-muted small">Nombre:</strong><br>{{ $cita->paciente->name }}</div>
                <div class="mb-2"><strong class="text-muted small">Email:</strong><br>{{ $cita->paciente->email }}</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card shadow-2 p-4 h-100">
                <h6 class="fw-bold mb-3" style="color:#1266f1">Médico</h6>
                <div class="mb-2"><strong class="text-muted small">Nombre:</strong><br>{{ $cita->medico->name }}</div>
                <div class="mb-2"><strong class="text-muted small">Email:</strong><br>{{ $cita->medico->email }}</div>
                @if ($cita->medico->medicoPerfil && $cita->medico->medicoPerfil->tipoMedico)
                <div class="mb-2"><strong class="text-muted small">Especialidad:</strong><br>{{ $cita->medico->medicoPerfil->tipoMedico->nombre_tipo_medico }}</div>
                @endif
            </div>
        </div>
    </div>

    @if ($cita->recetas->count())
    @foreach ($cita->recetas as $receta)
    <div class="card shadow-2 p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color:#1266f1">Receta del {{ $receta->fecha_emision->format('d/m/Y') }}</h6>
            <a href="{{ route('recetas.show', $receta->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-prescription me-1"></i><span class="btn-text">Abrir receta</span></a>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-3"><strong class="text-muted small">Paciente:</strong><br>{{ $receta->cita->paciente->name }}</div>
            <div class="col-12 col-md-3"><strong class="text-muted small">Médico:</strong><br>{{ $receta->cita->medico->name }}</div>
            <div class="col-12 col-md-3"><strong class="text-muted small">Fecha cita:</strong><br>{{ $receta->cita->fecha_hora->format('d/m/Y') }}</div>
            <div class="col-12 col-md-3"><strong class="text-muted small">Emisión:</strong><br>{{ $receta->fecha_emision->format('d/m/Y') }}</div>
        </div>
        @if ($receta->diagnostico)
        <div class="mb-3">
            <strong class="text-muted small">Diagnóstico</strong>
            <p class="mb-0" style="color:var(--text-emphasis);line-height:1.6">{{ $receta->diagnostico }}</p>
        </div>
        @endif
        @if ($receta->indicaciones_generales)
        <div class="mb-3">
            <strong class="text-muted small">Indicaciones generales</strong>
            <p class="mb-0" style="color:var(--text-emphasis);line-height:1.6">{{ $receta->indicaciones_generales }}</p>
        </div>
        @endif
        @if ($receta->medicamentos->count())
        <div class="mb-3">
            <strong class="text-muted small">Medicamentos</strong>
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Genérico</th>
                            <th>Comercial</th>
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
                        @foreach ($receta->medicamentos as $med)
                        <tr>
                            <td style="color:var(--text-emphasis)">{{ $med->medicamento }}</td>
                            <td>{{ $med->nombre_generico ?? '—' }}</td>
                            <td>{{ $med->nombre_comercial ?? '—' }}</td>
                            <td>{{ $med->presentacion ?? '—' }}</td>
                            <td>{{ $med->forma_farmaceutica ?? '—' }}</td>
                            <td>{{ $med->dosis ?? '—' }}</td>
                            <td>{{ $med->via_administracion ?? '—' }}</td>
                            <td>{{ $med->frecuencia ?? '—' }}</td>
                            <td>{{ $med->duracion ?? '—' }}</td>
                            <td>{{ $med->cantidad_total ?? '—' }}</td>
                            <td>{{ $med->indicaciones ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @if ($receta->notas)
        <br><br>
        <div class="mb-3">
            <strong class="text-muted small">Notas adicionales</strong>
            <p class="mb-0" style="color:var(--text-primary);line-height:1.6;white-space:pre-wrap">{{ $receta->notas }}</p>
        </div>
        @endif
        @if ($receta->documentos->count())
        <div>
            <strong class="text-muted small">Documentos adjuntos</strong>
            <div class="d-flex flex-wrap gap-3 mt-1">
                @foreach ($receta->documentos as $doc)
                    <div class="text-center" style="width:100px">
                        @if (str_starts_with($doc->tipo_mime, 'image/'))
                            <a href="{{ route('recetas.documento.download', $doc->id) }}" target="_blank">
                                <img src="{{ route('recetas.documento.download', $doc->id) }}"
                                     alt="{{ $doc->nombre_original }}"
                                     class="rounded mb-1"
                                     style="width:80px;height:80px;object-fit:cover;box-shadow:3px 3px 6px #ccc,-3px -3px 6px #f5f5f5">
                            </a>
                        @else
                            <a href="{{ route('recetas.documento.download', $doc->id) }}"
                               class="d-block neu-btn neu-btn-sm mb-1" style="font-size:0.65rem">📄 PDF</a>
                        @endif
                        <small class="text-muted" style="font-size:0.6rem;word-break:break-word">{{ $doc->nombre_original }}</small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
    @endif

    @if ($cita->consultaMedica)
    @php $consulta = $cita->consultaMedica; @endphp
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Consulta Médica</h6>

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
                    <thead>
                        <tr><th>Síntoma</th><th>Ubicación</th><th>Intensidad</th><th>Inicio</th><th>Duración</th><th>Frecuencia</th></tr>
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
        <div>
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
        <div class="mt-3">
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
    </div>
    @endif

    @if ($cita->historiales->count())
    <div class="card shadow-2 p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:#1266f1">Historial de cambios</h6>
        <div class="table-responsive">
            <table class="table table-sm neu-table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Estado anterior</th>
                        <th>Estado nuevo</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cita->historiales as $h)
                    <tr>
                        <td style="font-size:0.8rem">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-size:0.8rem">{{ optional($h->user)->name ?? '—' }}</td>
                        <td style="font-size:0.8rem">{{ $h->estado_anterior ?? '—' }}</td>
                        <td style="font-size:0.8rem">{{ $h->estado_nuevo }}</td>
                        <td style="font-size:0.8rem">{{ $h->comentario }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br><br>
    </div>
    @endif

</div>
@endsection
