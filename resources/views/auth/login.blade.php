@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="neu-card p-4 mt-5">
                <h4 class="text-center mb-4">Iniciar sesión</h4>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" id="email" name="email"
                                   class="neu-input form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" id="password" name="password"
                                   class="neu-input form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Recordarme</label>
                        </div>

                        <button type="submit" class="neu-btn neu-btn-primary w-100 mb-3">Entrar</button>

                        <p class="text-center mb-0">
                            ¿No tienes cuenta?
                            <a href="{{ route('register') }}">Regístrate</a>
                        </p>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
