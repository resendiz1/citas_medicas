@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="neu-card p-4 mt-5">
                <h4 class="text-center mb-4">Registro</h4>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">Nombre completo</label>
                            <input type="text" id="name" name="name"
                                   class="neu-input form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" id="email" name="email"
                                   class="neu-input form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" id="password" name="password"
                                   class="neu-input form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="neu-input form-control" required>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label">Tipo de usuario</label>
                            <select id="role" name="role"
                                    class="neu-select form-control @error('role') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                <option value="paciente" {{ old('role') === 'paciente' ? 'selected' : '' }}>Paciente</option>
                                <option value="medico" {{ old('role') === 'medico' ? 'selected' : '' }}>Médico</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="neu-btn neu-btn-primary w-100 mb-3">Crear cuenta</button>

                        <p class="text-center mb-0">
                            ¿Ya tienes cuenta?
                            <a href="{{ route('login') }}">Inicia sesión</a>
                        </p>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
