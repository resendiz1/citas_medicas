<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @auth
        <meta name="notificaciones-poll" content="{{ route('notificaciones.poll') }}">
        <meta name="user-id" content="{{ auth()->id() }}">
        <meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
        <meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.options.host', 'localhost') }}">
        <meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.options.port', 8080) }}">
        <meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.options.scheme', 'http') }}">
        <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY') }}">
        <link rel="manifest" href="/manifest.json">
    @endauth
    @php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
    $jsFile = $manifest['resources/js/app.js']['file'] ?? '';
    @endphp
    <link rel="stylesheet" href="/build/{{ $cssFile }}">
    <script src="/build/{{ $jsFile }}" defer></script>
    @stack('head')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-2">
        <div class="container">
            <a class="navbar-brand fw-bold me-3 d-flex flex-column align-items-start lh-1 text-white" href="{{ route('dashboard') }}">Citas Médicas<small style="font-size:0.6rem;font-weight:400;opacity:0.7">&lt;juanPancho's&gt;</small></a>
            <button class="navbar-toggler border-0" type="button" onclick="event.stopPropagation();document.getElementById('navbarNav').classList.toggle('show');this.classList.toggle('collapsed')" aria-label="Toggle navigation">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                            <li class="nav-item"><a class="nav-link{{ request()->routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}"><i class="fa fa-house me-1"></i>Inicio</a></li>
                            <li class="nav-item"><a class="nav-link{{ request()->routeIs('estadisticas.*') ? ' active' : '' }}" href="{{ route('estadisticas.index') }}"><i class="fa fa-chart-simple me-1"></i>Estadísticas</a></li>
                        @if (auth()->user()->esAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle{{ request()->routeIs('admin.*') ? ' active' : '' }}" href="#" data-mdb-toggle="dropdown"><i class="fa fa-screwdriver-wrench me-1"></i>Gestión</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.citas') ? ' active' : '' }}" href="{{ route('admin.citas') }}"><i class="fa fa-calendar me-1"></i>Citas</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.medicos*') ? ' active' : '' }}" href="{{ route('admin.medicos') }}"><i class="fa fa-user-doctor me-1"></i>Médicos</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.pacientes*') ? ' active' : '' }}" href="{{ route('admin.pacientes') }}"><i class="fa fa-user me-1"></i>Pacientes</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->esMedico())
                            <li class="nav-item"><a class="nav-link{{ request()->routeIs('medico.historial-citas') ? ' active' : '' }}" href="{{ route('medico.historial-citas') }}"><i class="fa fa-clock-rotate-left me-1"></i>Historial de citas</a></li>
                            <li class="nav-item"><a class="nav-link{{ request()->routeIs('medico.chat-ia*') ? ' active' : '' }}" href="{{ route('medico.chat-ia') }}"><i class="fa fa-robot me-1"></i>Asistente IA</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle{{ request()->routeIs('medico.horarios*') || request()->routeIs('medico.bloqueos*') ? ' active' : '' }}" href="#" data-mdb-toggle="dropdown"><i class="fa fa-calendar-days me-1"></i>Mi agenda</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item{{ request()->routeIs('medico.horarios*') ? ' active' : '' }}" href="{{ route('medico.horarios') }}"><i class="fa fa-clock me-1"></i>Horarios</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('medico.bloqueos*') ? ' active' : '' }}" href="{{ route('medico.bloqueos') }}"><i class="fa fa-ban me-1"></i>Bloqueos</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->esMedico() || auth()->user()->esPaciente())
                            <li class="nav-item"><a class="nav-link{{ request()->routeIs('ayuda') ? ' active' : '' }}" href="{{ route('ayuda') }}"><i class="fa fa-headset me-1"></i>Ayuda</a></li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-mdb-toggle="dropdown">
                                <span class="me-2">{{ auth()->user()->name }}</span>
                                @switch(auth()->user()->role)
                                    @case('admin') <span class="badge border border-2 border-warning text-white bg-transparent px-3 py-2"><i class="fa fa-shield-halved me-1"></i>Admin</span> @break
                                    @case('medico') <span class="badge border border-2 border-success text-white bg-transparent px-3 py-2"><i class="fa fa-user-doctor me-1"></i>Médico</span> @break
                                    @case('paciente') <span class="badge border border-2 border-primary text-white bg-transparent px-3 py-2"><i class="fa fa-user me-1"></i>Paciente</span> @break
                                    @case('recepcionista') <span class="badge border border-2 border-info text-white bg-transparent px-3 py-2"><i class="fa fa-phone me-1"></i>Recepcionista</span> @break
                                @endswitch
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a></li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @auth
        @php $incompleteReason = auth()->user()->getProfileIncompleteReason(); @endphp
        @if ($incompleteReason)
            @php
                $profileRoute = auth()->user()->esMedico() ? route('medico.perfil') : route('paciente.perfil');
            @endphp
            <div class="alert alert-warning mb-0 rounded-0 text-center" role="alert" style="border-bottom:2px solid #ffc107">
                <i class="fa fa-circle-exclamation me-1"></i>
                Tu perfil está incompleto: <strong>{{ $incompleteReason }}</strong>.
                <a href="{{ $profileRoute }}" class="alert-link ms-1">Completar perfil <i class="fa fa-arrow-right"></i></a>
            </div>
        @endif
    @endauth

    <main class="py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-mdb-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif
        @yield('content')
    </main>

    @auth
        @if (auth()->user()->esPaciente() || auth()->user()->esMedico())
            @include('partials.chat-widget')
        @endif
        @if (auth()->user()->esPaciente())
            @include('partials.ia-chat-widget')
        @endif
    @endauth
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>if(typeof marked!=='undefined'){marked.setOptions({breaks:true,gfm:true})}</script>
    @stack('scripts')
    <script>
    document.addEventListener('click', function (e) {
        const nav = document.getElementById('navbarNav');
        const toggler = document.querySelector('.navbar-toggler');
        if (nav && nav.classList.contains('show') && !nav.contains(e.target) && !toggler?.contains(e.target)) {
            nav.classList.remove('show');
            toggler?.classList.remove('collapsed');
        }
    });
    document.querySelectorAll('.btn-text').forEach(function(el) {
        var btn = el.closest('button, a');
        if (btn && !btn.hasAttribute('title')) {
            btn.setAttribute('title', el.textContent.trim());
        }
    });
    </script>
</body>
</html>
