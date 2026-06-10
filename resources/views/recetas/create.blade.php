@extends('layouts.app')

@section('title', 'Generar Receta')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Generar Receta Médica</h4>
        <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm" style="color:var(--yellow)">&larr; Volver</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Datos de la cita</h5>
                <table class="table neu-table align-middle mb-0">
                    <tbody>
                        <tr><th style="width:120px">Paciente</th><td style="color:var(--text-emphasis);font-weight:500">{{ $cita->paciente->name }}</td></tr>
                        <tr><th>Fecha</th><td style="color:var(--text-emphasis);font-weight:500">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Motivo</th><td style="color:var(--text-primary)">{{ $cita->motivo }}</td></tr>
                    </tbody>
                </table>
                <br><br><br><br>
            </div>
        </div>

        <div class="col-md-7">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Receta</h5>

                <form method="POST" action="{{ route('recetas.store', $cita->id) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="diagnostico" class="form-label">Diagnóstico</label>
                        <textarea id="diagnostico" name="diagnostico" rows="3"
                                  class="neu-textarea form-control @error('diagnostico') is-invalid @enderror"
                                  required>{{ old('diagnostico') }}</textarea>
                        @error('diagnostico') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="indicaciones_generales" class="form-label">Indicaciones generales</label>
                        <textarea id="indicaciones_generales" name="indicaciones_generales" rows="3"
                                  class="neu-textarea form-control @error('indicaciones_generales') is-invalid @enderror"
                                  required>{{ old('indicaciones_generales') }}</textarea>
                        @error('indicaciones_generales') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Medicamentos</label>
                        <div id="medicamentos-wrapper">
                            <div class="row g-2 mb-2 medicamento-row">
                                <div class="col-md-4">
                                    <input type="text" name="medicamentos[0][nombre]" class="neu-input form-control" placeholder="Medicamento">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="medicamentos[0][dosis]" class="neu-input form-control" placeholder="Dosis">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="medicamentos[0][frecuencia]" class="neu-input form-control" placeholder="Frecuencia">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="medicamentos[0][duracion]" class="neu-input form-control" placeholder="Duración">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="neu-btn neu-btn-sm neu-btn-primary w-100" onclick="agregarMedicamento()" style="font-size:0.75rem">+</button>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Agrega uno o más medicamentos con su dosis, frecuencia y duración.</small>
                    </div>

                    <div class="mb-4">
                        <label for="notas" class="form-label">Notas adicionales</label>
                        <textarea id="notas" name="notas" rows="2"
                                  class="neu-textarea form-control @error('notas') is-invalid @enderror">{{ old('notas') }}</textarea>
                        @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="documentos" class="form-label">Documentos adjuntos (imágenes o PDF)</label>
                        <input type="file" id="documentos" name="documentos[]" multiple
                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                               class="neu-input form-control @error('documentos.*') is-invalid @enderror">
                        <small class="text-muted">Máximo 10 MB por archivo. Formatos: JPG, PNG, GIF, WebP, PDF.</small>
                        @error('documentos.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard') }}" class="neu-btn">Cancelar</a>
                        <button type="submit" class="neu-btn neu-btn-primary">Guardar receta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let medIndex = 1;
function agregarMedicamento() {
    const wrapper = document.getElementById('medicamentos-wrapper');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 medicamento-row';
    row.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="medicamentos[${medIndex}][nombre]" class="neu-input form-control" placeholder="Medicamento">
        </div>
        <div class="col-md-2">
            <input type="text" name="medicamentos[${medIndex}][dosis]" class="neu-input form-control" placeholder="Dosis">
        </div>
        <div class="col-md-2">
            <input type="text" name="medicamentos[${medIndex}][frecuencia]" class="neu-input form-control" placeholder="Frecuencia">
        </div>
        <div class="col-md-2">
            <input type="text" name="medicamentos[${medIndex}][duracion]" class="neu-input form-control" placeholder="Duración">
        </div>
        <div class="col-md-2">
            <button type="button" class="neu-btn neu-btn-sm neu-btn-danger w-100" onclick="this.parentElement.parentElement.remove()" style="font-size:0.75rem">×</button>
        </div>
    `;
    wrapper.appendChild(row);
    medIndex++;
}
</script>
@endpush
