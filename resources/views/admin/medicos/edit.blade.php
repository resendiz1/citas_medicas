@extends('layouts.app')

@section('title', 'Editar Médico')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="neu-card p-4">
                <h4 class="mb-4 fw-bold">Editar Médico</h4>
                    <form method="POST" action="{{ route('admin.medicos.update', $medico->id) }}">
                        @csrf @method('PUT')

                        <div class="mb-4">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="name" class="neu-input form-control @error('name') is-invalid @enderror" value="{{ old('name', $medico->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="neu-input form-control @error('email') is-invalid @enderror" value="{{ old('email', $medico->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña <small class="text-muted">(dejar vacío para no cambiar)</small></label>
                            <input type="password" name="password" class="neu-input form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Especialidad</label>
                            <select name="tipo_medico_id" class="neu-select form-control @error('tipo_medico_id') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($tiposMedico as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_medico_id', $medico->medicoPerfil->tipo_medico_id ?? '') == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre_tipo_medico }}</option>
                                @endforeach
                            </select>
                            @error('tipo_medico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="neu-input form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $medico->telefono) }}">
                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Cédula profesional</label>
                            <input type="text" name="cedula_profesional" class="neu-input form-control @error('cedula_profesional') is-invalid @enderror" value="{{ old('cedula_profesional', $medico->medicoPerfil->cedula_profesional ?? '') }}">
                            @error('cedula_profesional') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Universidad</label>
                            <input type="text" name="universidad" class="neu-input form-control @error('universidad') is-invalid @enderror" value="{{ old('universidad', $medico->medicoPerfil->universidad ?? '') }}">
                            @error('universidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Años de experiencia</label>
                            <input type="number" name="experiencia_anios" class="neu-input form-control @error('experiencia_anios') is-invalid @enderror" value="{{ old('experiencia_anios', $medico->medicoPerfil->experiencia_anios ?? '') }}" min="0">
                            @error('experiencia_anios') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="activoSwitch" name="activo" value="1"
                                       {{ old('activo', $medico->medicoPerfil->activo ?? true) ? 'checked' : '' }}
                                       style="width:3rem;height:1.5rem;cursor:pointer;background:var(--neu-shadow-dark)">
                                <label class="form-label ms-2" for="activoSwitch">Médico activo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.medicos') }}" class="neu-btn">Cancelar</a>
                            <button type="submit" class="neu-btn neu-btn-primary">Actualizar</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
