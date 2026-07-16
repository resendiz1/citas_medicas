@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('content')
<div class="container-fluid min-vh-100 px-0">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-10">
            <div class="row g-0">
                <div id="loginFormCol" class="col-12 col-lg-6 login-col d-flex align-items-center justify-content-center p-4 p-lg-5 bg-light" style="border-radius:12px 0 0 12px">
                    <div class="w-100" style="max-width:420px">
                        <div class="card shadow-2 p-4">
                            <div class="text-center mb-3"><img src="/logo.png" alt="Logo" class="img-fluid"></div>
                            <h4 class="text-center mb-4">Iniciar sesión</h4>
                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-4">
                                    <label for="email" class="form-label">Correo electrónico</label>
                                    <input type="email" id="email" name="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" id="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Recordarme</label>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fa fa-right-to-bracket me-1"></i><span class="btn-text">Entrar</span></button>

                                <div class="text-center mb-3">
                                    <small class="text-muted">o</small>
                                </div>

                                <a href="{{ route('google.redirect') }}" class="btn w-100 mb-3" style="background:#fff;color:#1a1a1a;border:1px solid #dadce0;font-weight:500">
                                    <i class="fa-brands fa-google me-2" style="background:linear-gradient(to bottom,#4285F4 25%,#EA4335 25%,#EA4335 50%,#FBBC05 50%,#FBBC05 75%,#34A853 75%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent"></i><span class="btn-text">Continuar con Google</span>
                                </a>

                                <p class="text-center mb-0">
                                    ¿No tienes cuenta?
                                    <a href="{{ route('register') }}">Regístrate</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="loginInfoCol" class="col-12 col-lg-6 login-col d-flex flex-column justify-content-center p-4 p-lg-5" style="background:linear-gradient(135deg, #3b71ca 0%, #1a3d7c 100%);color:#fff;min-height:50vh;border-radius:0 12px 12px 0">
                    <div class="mx-auto" style="max-width:480px">
                        <div class="mb-4">
                            <h2 class="fw-bold mb-2" style="font-size:2rem">Citas Médicas</h2>
                            <p class="mb-0" style="font-size:0.85rem;opacity:0.85">&lt;JuanPancho's/&gt;</p>
                        </div>
                        <p style="font-size:1rem;opacity:0.95;line-height:1.7">
                            Plataforma integral para la gestión de consultas médicas. Agenda, controla y da seguimiento a tus citas de forma rápida y segura.
                        </p>
                        <hr style="border-color:rgba(255,255,255,0.2);margin:1.5rem 0">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fa fa-calendar-check mt-1 me-3" style="font-size:1.3rem;opacity:0.9"></i>
                                    <div>
                                        <strong style="font-size:0.9rem">Agenda tu cita</strong>
                                        <p class="mb-0" style="font-size:0.78rem;opacity:0.75">Selecciona médico, fecha y motivo en pocos pasos.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fa fa-user-md mt-1 me-3" style="font-size:1.3rem;opacity:0.9"></i>
                                    <div>
                                        <strong style="font-size:0.9rem">Perfiles clínicos</strong>
                                        <p class="mb-0" style="font-size:0.78rem;opacity:0.75">Historial de consultas, recetas y diagnósticos.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fa fa-notes-medical mt-1 me-3" style="font-size:1.3rem;opacity:0.9"></i>
                                    <div>
                                        <strong style="font-size:0.9rem">Recetas digitales</strong>
                                        <p class="mb-0" style="font-size:0.78rem;opacity:0.75">Genera y descarga recetas con medicamentos y documentos.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fa fa-comments mt-1 me-3" style="font-size:1.3rem;opacity:0.9"></i>
                                    <div>
                                        <strong style="font-size:0.9rem">Chat integrado</strong>
                                        <p class="mb-0" style="font-size:0.78rem;opacity:0.75">Comunicación directa con tu médico en tiempo real.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr style="border-color:rgba(255,255,255,0.2);margin:1.5rem 0">
                        <div class="small" style="opacity:0.7;font-size:0.72rem">
                            <i class="fa fa-shield-halved me-1"></i> Datos protegidos · 
                            <i class="fa fa-clock me-1"></i> Disponible 24/7
                        </div>
                        <div class="mt-4 small" style="opacity:0.65;font-size:0.72rem">
                            <i class="fa fa-circle-info me-1"></i>¿Eres nuevo? 
                            <a href="{{ route('register') }}" class="text-white" style="text-decoration:underline;text-underline-offset:3px">Regístrate aquí</a>
                            — elige paciente o médico.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="col-12 col-md-10">
            <div class="text-center small text-muted py-3 px-2" style="border-top:1px solid rgba(0,0,0,0.06);">
                <div class="d-flex flex-wrap justify-content-center gap-3 gap-md-4">
                    <span><i class="fa-brands fa-whatsapp me-1" style="color:#25d366"></i>238 150 1369</span>
                    <span><i class="fa-brands fa-whatsapp me-1" style="color:#25d366"></i>221 195 9921</span>
                    <span><i class="fa fa-phone me-1" style="color:#1266f1"></i>238 127 1940</span>
                    <span><i class="fa fa-globe me-1"></i><a href="https://juanpanchoslandingadministrable-production.up.railway.app/" target="_blank" class="text-muted text-decoration-none">JuanPancho's</a></span>
                </div>
                <div class="mt-1" style="font-size:0.65rem;opacity:0.6">&copy; {{ date('Y') }} &lt;JuanPancho's/&gt; — Todos los derechos reservados</div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.login-col { opacity:0 }
</style>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
(function(){
    var animate = function() {
        if (typeof gsap === 'undefined') return;
        gsap.fromTo('#loginFormCol', {x: -80, opacity: 0}, {x: 0, opacity: 1, duration: 0.9, ease: 'power3.out'});
        gsap.fromTo('#loginInfoCol', {x: 80, opacity: 0}, {x: 0, opacity: 1, duration: 0.9, ease: 'power3.out', delay: 0.15});
        gsap.fromTo('#loginInfoCol .fa, #loginInfoCol strong, #loginInfoCol hr',
            {opacity: 0, y: 20}, {opacity: 1, y: 0, duration: 0.5, stagger: 0.06, delay: 0.4, ease: 'power2.out'}
        );
    };
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        animate();
    } else {
        document.addEventListener('DOMContentLoaded', animate);
    }
})();
</script>
<noscript><style>.login-col{opacity:1}</style></noscript>
@endpush

