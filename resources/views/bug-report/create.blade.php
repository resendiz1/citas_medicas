@extends('layouts.app')

@section('title', 'Reportar un problema')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-2 p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color:#1266f1"><i class="fa fa-bug me-2"></i>Reportar un problema</h5>
                <p class="text-muted mb-4">¿Encontraste un error o algo no funciona como debería? Cuéntanos los detalles para solucionarlo lo antes posible.</p>

                <form method="POST" action="{{ route('bug-report.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="categoria" class="form-label fw-semibold">Categoría</label>
                        <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                            <option value="">Selecciona una categoría</option>
                            <option value="general" @selected(old('categoria')=='general')>General</option>
                            <option value="error_visual" @selected(old('categoria')=='error_visual')>Error visual / UI</option>
                            <option value="funcionalidad" @selected(old('categoria')=='funcionalidad')>Funcionalidad</option>
                            <option value="rendimiento" @selected(old('categoria')=='rendimiento')>Rendimiento</option>
                            <option value="seguridad" @selected(old('categoria')=='seguridad')>Seguridad</option>
                            <option value="otro" @selected(old('categoria')=='otro')>Otro</option>
                        </select>
                        @error('categoria')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-semibold">Título</label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}" placeholder="Ej: El botón de guardar no responde" required maxlength="255">
                        @error('titulo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="6" class="form-control @error('descripcion') is-invalid @enderror" placeholder="Explica qué pasó, qué esperabas que ocurriera, y cómo reproducirlo..." required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane me-1"></i>Enviar reporte</button>
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
