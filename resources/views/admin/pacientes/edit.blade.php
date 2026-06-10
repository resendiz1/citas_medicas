@extends('layouts.app')

@section('title', 'Editar Paciente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="neu-card p-4">
                <h4 class="mb-4 fw-bold">Editar Paciente</h4>
                    <form method="POST" action="{{ route('admin.pacientes.update', $paciente->id) }}">
                        @csrf @method('PUT')

                        <div class="mb-4">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="name" class="neu-input form-control @error('name') is-invalid @enderror" value="{{ old('name', $paciente->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="neu-input form-control @error('email') is-invalid @enderror" value="{{ old('email', $paciente->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña <small class="text-muted">(dejar vacío para no cambiar)</small></label>
                            <input type="password" name="password" class="neu-input form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="neu-input form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento?->format('Y-m-d')) }}">
                            @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="neu-input form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $paciente->telefono) }}">
                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="neu-textarea form-control @error('direccion') is-invalid @enderror" rows="2">{{ old('direccion', $paciente->direccion) }}</textarea>
                            @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="neu-textarea form-control @error('observaciones') is-invalid @enderror" rows="2">{{ old('observaciones', $paciente->observaciones) }}</textarea>
                            @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.pacientes') }}" class="neu-btn">Cancelar</a>
                            <button type="submit" class="neu-btn neu-btn-primary">Actualizar</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
