@extends('layouts.app')

@section('title', 'Consulta Médica')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Consulta Médica</h4>
        <span class="text-muted small">Paciente: {{ $cita->paciente->name }} — {{ $cita->fecha_hora->format('d/m/Y H:i') }}</span>
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

        <div class="neu-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:var(--yellow)">Motivo y síntomas</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label text-muted small">Motivo de consulta</label>
                    <textarea name="motivo_consulta" rows="2" class="neu-textarea form-control @error('motivo_consulta') is-invalid @enderror">{{ old('motivo_consulta', $consulta->motivo_consulta ?? '') }}</textarea>
                    @error('motivo_consulta')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label text-muted small">Síntomas</label>
                    <textarea name="sintomas" rows="2" class="neu-textarea form-control @error('sintomas') is-invalid @enderror">{{ old('sintomas', $consulta->sintomas ?? '') }}</textarea>
                    @error('sintomas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Tiempo de evolución</label>
                    <input type="text" name="tiempo_evolucion" class="neu-input form-control @error('tiempo_evolucion') is-invalid @enderror" value="{{ old('tiempo_evolucion', $consulta->tiempo_evolucion ?? '') }}">
                    @error('tiempo_evolucion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="neu-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:var(--yellow)">
                Dolores
                <button type="button" class="neu-btn neu-btn-sm ms-2" id="btnAgregarDolor">+ Agregar</button>
            </h6>
            <div id="doloresContainer">
                @php
                    $oldDolores = old('dolores', $consulta?->dolores?->toArray() ?? []);
                @endphp
                @foreach ($oldDolores as $i => $dolor)
                <div class="dolor-row row g-3 align-items-end mb-2 p-2" style="border-radius:12px;">
                    <input type="hidden" name="dolores[{{ $i }}][id]" value="{{ $dolor['id'] ?? '' }}">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Ubicación</label>
                        <input type="text" name="dolores[{{ $i }}][ubicacion]" class="neu-input form-control" value="{{ $dolor['ubicacion'] ?? '' }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Intensidad</label>
                        <input type="text" name="dolores[{{ $i }}][intensidad]" class="neu-input form-control" value="{{ $dolor['intensidad'] ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Duración</label>
                        <input type="text" name="dolores[{{ $i }}][duracion]" class="neu-input form-control" value="{{ $dolor['duracion'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="neu-btn neu-btn-sm btn-remove-dolor" style="background:#e74c3c;color:#fff;border:none;">Eliminar</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="neu-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:var(--yellow)">Signos vitales</h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted small">Presión arterial</label>
                    <input type="text" name="presion_arterial" class="neu-input form-control @error('presion_arterial') is-invalid @enderror" placeholder="120/80" value="{{ old('presion_arterial', $consulta->presion_arterial ?? '') }}">
                    @error('presion_arterial')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Temperatura (°C)</label>
                    <input type="number" step="0.1" name="temperatura" class="neu-input form-control @error('temperatura') is-invalid @enderror" value="{{ old('temperatura', $consulta->temperatura ?? '') }}">
                    @error('temperatura')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Frec. cardíaca (lpm)</label>
                    <input type="number" name="frecuencia_cardiaca" class="neu-input form-control @error('frecuencia_cardiaca') is-invalid @enderror" value="{{ old('frecuencia_cardiaca', $consulta->frecuencia_cardiaca ?? '') }}">
                    @error('frecuencia_cardiaca')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Frec. respiratoria (rpm)</label>
                    <input type="number" name="frecuencia_respiratoria" class="neu-input form-control @error('frecuencia_respiratoria') is-invalid @enderror" value="{{ old('frecuencia_respiratoria', $consulta->frecuencia_respiratoria ?? '') }}">
                    @error('frecuencia_respiratoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Saturación O₂ (%)</label>
                    <input type="number" name="saturacion_oxigeno" class="neu-input form-control @error('saturacion_oxigeno') is-invalid @enderror" value="{{ old('saturacion_oxigeno', $consulta->saturacion_oxigeno ?? '') }}">
                    @error('saturacion_oxigeno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Peso (kg)</label>
                    <input type="number" step="0.1" name="peso" class="neu-input form-control @error('peso') is-invalid @enderror" value="{{ old('peso', $consulta->peso ?? '') }}">
                    @error('peso')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Estatura (cm)</label>
                    <input type="number" step="0.1" name="estatura" class="neu-input form-control @error('estatura') is-invalid @enderror" value="{{ old('estatura', $consulta->estatura ?? '') }}">
                    @error('estatura')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">IMC</label>
                    <input type="number" step="0.1" name="imc" class="neu-input form-control @error('imc') is-invalid @enderror" value="{{ old('imc', $consulta->imc ?? '') }}">
                    @error('imc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="neu-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:var(--yellow)">Exploración y diagnóstico</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label text-muted small">Exploración física</label>
                    <textarea name="exploracion_fisica" rows="3" class="neu-textarea form-control @error('exploracion_fisica') is-invalid @enderror">{{ old('exploracion_fisica', $consulta->exploracion_fisica ?? '') }}</textarea>
                    @error('exploracion_fisica')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small">Observaciones del médico</label>
                    <textarea name="observaciones" rows="2" class="neu-textarea form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $consulta->observaciones ?? '') }}</textarea>
                    @error('observaciones')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Diagnóstico probable</label>
                    <textarea name="diagnostico_probable" rows="2" class="neu-textarea form-control @error('diagnostico_probable') is-invalid @enderror">{{ old('diagnostico_probable', $consulta->diagnostico_probable ?? '') }}</textarea>
                    @error('diagnostico_probable')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Diagnóstico final</label>
                    <textarea name="diagnostico_final" rows="2" class="neu-textarea form-control @error('diagnostico_final') is-invalid @enderror">{{ old('diagnostico_final', $consulta->diagnostico_final ?? '') }}</textarea>
                    @error('diagnostico_final')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Código CIE-10</label>
                    <input type="text" name="codigo_cie10" class="neu-input form-control @error('codigo_cie10') is-invalid @enderror" placeholder="Ej. J00.X" value="{{ old('codigo_cie10', $consulta->codigo_cie10 ?? '') }}">
                    @error('codigo_cie10')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="neu-card p-4 mb-4">
            <h6 class="fw-bold mb-3" style="color:var(--yellow)">
                Recetas
                <button type="button" class="neu-btn neu-btn-sm ms-2" id="btnAgregarReceta">+ Agregar receta</button>
            </h6>
            <div id="recetasContainer">
                @php
                    $oldRecetas = old('recetas', $cita->recetas->load('medicamentos')->toArray() ?? []);
                @endphp
                @foreach ($oldRecetas as $ri => $receta)
                <div class="receta-card neu-card p-3 mb-3" style="border-radius:12px;">
                    <input type="hidden" name="recetas[{{ $ri }}][id]" value="{{ $receta['id'] ?? '' }}">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Diagnóstico</label>
                            <textarea name="recetas[{{ $ri }}][diagnostico]" rows="2" class="neu-textarea form-control @error('recetas.'.$ri.'.diagnostico') is-invalid @enderror" required>{{ $receta['diagnostico'] ?? '' }}</textarea>
                            @error('recetas.'.$ri.'.diagnostico')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Indicaciones generales</label>
                            <textarea name="recetas[{{ $ri }}][indicaciones_generales]" rows="2" class="neu-textarea form-control @error('recetas.'.$ri.'.indicaciones_generales') is-invalid @enderror" required>{{ $receta['indicaciones_generales'] ?? '' }}</textarea>
                            @error('recetas.'.$ri.'.indicaciones_generales')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Notas adicionales</label>
                        <textarea name="recetas[{{ $ri }}][notas]" rows="1" class="neu-textarea form-control @error('recetas.'.$ri.'.notas') is-invalid @enderror">{{ $receta['notas'] ?? '' }}</textarea>
                        @error('recetas.'.$ri.'.notas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label text-muted small">Medicamentos</label>
                        <button type="button" class="neu-btn neu-btn-sm btn-agregar-med-receta ms-2" data-receta="{{ $ri }}">+</button>
                    </div>
                    <div class="medicamentos-container">
                        @foreach ($receta['medicamentos'] ?? [] as $mi => $med)
                        <div class="row g-2 mb-2 medicamento-row align-items-end">
                            <input type="hidden" name="recetas[{{ $ri }}][medicamentos][{{ $mi }}][id]" value="{{ $med['id'] ?? '' }}">
                            <div class="col-md-3">
                                <input type="text" name="recetas[{{ $ri }}][medicamentos][{{ $mi }}][nombre]" class="neu-input form-control" placeholder="Medicamento" value="{{ $med['medicamento'] ?? $med['nombre'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="recetas[{{ $ri }}][medicamentos][{{ $mi }}][dosis]" class="neu-input form-control" placeholder="Dosis" value="{{ $med['dosis'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="recetas[{{ $ri }}][medicamentos][{{ $mi }}][frecuencia]" class="neu-input form-control" placeholder="Frecuencia" value="{{ $med['frecuencia'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="recetas[{{ $ri }}][medicamentos][{{ $mi }}][duracion]" class="neu-input form-control" placeholder="Duración" value="{{ $med['duracion'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="recetas[{{ $ri }}][medicamentos][{{ $mi }}][indicaciones]" class="neu-input form-control" placeholder="Indicaciones" value="{{ $med['indicaciones'] ?? '' }}">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="neu-btn neu-btn-sm btn-remove-med-receta" style="background:#e74c3c;color:#fff;border:none;">×</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <button type="button" class="neu-btn neu-btn-sm btn-remove-receta" style="background:#e74c3c;color:#fff;border:none;">Eliminar receta</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="neu-btn neu-btn-primary">Guardar consulta</button>
            <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm">Cancelar</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const doloresContainer = document.getElementById('doloresContainer');
        const btnAgregarDolor = document.getElementById('btnAgregarDolor');
        let dolorIndex = doloresContainer.querySelectorAll('.dolor-row').length;

        btnAgregarDolor.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'dolor-row row g-3 align-items-end mb-2 p-2';
            row.style.cssText = 'border-radius:12px;';
            row.innerHTML = `
                <input type="hidden" name="dolores[${dolorIndex}][id]" value="">
                <div class="col-md-4">
                    <label class="form-label text-muted small">Ubicación</label>
                    <input type="text" name="dolores[${dolorIndex}][ubicacion]" class="neu-input form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Intensidad</label>
                    <input type="text" name="dolores[${dolorIndex}][intensidad]" class="neu-input form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small">Duración</label>
                    <input type="text" name="dolores[${dolorIndex}][duracion]" class="neu-input form-control">
                </div>
                <div class="col-md-2">
                    <button type="button" class="neu-btn neu-btn-sm btn-remove-dolor" style="background:#e74c3c;color:#fff;border:none;">Eliminar</button>
                </div>
            `;
            doloresContainer.appendChild(row);
            dolorIndex++;
        });

        doloresContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-remove-dolor')) {
                e.target.closest('.dolor-row').remove();
            }
        });

        let recetaIndex = document.querySelectorAll('#recetasContainer .receta-card').length;

        function crearRecetaCard(ri) {
            const card = document.createElement('div');
            card.className = 'receta-card neu-card p-3 mb-3';
            card.style.cssText = 'border-radius:12px;';
            card.innerHTML = `
                <input type="hidden" name="recetas[${ri}][id]" value="">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Diagnóstico</label>
                        <textarea name="recetas[${ri}][diagnostico]" rows="2" class="neu-textarea form-control" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Indicaciones generales</label>
                        <textarea name="recetas[${ri}][indicaciones_generales]" rows="2" class="neu-textarea form-control" required></textarea>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Notas adicionales</label>
                    <textarea name="recetas[${ri}][notas]" rows="1" class="neu-textarea form-control"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label text-muted small">Medicamentos</label>
                    <button type="button" class="neu-btn neu-btn-sm btn-agregar-med-receta ms-2" data-receta="${ri}">+</button>
                </div>
                <div class="medicamentos-container"></div>
                <div class="mt-2">
                    <button type="button" class="neu-btn neu-btn-sm btn-remove-receta" style="background:#e74c3c;color:#fff;border:none;">Eliminar receta</button>
                </div>
            `;
            return card;
        }

        document.getElementById('btnAgregarReceta').addEventListener('click', function () {
            const container = document.getElementById('recetasContainer');
            container.appendChild(crearRecetaCard(recetaIndex));
            recetaIndex++;
        });

        document.getElementById('recetasContainer').addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-remove-receta')) {
                e.target.closest('.receta-card').remove();
            }
        });

        document.getElementById('recetasContainer').addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-agregar-med-receta')) {
                const card = e.target.closest('.receta-card');
                const ri = card.querySelector('input[name$="[id]"]').name.match(/recetas\[(\d+)\]/)[1];
                const medContainer = card.querySelector('.medicamentos-container');
                const mi = medContainer.querySelectorAll('.medicamento-row').length;
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 medicamento-row align-items-end';
                row.innerHTML = `
                    <input type="hidden" name="recetas[${ri}][medicamentos][${mi}][id]" value="">
                    <div class="col-md-3">
                        <input type="text" name="recetas[${ri}][medicamentos][${mi}][nombre]" class="neu-input form-control" placeholder="Medicamento">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="recetas[${ri}][medicamentos][${mi}][dosis]" class="neu-input form-control" placeholder="Dosis">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="recetas[${ri}][medicamentos][${mi}][frecuencia]" class="neu-input form-control" placeholder="Frecuencia">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="recetas[${ri}][medicamentos][${mi}][duracion]" class="neu-input form-control" placeholder="Duración">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="recetas[${ri}][medicamentos][${mi}][indicaciones]" class="neu-input form-control" placeholder="Indicaciones">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="neu-btn neu-btn-sm btn-remove-med-receta" style="background:#e74c3c;color:#fff;border:none;">×</button>
                    </div>
                `;
                medContainer.appendChild(row);
            }
        });

        document.getElementById('recetasContainer').addEventListener('click', function (e) {
            if (e.target.classList.contains('btn-remove-med-receta')) {
                e.target.closest('.medicamento-row').remove();
            }
        });
    });
</script>
@endpush
@endsection
