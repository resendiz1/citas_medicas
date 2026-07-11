@extends('layouts.app')

@section('title', 'Consulta Médica')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1" style="color:#1266f1">Consulta Médica</h4>
            <p class="mb-0 text-muted" style="font-size:0.9rem">
                {{ $cita->paciente->name }} &middot;
                Médico: {{ $cita->medico->name }} &middot;
                {{ $cita->fecha_hora->format('d/m/Y, H:i') }}
            </p>
        </div>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger py-2 mb-3" style="border-radius:12px;background:rgba(220,53,69,0.15);color:var(--text-primary);border:1px solid rgba(220,53,69,0.3);">
        <strong class="small">Corrige los errores antes de guardar:</strong>
        <ul class="mb-0 mt-1 small">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('consulta-medica.store', $cita->id) }}" method="POST">
        @csrf
        <input type="hidden" name="accion" id="accionInput" value="borrador">

        {{-- 1. MOTIVO Y PADECIMIENTO ACTUAL --}}
        <details class="card shadow-2 p-4 mb-4" open>
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">Motivo y padecimiento actual</summary>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label text-muted small">Motivo de consulta</label>
                    <textarea name="motivo_consulta" rows="2" class="form-control @error('motivo_consulta') is-invalid @enderror">{{ old('motivo_consulta', $consulta->motivo_consulta ?? '') }}</textarea>
                    @error('motivo_consulta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted small">Fecha de inicio de síntomas</label>
                    <input type="date" name="fecha_inicio_sintomas" id="fechaInicioSintomas" class="form-control @error('fecha_inicio_sintomas') is-invalid @enderror" max="{{ $cita->fecha_hora->format('Y-m-d') }}" value="{{ old('fecha_inicio_sintomas', $consulta->fecha_inicio_sintomas ?? '') }}">
                    @error('fecha_inicio_sintomas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label text-muted small">Tiempo de evolución</label>
                    <input type="text" name="tiempo_evolucion" id="tiempoEvolucion" class="form-control @error('tiempo_evolucion') is-invalid @enderror" placeholder="Calculado automáticamente" value="{{ old('tiempo_evolucion', $consulta->tiempo_evolucion ?? '') }}">
                    @error('tiempo_evolucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted" style="font-size:0.65rem;cursor:pointer" id="calcInfo">Se calcula desde la fecha de inicio. Edítalo si no sabes la fecha exacta.</small>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">Forma de inicio</label>
                    <select name="forma_inicio" class="form-select @error('forma_inicio') is-invalid @enderror">
                        <option value="">—</option>
                        <option value="subito" @selected(old('forma_inicio', $consulta->forma_inicio ?? '') === 'subito')>Súbito</option>
                        <option value="gradual" @selected(old('forma_inicio', $consulta->forma_inicio ?? '') === 'gradual')>Gradual</option>
                    </select>
                    @error('forma_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">Evolución</label>
                    <select name="evolucion" class="form-select @error('evolucion') is-invalid @enderror">
                        <option value="">—</option>
                        <option value="mejorando" @selected(old('evolucion', $consulta->evolucion ?? '') === 'mejorando')>Mejorando</option>
                        <option value="estable" @selected(old('evolucion', $consulta->evolucion ?? '') === 'estable')>Estable</option>
                        <option value="empeorando" @selected(old('evolucion', $consulta->evolucion ?? '') === 'empeorando')>Empeorando</option>
                    </select>
                    @error('evolucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small">Descripción del padecimiento</label>
                    <textarea name="descripcion_padecimiento" rows="3" class="form-control @error('descripcion_padecimiento') is-invalid @enderror">{{ old('descripcion_padecimiento', $consulta->descripcion_padecimiento ?? '') }}</textarea>
                    @error('descripcion_padecimiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </details>

        {{-- 2. SÍNTOMAS --}}
        <details class="card shadow-2 p-4 mb-4" open>
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">Síntomas</summary>
            @php $oldSintomas = old('sintomas_lista', $consulta?->sintomasRegistrados?->toArray() ?? []); @endphp
            @if (count($oldSintomas) === 0)
            <div class="text-center py-4" id="sintomasEmpty">
                <i class="fa fa-notes-medical fa-2x text-muted opacity-50 mb-2"></i>
                <p class="text-muted mb-2">No hay síntomas registrados.</p>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarSintoma"><i class="fa fa-plus me-1"></i>Agregar síntoma</button>
            </div>
            @endif
            <div class="table-responsive" id="sintomasTableWrap" style="{{ count($oldSintomas) === 0 ? 'display:none' : '' }}">
                <table class="table table-sm neu-table mb-0" id="sintomasTable">
                    <thead>
                        <tr>
                            <th style="width:70px"></th>
                            <th style="min-width:120px">Síntoma</th>
                            <th style="min-width:90px">Ubicación</th>
                            <th style="min-width:90px">Intensidad</th>
                            <th style="min-width:130px">Fecha de inicio</th>
                            <th style="min-width:70px">Duración</th>
                            <th style="min-width:90px">Frecuencia</th>
                            <th style="min-width:100px">Observaciones</th>
                        </tr>
                    </thead>
                    <tbody id="sintomasContainer">
                        @foreach ($oldSintomas as $i => $sintoma)
                        <tr class="sintoma-row">
                            <input type="hidden" name="sintomas_lista[{{ $i }}][id]" value="{{ $sintoma['id'] ?? '' }}">
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-sintoma" title="Editar"><i class="fa fa-pen"></i></button>
                                <button type="button" class="btn btn-danger btn-sm btn-remove-sintoma" title="Eliminar"><i class="fa fa-trash-can"></i></button>
                            </td>
                            <td><input type="text" name="sintomas_lista[{{ $i }}][nombre]" class="form-control form-control-sm" value="{{ $sintoma['nombre'] ?? '' }}" required placeholder="Ej. Dolor de cabeza"></td>
                            <td><input type="text" name="sintomas_lista[{{ $i }}][ubicacion]" class="form-control form-control-sm" value="{{ $sintoma['ubicacion'] ?? '' }}" placeholder="Ej. Cabeza"></td>
                            <td>
                                <select name="sintomas_lista[{{ $i }}][intensidad_categoria]" class="form-select form-select-sm intensidad-categoria" data-index="{{ $i }}">
                                    <option value="">—</option>
                                    <option value="leve" @selected(($sintoma['intensidad_categoria'] ?? '') === 'leve')>Leve</option>
                                    <option value="moderado" @selected(($sintoma['intensidad_categoria'] ?? '') === 'moderado')>Moderado</option>
                                    <option value="intenso" @selected(($sintoma['intensidad_categoria'] ?? '') === 'intenso')>Intenso</option>
                                </select>
                            </td>
                            <td><input type="date" name="sintomas_lista[{{ $i }}][inicio]" class="form-control form-control-sm inicio-fecha" value="{{ $sintoma['inicio'] ?? '' }}" max="{{ $cita->fecha_hora->format('Y-m-d') }}"><small class="dias-transcurridos text-muted" style="font-size:0.65rem"></small></td>
                            <td><input type="text" name="sintomas_lista[{{ $i }}][duracion]" class="form-control form-control-sm" value="{{ $sintoma['duracion'] ?? '' }}" placeholder="Ej. 10 min"></td>
                            <td>
                                <select name="sintomas_lista[{{ $i }}][frecuencia]" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    <option value="continuo" @selected(($sintoma['frecuencia'] ?? '') === 'continuo')>Continuo</option>
                                    <option value="intermitente" @selected(($sintoma['frecuencia'] ?? '') === 'intermitente')>Intermitente</option>
                                    <option value="matutino" @selected(($sintoma['frecuencia'] ?? '') === 'matutino')>Matutino</option>
                                    <option value="nocturno" @selected(($sintoma['frecuencia'] ?? '') === 'nocturno')>Nocturno</option>
                                    <option value="ocasional" @selected(($sintoma['frecuencia'] ?? '') === 'ocasional')>Ocasional</option>
                                </select>
                            </td>
                            <td><input type="text" name="sintomas_lista[{{ $i }}][observaciones]" class="form-control form-control-sm" value="{{ $sintoma['observaciones'] ?? '' }}" placeholder="Ej. Duele más al moverme"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarSintomaBottom"><i class="fa fa-plus me-1"></i>Agregar síntoma</button>
            </div>
        </details>

        {{-- 3. SIGNOS VITALES --}}
        <details class="card shadow-2 p-4 mb-4" open>
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">Signos vitales y antropometría</summary>
            <div class="row g-3">
                @php
                    $vitals = [
                        'presion_arterial' => ['label' => 'Presión arterial', 'unidad' => 'mmHg', 'placeholder' => '120/80', 'rango_min' => 90, 'rango_max' => 140, 'tipo' => 'text'],
                        'temperatura' => ['label' => 'Temperatura', 'unidad' => '°C', 'placeholder' => '36.6', 'rango_min' => 36, 'rango_max' => 37.5, 'tipo' => 'number', 'step' => 0.1],
                        'frecuencia_cardiaca' => ['label' => 'Frecuencia cardíaca', 'unidad' => 'lpm', 'placeholder' => '72', 'rango_min' => 60, 'rango_max' => 100, 'tipo' => 'number'],
                        'frecuencia_respiratoria' => ['label' => 'Frecuencia respiratoria', 'unidad' => 'rpm', 'placeholder' => '16', 'rango_min' => 12, 'rango_max' => 20, 'tipo' => 'number'],
                        'saturacion_oxigeno' => ['label' => 'Saturación O₂', 'unidad' => '%', 'placeholder' => '98', 'rango_min' => 95, 'rango_max' => 100, 'tipo' => 'number'],
                        'peso' => ['label' => 'Peso', 'unidad' => 'kg', 'placeholder' => '70', 'tipo' => 'number', 'step' => 0.1],
                        'estatura' => ['label' => 'Estatura', 'unidad' => 'cm', 'placeholder' => '170', 'tipo' => 'number', 'step' => 0.1],
                        'imc' => ['label' => 'IMC', 'unidad' => 'kg/m²', 'placeholder' => 'Automático', 'tipo' => 'number', 'step' => 0.1, 'readonly' => true],
                    ];
                @endphp
                @foreach ($vitals as $field => $cfg)
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">{{ $cfg['label'] }} <span class="text-muted" style="font-size:0.65rem">({{ $cfg['unidad'] }})</span></label>
                    @php
                        $val = old($field, $consulta->$field ?? '');
                        $withinRange = true;
                        if (isset($cfg['rango_min']) && $val !== '' && $val !== null) {
                            $numericVal = is_numeric($val) ? (float)$val : null;
                            if ($numericVal !== null && ($numericVal < $cfg['rango_min'] || $numericVal > $cfg['rango_max'])) {
                                $withinRange = false;
                            }
                        }
                        $extraClass = '';
                        if ($val !== '' && $val !== null) {
                            $extraClass = $withinRange ? ' is-valid' : '';
                        }
                    @endphp
                    <input type="{{ $cfg['tipo'] }}"
                           name="{{ $field }}"
                           id="{{ $field }}"
                           class="form-control form-control-sm{{ $extraClass }}@error($field) is-invalid @enderror"
                           placeholder="{{ $cfg['placeholder'] }}"
                           value="{{ $val }}"
                           @isset($cfg['step']) step="{{ $cfg['step'] }}" @endisset
                           @isset($cfg['readonly']) readonly @endisset>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if (!$withinRange)
                    <small class="text-danger" style="font-size:0.6rem">Fuera de rango ({{ $cfg['rango_min'] }}–{{ $cfg['rango_max'] }})</small>
                    @endif
                </div>
                @endforeach
            </div>
        </details>

        {{-- 4. EXPLORACIÓN FÍSICA --}}
        <details class="card shadow-2 p-4 mb-4">
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1em">
                Exploración física
                <span class="text-muted fw-normal" style="font-size:0.75rem">(contraído)</span>
            </summary>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="exploracion_sin_hallazgos" id="sinHallazgos" value="1" {{ old('exploracion_sin_hallazgos', $consulta->exploracion_sin_hallazgos ?? false) ? 'checked' : '' }}>
                <label class="form-check-label text-muted small" for="sinHallazgos">Sin hallazgos relevantes</label>
            </div>
            @php
                $exploracionAreas = [
                    'exploracion_estado_general' => 'Estado general',
                    'exploracion_cabeza_cuello' => 'Cabeza y cuello',
                    'exploracion_respiratorio' => 'Aparato respiratorio',
                    'exploracion_cardiovascular' => 'Aparato cardiovascular',
                    'exploracion_abdomen' => 'Abdomen',
                    'exploracion_extremidades' => 'Extremidades',
                    'exploracion_neurologico' => 'Sistema neurológico',
                    'exploracion_hallazgos' => 'Hallazgos adicionales',
                ];
            @endphp
            <div class="row g-3 exploracion-campos">
                @foreach ($exploracionAreas as $field => $label)
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small">{{ $label }}</label>
                    <textarea name="{{ $field }}" rows="2" class="form-control @error($field) is-invalid @enderror">{{ old($field, $consulta->$field ?? '') }}</textarea>
                    @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endforeach
            </div>
        </details>

        {{-- 5. EVALUACIÓN Y DIAGNÓSTICOS --}}
        <details class="card shadow-2 p-4 mb-4" open>
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">Evaluación y diagnósticos</summary>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label text-muted small">Resumen clínico</label>
                    <textarea name="resumen_clinico" rows="2" class="form-control @error('resumen_clinico') is-invalid @enderror">{{ old('resumen_clinico', $consulta->resumen_clinico ?? '') }}</textarea>
                    @error('resumen_clinico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <label class="form-label text-muted small mb-2">Diagnósticos</label>
            @php $oldDiagnosticos = old('diagnosticos', $consulta?->diagnosticos?->toArray() ?? []); @endphp
            @if (count($oldDiagnosticos) === 0)
            <div class="text-center py-3" id="diagnosticosEmpty">
                <i class="fa fa-stethoscope fa-2x text-muted opacity-50 mb-2"></i>
                <p class="text-muted mb-2">No hay diagnósticos registrados.</p>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarDiagnostico"><i class="fa fa-plus me-1"></i>Agregar diagnóstico</button>
            </div>
            @endif
            <div class="table-responsive" id="diagnosticosTableWrap" style="{{ count($oldDiagnosticos) === 0 ? 'display:none' : '' }}">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:180px">Diagnóstico</th>
                            <th style="min-width:100px">Código CIE-10</th>
                            <th style="min-width:100px">Tipo</th>
                            <th style="min-width:60px">Principal</th>
                            <th style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="diagnosticosContainer">
                        @foreach ($oldDiagnosticos as $di => $diag)
                        <tr class="diagnostico-row">
                            <input type="hidden" name="diagnosticos[{{ $di }}][id]" value="{{ $diag['id'] ?? '' }}">
                            <td><input type="text" name="diagnosticos[{{ $di }}][descripcion]" class="form-control form-control-sm" value="{{ $diag['descripcion'] ?? '' }}" required placeholder="Ej. Cefalea tensional"></td>
                            <td>
                                <input type="text" name="diagnosticos[{{ $di }}][codigo_cie10]" class="form-control form-control-sm" value="{{ $diag['codigo_cie10'] ?? '' }}" placeholder="Buscar o escribir" list="cie10List">
                            </td>
                            <td>
                                <select name="diagnosticos[{{ $di }}][tipo]" class="form-select form-select-sm" required>
                                    <option value="">—</option>
                                    <option value="probable" @selected(($diag['tipo'] ?? '') === 'probable')>Probable</option>
                                    <option value="diferencial" @selected(($diag['tipo'] ?? '') === 'diferencial')>Diferencial</option>
                                    <option value="definitivo" @selected(($diag['tipo'] ?? '') === 'definitivo')>Definitivo</option>
                                </select>
                            </td>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input diagnostico-principal" type="radio" name="diagnostico_principal" value="{{ $di }}" {{ ($diag['es_principal'] ?? false) ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-diagnostico" title="Eliminar"><i class="fa fa-trash-can"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarDiagnosticoBottom"><i class="fa fa-plus me-1"></i>Agregar diagnóstico</button>
            </div>
            <div class="row g-3 mt-3">
                <div class="col-12 col-md-4">
                    <label class="form-label text-muted small">Pronóstico</label>
                    <select name="pronostico" class="form-select @error('pronostico') is-invalid @enderror">
                        <option value="">—</option>
                        <option value="favorable" @selected(old('pronostico', $consulta->pronostico ?? '') === 'favorable')>Favorable</option>
                        <option value="reservado" @selected(old('pronostico', $consulta->pronostico ?? '') === 'reservado')>Reservado</option>
                        <option value="grave" @selected(old('pronostico', $consulta->pronostico ?? '') === 'grave')>Grave</option>
                        <option value="muy_grave" @selected(old('pronostico', $consulta->pronostico ?? '') === 'muy_grave')>Muy grave</option>
                    </select>
                    @error('pronostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </details>

        {{-- 6. PLAN Y TRATAMIENTO --}}
        <details class="card shadow-2 p-4 mb-4">
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">
                Plan y tratamiento
                <span class="text-muted fw-normal" style="font-size:0.75rem">(contraído)</span>
            </summary>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small">Estudios solicitados</label>
                    <textarea name="plan_estudios" rows="3" class="form-control @error('plan_estudios') is-invalid @enderror" placeholder="Laboratorios, imagen, etc.">{{ old('plan_estudios', $consulta->plan_estudios ?? '') }}</textarea>
                    @error('plan_estudios')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small">Procedimientos</label>
                    <textarea name="plan_procedimientos" rows="3" class="form-control @error('plan_procedimientos') is-invalid @enderror">{{ old('plan_procedimientos', $consulta->plan_procedimientos ?? '') }}</textarea>
                    @error('plan_procedimientos')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small">Recomendaciones generales</label>
                    <textarea name="plan_recomendaciones" rows="2" class="form-control @error('plan_recomendaciones') is-invalid @enderror">{{ old('plan_recomendaciones', $consulta->plan_recomendaciones ?? '') }}</textarea>
                    @error('plan_recomendaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small">Signos de alarma</label>
                    <textarea name="plan_signos_alarma" rows="2" class="form-control @error('plan_signos_alarma') is-invalid @enderror" placeholder="Indicar al paciente cuándo regresar a urgencias...">{{ old('plan_signos_alarma', $consulta->plan_signos_alarma ?? '') }}</textarea>
                    @error('plan_signos_alarma')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small">Referencia a especialista</label>
                    <textarea name="plan_referencia" rows="2" class="form-control @error('plan_referencia') is-invalid @enderror" placeholder="Especialidad, motivo...">{{ old('plan_referencia', $consulta->plan_referencia ?? '') }}</textarea>
                    @error('plan_referencia')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">Fecha sugerida de seguimiento</label>
                    <input type="date" name="plan_seguimiento_fecha" class="form-control @error('plan_seguimiento_fecha') is-invalid @enderror" value="{{ old('plan_seguimiento_fecha', $consulta->plan_seguimiento_fecha ?? '') }}">
                    @error('plan_seguimiento_fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small">Incapacidad</label>
                    <input type="text" name="plan_incapacidad" class="form-control @error('plan_incapacidad') is-invalid @enderror" placeholder="Ej. 3 días" value="{{ old('plan_incapacidad', $consulta->plan_incapacidad ?? '') }}">
                    @error('plan_incapacidad')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </details>

        {{-- 7. MEDICAMENTOS --}}
        <details class="card shadow-2 p-4 mb-4" open>
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">Medicamentos</summary>
            @php $oldMedicamentos = old('medicamentos', $consulta?->medicamentos?->toArray() ?? []); @endphp
            @if (count($oldMedicamentos) === 0)
            <div class="text-center py-4" id="medicamentosEmpty">
                <i class="fa fa-prescription-bottle fa-2x text-muted opacity-50 mb-2"></i>
                <p class="text-muted mb-2">No hay medicamentos registrados.</p>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMedicamento"><i class="fa fa-plus me-1"></i>Agregar medicamento</button>
            </div>
            @endif
            <div class="table-responsive" id="medicamentosTableWrap" style="{{ count($oldMedicamentos) === 0 ? 'display:none' : '' }}">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:100px">Genérico</th>
                            <th style="min-width:90px">Comercial</th>
                            <th style="min-width:70px">Concentración</th>
                            <th style="min-width:80px">Presentación</th>
                            <th style="min-width:80px">Forma farm.</th>
                            <th style="min-width:60px">Dosis</th>
                            <th style="min-width:60px">Vía</th>
                            <th style="min-width:70px">Frecuencia</th>
                            <th style="min-width:60px">Duración</th>
                            <th style="min-width:50px">Cant.</th>
                            <th style="min-width:80px">Indicaciones</th>
                            <th style="min-width:55px">Receta</th>
                            <th style="width:70px"></th>
                        </tr>
                    </thead>
                    <tbody id="medicamentosContainer">
                        @foreach ($oldMedicamentos as $mi => $med)
                        <tr class="medicamento-row">
                            <input type="hidden" name="medicamentos[{{ $mi }}][id]" value="{{ $med['id'] ?? '' }}">
                            <td><input type="text" name="medicamentos[{{ $mi }}][nombre_generico]" class="form-control form-control-sm" required placeholder="Paracetamol" value="{{ $med['nombre_generico'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][nombre_comercial]" class="form-control form-control-sm" placeholder="Tempra" value="{{ $med['nombre_comercial'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][concentracion]" class="form-control form-control-sm" placeholder="500 mg" value="{{ $med['concentracion'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][presentacion]" class="form-control form-control-sm" placeholder="Tabletas" value="{{ $med['presentacion'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][forma_farmaceutica]" class="form-control form-control-sm" placeholder="Tableta" value="{{ $med['forma_farmaceutica'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][dosis]" class="form-control form-control-sm med-dosis" placeholder="1" value="{{ $med['dosis'] ?? '' }}"></td>
                            <td>
                                <select name="medicamentos[{{ $mi }}][via_administracion]" class="form-select form-select-sm">
                                    <option value="">—</option>
                                    <option value="oral" @selected(($med['via_administracion'] ?? '') === 'oral')>Oral</option>
                                    <option value="topica" @selected(($med['via_administracion'] ?? '') === 'topica')>Tópica</option>
                                    <option value="intravenosa" @selected(($med['via_administracion'] ?? '') === 'intravenosa')>IV</option>
                                    <option value="intramuscular" @selected(($med['via_administracion'] ?? '') === 'intramuscular')>IM</option>
                                    <option value="subcutanea" @selected(($med['via_administracion'] ?? '') === 'subcutanea')>SC</option>
                                    <option value="inhalatoria" @selected(($med['via_administracion'] ?? '') === 'inhalatoria')>Inhalatoria</option>
                                    <option value="oftalmica" @selected(($med['via_administracion'] ?? '') === 'oftalmica')>Oftálmica</option>
                                    <option value="otica" @selected(($med['via_administracion'] ?? '') === 'otica')>Ótica</option>
                                </select>
                            </td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][frecuencia]" class="form-control form-control-sm med-frecuencia" placeholder="C/8h" value="{{ $med['frecuencia'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][duracion]" class="form-control form-control-sm med-duracion" placeholder="7 días" value="{{ $med['duracion'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][cantidad_total]" class="form-control form-control-sm med-cantidad" placeholder="Auto" value="{{ $med['cantidad_total'] ?? '' }}"></td>
                            <td><input type="text" name="medicamentos[{{ $mi }}][indicaciones]" class="form-control form-control-sm" placeholder="Tomar con alimentos" value="{{ $med['indicaciones'] ?? '' }}"></td>
                            <td class="text-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="medicamentos[{{ $mi }}][incluir_en_receta]" value="1" {{ ($med['incluir_en_receta'] ?? true) ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-med" title="Editar"><i class="fa fa-pen"></i></button>
                                <button type="button" class="btn btn-danger btn-sm btn-remove-med" title="Eliminar"><i class="fa fa-trash-can"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMedicamentoBottom"><i class="fa fa-plus me-1"></i>Agregar medicamento</button>
                <small class="text-muted ms-2" style="font-size:0.65rem">Los medicamentos con "Incluir en receta" activo se usarán al generar la receta.</small>
            </div>
        </details>

        {{-- 8. VISTA PREVIA DE RECETA --}}
        <details class="card shadow-2 p-4 mb-4">
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">
                Vista previa de receta
                <span class="text-muted fw-normal" style="font-size:0.75rem">(contraído)</span>
            </summary>
            <div id="recetaPreview">
                <div class="alert alert-info py-2 mb-3" style="border-radius:12px;font-size:0.85rem">
                    <i class="fa fa-info-circle me-1"></i>
                    La receta se generará con el diagnóstico principal y los medicamentos marcados para incluir.
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <span class="text-muted small">Paciente</span>
                        <p class="fw-bold mb-0" id="previewPaciente">{{ $cita->paciente->name }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-muted small">Médico</span>
                        <p class="fw-bold mb-0" id="previewMedico">{{ $cita->medico->name }}</p>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small">Diagnóstico principal</span>
                        <p class="mb-0" id="previewDiagnostico"><em class="text-muted">Se tomará del diagnóstico marcado como principal</em></p>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small">Medicamentos a incluir</span>
                        <div id="previewMedicamentos">
                            <em class="text-muted">No hay medicamentos marcados para receta</em>
                        </div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted small">Indicaciones / Recomendaciones</span>
                        <p class="mb-0" id="previewIndicaciones"><em class="text-muted">Se tomarán de "Recomendaciones generales" en Plan y tratamiento</em></p>
                    </div>
                </div>
            </div>
        </details>

        {{-- 9. OBSERVACIONES GENERALES --}}
        <details class="card shadow-2 p-4 mb-4">
            <summary class="fw-bold mb-3" style="color:#1266f1;cursor:pointer;font-size:1rem">
                Observaciones generales de la consulta
                <span class="text-muted fw-normal" style="font-size:0.75rem">(contraído)</span>
            </summary>
            <div class="row">
                <div class="col-12">
                    <textarea name="observaciones" rows="3" class="form-control @error('observaciones') is-invalid @enderror" placeholder="Notas adicionales sobre la consulta...">{{ old('observaciones', $consulta->observaciones ?? '') }}</textarea>
                    @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </details>

        {{-- BOTONES DE ACCIÓN --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <button type="submit" class="btn btn-outline-secondary" onclick="document.getElementById('accionInput').value='borrador'">
                <i class="fa fa-floppy-disk me-1"></i>Guardar borrador
            </button>
            <button type="submit" class="btn btn-primary" onclick="document.getElementById('accionInput').value='finalizar'">
                <i class="fa fa-check me-1"></i>Finalizar consulta
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-xmark me-1"></i>Salir
            </a>
        </div>
    </form>
</div>

<datalist id="cie10List">
    <option value="A00.0">Cólera</option>
    <option value="A09">Diarrea y gastroenteritis</option>
    <option value="B00.9">Infección por herpes viral</option>
    <option value="B34.9">Infección viral no especificada</option>
    <option value="E10.9">Diabetes mellitus tipo 1</option>
    <option value="E11.9">Diabetes mellitus tipo 2</option>
    <option value="E66.9">Obesidad no especificada</option>
    <option value="F32.0">Episodio depresivo leve</option>
    <option value="F41.0">Trastorno de pánico</option>
    <option value="G40.9">Epilepsia no especificada</option>
    <option value="G43.9">Migraña no especificada</option>
    <option value="G44.2">Cefalea tensional</option>
    <option value="I10">Hipertensión esencial</option>
    <option value="I25.1">Enfermedad cardíaca aterosclerótica</option>
    <option value="J00">Resfriado común</option>
    <option value="J02.0">Faringitis estreptocócica</option>
    <option value="J06.9">Infección respiratoria aguda no especificada</option>
    <option value="J15.9">Neumonía bacteriana no especificada</option>
    <option value="J20.9">Bronquitis aguda no especificada</option>
    <option value="J45.0">Asma alérgica</option>
    <option value="K29.7">Gastritis no especificada</option>
    <option value="K59.0">Estreñimiento</option>
    <option value="L20.8">Dermatitis atópica</option>
    <option value="L30.9">Dermatitis no especificada</option>
    <option value="M05.9">Artritis reumatoide seropositiva</option>
    <option value="M10.0">Gota idiopática</option>
    <option value="M17.9">Gonartrosis no especificada</option>
    <option value="M54.4">Lumbago con ciática</option>
    <option value="M54.5">Lumbago no especificado</option>
    <option value="N39.0">Infección de vías urinarias</option>
    <option value="R05">Tos</option>
    <option value="R06.0">Disnea</option>
    <option value="R10.1">Dolor abdominal superior</option>
    <option value="R50.9">Fiebre no especificada</option>
    <option value="R51">Cefalea</option>
    <option value="Z00.0">Revisión general de salud</option>
    <option value="Z23">Inmunización contra enfermedades virales</option>
</datalist>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calc tiempo de evolución
        var fechaInput = document.getElementById('fechaInicioSintomas');
        var evolucionInput = document.getElementById('tiempoEvolucion');
        var calcInfo = document.getElementById('calcInfo');
        var fechaConsulta = '{{ $cita->fecha_hora->format("Y-m-d") }}';

        function calcTiempoEvolucion() {
            if (!fechaInput.value) return;
            var inicio = new Date(fechaInput.value + 'T00:00:00');
            var consulta = new Date(fechaConsulta + 'T00:00:00');
            var diffMs = consulta - inicio;
            if (diffMs < 0) {
                fechaInput.setCustomValidity('La fecha no puede ser posterior a la consulta.');
                fechaInput.reportValidity();
                return;
            }
            fechaInput.setCustomValidity('');
            var diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
            if (diffDays === 0) {
                evolucionInput.value = 'Hoy';
            } else if (diffDays === 1) {
                evolucionInput.value = '1 día';
            } else {
                evolucionInput.value = diffDays + ' días';
            }
            calcInfo.textContent = 'Editado manualmente. Haz clic para recalcular desde la fecha.';
        }

        if (fechaInput && evolucionInput) {
            fechaInput.addEventListener('change', function() {
                if (!evolucionInput.dataset.edited) calcTiempoEvolucion();
            });
            evolucionInput.addEventListener('focus', function() {
                this.dataset.edited = 'true';
                calcInfo.textContent = 'Editado manualmente. Haz clic para recalcular.';
            });
            calcInfo.addEventListener('click', function() {
                delete evolucionInput.dataset.edited;
                calcTiempoEvolucion();
                calcInfo.textContent = 'Recalculado desde la fecha de inicio.';
            });
            if (fechaInput.value && !evolucionInput.value) calcTiempoEvolucion();
        }

        // Calc días transcurridos por síntoma en filas existentes
        document.querySelectorAll('#sintomasContainer .inicio-fecha').forEach(function(el) {
            calcDiasSintoma(el);
        });

        // Auto-calc IMC
        function calcIMC() {
            var peso = parseFloat(document.getElementById('peso').value);
            var estatura = parseFloat(document.getElementById('estatura').value);
            var imcInput = document.getElementById('imc');
            if (peso > 0 && estatura > 0) {
                var imc = peso / Math.pow(estatura / 100, 2);
                imcInput.value = imc.toFixed(1);
            } else if (peso <= 0 || estatura <= 0) {
                imcInput.value = '';
            }
        }
        var pesoEl = document.getElementById('peso');
        var estaturaEl = document.getElementById('estatura');
        if (pesoEl && estaturaEl) {
            pesoEl.addEventListener('input', calcIMC);
            estaturaEl.addEventListener('input', calcIMC);
        }

        // Auto-calc cantidad_total from dosis * frecuencia * duración
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('med-dosis') || e.target.classList.contains('med-frecuencia') || e.target.classList.contains('med-duracion')) {
                var row = e.target.closest('tr');
                var dosis = row.querySelector('.med-dosis').value;
                var frecuencia = row.querySelector('.med-frecuencia').value;
                var duracion = row.querySelector('.med-duracion').value;
                var cantidadInput = row.querySelector('.med-cantidad');
                if (cantidadInput.dataset.edited) return;
                var dosisNum = parseInt(dosis);
                var freqMatch = frecuencia ? frecuencia.match(/(\d+)/) : null;
                var freqNum = freqMatch ? parseInt(freqMatch[1]) : null;
                var durMatch = duracion ? duracion.match(/(\d+)/) : null;
                var durNum = durMatch ? parseInt(durMatch[1]) : null;
                if (dosisNum && freqNum && durNum) {
                    var total = dosisNum * (24 / freqNum) * durNum;
                    cantidadInput.value = Math.ceil(total);
                }
            }
        });
        document.addEventListener('focus', function(e) {
            if (e.target.classList.contains('med-cantidad')) {
                e.target.dataset.edited = 'true';
            }
        });

        // Sin hallazgos: no borra datos, solo marca visual
        document.getElementById('sinHallazgos')?.addEventListener('change', function() {
            document.querySelectorAll('.exploracion-campos textarea').forEach(function(el) {
                el.style.opacity = this.checked ? '0.5' : '1';
            }.bind(this));
        });

        // --- SÍNTOMAS DINÁMICOS ---
        var sintomaIndex = document.querySelectorAll('#sintomasContainer .sintoma-row').length;
        var sintomaTemplate = function(idx) {
            return '<tr class="sintoma-row">' +
                '<input type="hidden" name="sintomas_lista[' + idx + '][id]" value="">' +
                '<td class="text-nowrap"><button type="button" class="btn btn-outline-secondary btn-sm btn-edit-sintoma" title="Editar"><i class="fa fa-pen"></i></button><button type="button" class="btn btn-danger btn-sm btn-remove-sintoma" title="Eliminar"><i class="fa fa-trash-can"></i></button></td>' +
                '<td><input type="text" name="sintomas_lista[' + idx + '][nombre]" class="form-control form-control-sm" required placeholder="Ej. Dolor de cabeza"></td>' +
                '<td><input type="text" name="sintomas_lista[' + idx + '][ubicacion]" class="form-control form-control-sm" placeholder="Ej. Cabeza"></td>' +
                '<td><select name="sintomas_lista[' + idx + '][intensidad_categoria]" class="form-select form-select-sm intensidad-categoria"><option value="">—</option><option value="leve">Leve</option><option value="moderado">Moderado</option><option value="intenso">Intenso</option></select></td>' +
                '<td><input type="date" name="sintomas_lista[' + idx + '][inicio]" class="form-control form-control-sm inicio-fecha" max="' + fechaConsulta + '"><small class="dias-transcurridos text-muted" style="font-size:0.65rem"></small></td>' +
                '<td><input type="text" name="sintomas_lista[' + idx + '][duracion]" class="form-control form-control-sm" placeholder="Ej. 10 min"></td>' +
                '<td><select name="sintomas_lista[' + idx + '][frecuencia]" class="form-select form-select-sm"><option value="">—</option><option value="continuo">Continuo</option><option value="intermitente">Intermitente</option><option value="matutino">Matutino</option><option value="nocturno">Nocturno</option><option value="ocasional">Ocasional</option></select></td>' +
                '<td><input type="text" name="sintomas_lista[' + idx + '][observaciones]" class="form-control form-control-sm" placeholder="Ej. Duele más al moverme"></td>' +
                '</tr>';
        };

        function calcDiasSintoma(input) {
            var small = input.parentElement.querySelector('.dias-transcurridos');
            if (!small) return;
            if (!input.value) { small.textContent = ''; return; }
            var inicio = new Date(input.value + 'T00:00:00');
            var consulta = new Date(fechaConsulta + 'T00:00:00');
            var diffMs = consulta - inicio;
            if (diffMs < 0) { small.textContent = 'Fecha futura'; return; }
            var dias = Math.round(diffMs / (1000 * 60 * 60 * 24));
            if (dias === 0) small.textContent = 'Hoy';
            else if (dias === 1) small.textContent = '1 día';
            else small.textContent = dias + ' días';
        }

        document.getElementById('sintomasContainer').addEventListener('change', function(e) {
            if (e.target.classList.contains('inicio-fecha')) calcDiasSintoma(e.target);
        });

        function addSintoma() {
            var tbody = document.getElementById('sintomasContainer');
            tbody.insertAdjacentHTML('beforeend', sintomaTemplate(sintomaIndex));
            sintomaIndex++;
            document.getElementById('sintomasEmpty').style.display = 'none';
            document.getElementById('sintomasTableWrap').style.display = '';
            var lastFecha = tbody.querySelector('.sintoma-row:last-child .inicio-fecha');
            if (lastFecha) calcDiasSintoma(lastFecha);
        }

        document.getElementById('btnAgregarSintoma')?.addEventListener('click', addSintoma);
        document.getElementById('btnAgregarSintomaBottom')?.addEventListener('click', addSintoma);

        document.getElementById('sintomasContainer').addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-remove-sintoma');
            if (btn) {
                btn.closest('.sintoma-row').remove();
                if (document.querySelectorAll('#sintomasContainer .sintoma-row').length === 0) {
                    document.getElementById('sintomasEmpty').style.display = '';
                    document.getElementById('sintomasTableWrap').style.display = 'none';
                }
            }
        });

        // --- DIAGNÓSTICOS DINÁMICOS ---
        var diagIndex = document.querySelectorAll('#diagnosticosContainer .diagnostico-row').length;
        var diagTemplate = function(idx) {
            return '<tr class="diagnostico-row">' +
                '<input type="hidden" name="diagnosticos[' + idx + '][id]" value="">' +
                '<td><input type="text" name="diagnosticos[' + idx + '][descripcion]" class="form-control form-control-sm" required placeholder="Ej. Cefalea tensional"></td>' +
                '<td><input type="text" name="diagnosticos[' + idx + '][codigo_cie10]" class="form-control form-control-sm" placeholder="Buscar o escribir" list="cie10List"></td>' +
                '<td><select name="diagnosticos[' + idx + '][tipo]" class="form-select form-select-sm" required><option value="">—</option><option value="probable">Probable</option><option value="diferencial">Diferencial</option><option value="definitivo">Definitivo</option></select></td>' +
                '<td><div class="form-check"><input class="form-check-input diagnostico-principal" type="radio" name="diagnostico_principal" value="' + idx + '"></div></td>' +
                '<td class="text-nowrap"><button type="button" class="btn btn-danger btn-sm btn-remove-diagnostico" title="Eliminar"><i class="fa fa-trash-can"></i></button></td>' +
                '</tr>';
        };

        function addDiagnostico() {
            var tbody = document.getElementById('diagnosticosContainer');
            tbody.insertAdjacentHTML('beforeend', diagTemplate(diagIndex));
            diagIndex++;
            document.getElementById('diagnosticosEmpty').style.display = 'none';
            document.getElementById('diagnosticosTableWrap').style.display = '';
        }

        document.getElementById('btnAgregarDiagnostico')?.addEventListener('click', addDiagnostico);
        document.getElementById('btnAgregarDiagnosticoBottom')?.addEventListener('click', addDiagnostico);

        document.getElementById('diagnosticosContainer').addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-remove-diagnostico');
            if (btn) {
                btn.closest('.diagnostico-row').remove();
                if (document.querySelectorAll('#diagnosticosContainer .diagnostico-row').length === 0) {
                    document.getElementById('diagnosticosEmpty').style.display = '';
                    document.getElementById('diagnosticosTableWrap').style.display = 'none';
                }
            }
        });

        // Radio principal sync
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('diagnostico-principal')) {
                document.querySelectorAll('.diagnostico-principal').forEach(function(rb) {
                    if (rb !== e.target) rb.checked = false;
                });
            }
        });

        // --- MEDICAMENTOS DINÁMICOS ---
        var medIndex = document.querySelectorAll('#medicamentosContainer .medicamento-row').length;
        var medTemplate = function(idx) {
            return '<tr class="medicamento-row">' +
                '<input type="hidden" name="medicamentos[' + idx + '][id]" value="">' +
                '<td><input type="text" name="medicamentos[' + idx + '][nombre_generico]" class="form-control form-control-sm" required placeholder="Paracetamol"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][nombre_comercial]" class="form-control form-control-sm" placeholder="Tempra"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][concentracion]" class="form-control form-control-sm" placeholder="500 mg"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][presentacion]" class="form-control form-control-sm" placeholder="Tabletas"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][forma_farmaceutica]" class="form-control form-control-sm" placeholder="Tableta"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][dosis]" class="form-control form-control-sm med-dosis" placeholder="1"></td>' +
                '<td><select name="medicamentos[' + idx + '][via_administracion]" class="form-select form-select-sm"><option value="">—</option><option value="oral">Oral</option><option value="topica">Tópica</option><option value="intravenosa">IV</option><option value="intramuscular">IM</option><option value="subcutanea">SC</option><option value="inhalatoria">Inhalatoria</option><option value="oftalmica">Oftálmica</option><option value="otica">Ótica</option></select></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][frecuencia]" class="form-control form-control-sm med-frecuencia" placeholder="C/8h"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][duracion]" class="form-control form-control-sm med-duracion" placeholder="7 días"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][cantidad_total]" class="form-control form-control-sm med-cantidad" placeholder="Auto"></td>' +
                '<td><input type="text" name="medicamentos[' + idx + '][indicaciones]" class="form-control form-control-sm" placeholder="Tomar con alimentos"></td>' +
                '<td class="text-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="medicamentos[' + idx + '][incluir_en_receta]" value="1" checked></div></td>' +
                '<td class="text-nowrap"><button type="button" class="btn btn-outline-secondary btn-sm btn-edit-med" title="Editar"><i class="fa fa-pen"></i></button><button type="button" class="btn btn-danger btn-sm btn-remove-med" title="Eliminar"><i class="fa fa-trash-can"></i></button></td>' +
                '</tr>';
        };

        function addMedicamento() {
            var tbody = document.getElementById('medicamentosContainer');
            tbody.insertAdjacentHTML('beforeend', medTemplate(medIndex));
            medIndex++;
            document.getElementById('medicamentosEmpty').style.display = 'none';
            document.getElementById('medicamentosTableWrap').style.display = '';
        }

        document.getElementById('btnAgregarMedicamento')?.addEventListener('click', addMedicamento);
        document.getElementById('btnAgregarMedicamentoBottom')?.addEventListener('click', addMedicamento);

        document.getElementById('medicamentosContainer').addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-remove-med');
            if (btn) {
                btn.closest('.medicamento-row').remove();
                if (document.querySelectorAll('#medicamentosContainer .medicamento-row').length === 0) {
                    document.getElementById('medicamentosEmpty').style.display = '';
                    document.getElementById('medicamentosTableWrap').style.display = 'none';
                }
            }
        });

        // Botón editar síntoma (hace focus al primer input)
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-edit-sintoma');
            if (btn) {
                var row = btn.closest('tr');
                var firstInput = row.querySelector('input');
                if (firstInput) firstInput.focus();
            }
        });

        // Botón editar medicamento
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-edit-med');
            if (btn) {
                var row = btn.closest('tr');
                var firstInput = row.querySelector('input');
                if (firstInput) firstInput.focus();
            }
        });
    });
</script>
@endpush
@endsection
