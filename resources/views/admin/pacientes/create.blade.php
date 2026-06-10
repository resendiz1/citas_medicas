@extends('layouts.app')

@section('title', 'Nuevo Paciente')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="neu-card p-4">
                <h4 class="mb-4 fw-bold">Nuevo Paciente</h4>
                    <form method="POST" action="{{ route('admin.pacientes.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="name" class="neu-input form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="neu-input form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="neu-input form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Fecha de nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="neu-input form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento') }}">
                            @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="neu-input form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}">
                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="neu-textarea form-control @error('direccion') is-invalid @enderror" rows="2">{{ old('direccion') }}</textarea>
                            @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="neu-textarea form-control @error('observaciones') is-invalid @enderror" rows="2">{{ old('observaciones') }}</textarea>
                            @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.pacientes') }}" class="neu-btn">Cancelar</a>
                            <button type="submit" class="neu-btn neu-btn-primary">Guardar</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
