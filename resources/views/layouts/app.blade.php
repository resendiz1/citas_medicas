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
    @endauth
    @php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
    $jsFile = $manifest['resources/js/app.js']['file'] ?? '';
    @endphp
    <link rel="stylesheet" href="/build/{{ $cssFile }}">
    <script src="/build/{{ $jsFile }}" defer></script>
    <style>
    .nav-icon-only .nav-text { display:inline-block; white-space:nowrap; max-width:0; opacity:0; overflow:hidden; vertical-align:middle; transition:max-width 0.25s ease, opacity 0.2s ease; }
    .nav-icon-only .nav-link { display:flex; align-items:center; gap:2px; }
    .nav-icon-only .nav-item:hover .nav-text { max-width:200px; opacity:1; }
    .nav-icon-only .dropdown-toggle::after { display:none !important; }
    .nav-icon-only .nav-text-caret { font-size:0.55rem; margin-left:1px; }
    @media (min-width: 992px) {
        .navbar-expand-lg .navbar-collapse.show { display:flex !important; }
    }
    </style>
    @stack('head')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-2">
        <div class="container">
            <a class="navbar-brand fw-bold me-3 d-flex flex-column align-items-start lh-1 text-white" href="{{ route('dashboard') }}">Citas Médicas<small style="font-size:0.6rem;font-weight:400;opacity:0.7">&lt;juanPancho's&gt;</small></a>
            @if (!request()->routeIs('login'))
            <button class="navbar-toggler border-0" type="button" onclick="event.stopPropagation();document.getElementById('navbarNav').classList.toggle('show');this.classList.toggle('collapsed')" aria-label="Toggle navigation">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            @endif
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 nav-icon-only">
                    @auth
                            <li class="nav-item"><a class="nav-link text-white{{ request()->routeIs('dashboard') ? ' active' : '' }}" href="{{ route('dashboard') }}"><i class="fa fa-house"></i><span class="text-white"> Inicio</span></a></li>
                            @if (auth()->user()->esMedico())
                            <li class="nav-item"><a class="nav-link text-white{{ request()->routeIs('medico.chat-ia*') ? ' active' : '' }}" href="{{ route('medico.chat-ia') }}"><i class="fa fa-robot"></i><span class="text-white"> Asistente IA</span></a></li>
                            @endif
                            @if (!auth()->user()->esMedico())
                            <li class="nav-item"><a class="nav-link text-white{{ request()->routeIs('estadisticas.*') ? ' active' : '' }}" href="{{ route('estadisticas.index') }}"><i class="fa fa-chart-simple"></i><span class="text-white"> Estadísticas</span></a></li>
                            @endif
                            @if (auth()->user()->esPaciente())
                            <li class="nav-item"><a class="nav-link text-white{{ request()->routeIs('paciente.chat-ia*') ? ' active' : '' }}" href="{{ route('paciente.chat-ia.index') }}"><i class="fa fa-robot"></i><span class="text-white"> Asistente IA</span></a></li>
                            @endif
                        @if (auth()->user()->esAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white{{ request()->routeIs('admin.*') ? ' active' : '' }}" href="#"><i class="fa fa-screwdriver-wrench"></i><span class="text-white"> Gestión<i class="fa fa-caret-down nav-text-caret"></i></span></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.citas') ? ' active' : '' }}" href="{{ route('admin.citas') }}"><i class="fa fa-calendar me-1"></i>Citas</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.medicos*') ? ' active' : '' }}" href="{{ route('admin.medicos') }}"><i class="fa fa-user-doctor me-1"></i>Médicos</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.pacientes*') ? ' active' : '' }}" href="{{ route('admin.pacientes') }}"><i class="fa fa-user me-1"></i>Pacientes</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('admin.logs') ? ' active' : '' }}" href="{{ route('admin.logs') }}"><i class="fa fa-clipboard-list me-1"></i>Logs</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->esAdmin())
                            <li class="nav-item"><a class="nav-link text-white{{ request()->routeIs('admin.bug-reports*') ? ' active' : '' }}" href="{{ route('admin.bug-reports') }}"><i class="fa fa-bug"></i><span class="text-white"> Reportes</span></a></li>
                        @endif
                        @if (auth()->user()->esMedico())
                            <li class="nav-item"><a class="nav-link text-white{{ request()->routeIs('medico.pacientes*') ? ' active' : '' }}" href="{{ route('medico.pacientes.index') }}"><i class="fa fa-users"></i><span class="text-white"> Pacientes</span></a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white{{ request()->routeIs('medico.citas.create') || request()->routeIs('medico.historial-citas') || request()->routeIs('estadisticas.*') ? ' active' : '' }}" href="#"><i class="fa fa-calendar"></i><span class="text-white"> Citas<i class="fa fa-caret-down nav-text-caret"></i></span></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item{{ request()->routeIs('medico.citas.create') ? ' active' : '' }}" href="{{ route('medico.citas.create') }}"><i class="fa fa-calendar-plus me-1"></i>Agendar cita</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('medico.historial-citas') ? ' active' : '' }}" href="{{ route('medico.historial-citas') }}"><i class="fa fa-clock-rotate-left me-1"></i>Historial de citas</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('estadisticas.*') ? ' active' : '' }}" href="{{ route('estadisticas.index') }}"><i class="fa fa-chart-simple me-1"></i>Estadísticas</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white{{ request()->routeIs('medico.horarios*') || request()->routeIs('medico.bloqueos*') ? ' active' : '' }}" href="#"><i class="fa fa-calendar-days"></i><span class="text-white"> Mi agenda<i class="fa fa-caret-down nav-text-caret"></i></span></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item{{ request()->routeIs('medico.horarios*') ? ' active' : '' }}" href="{{ route('medico.horarios') }}"><i class="fa fa-clock me-1"></i>Horarios</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('medico.bloqueos*') ? ' active' : '' }}" href="{{ route('medico.bloqueos') }}"><i class="fa fa-ban me-1"></i>Bloqueos</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->esMedico() || auth()->user()->esPaciente())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white{{ request()->routeIs('ayuda') || request()->routeIs('bug-report.*') ? ' active' : '' }}" href="#"><i class="fa fa-headset"></i><span class="text-white"> Soporte<i class="fa fa-caret-down nav-text-caret"></i></span></a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item{{ request()->routeIs('ayuda') ? ' active' : '' }}" href="{{ route('ayuda') }}"><i class="fa fa-headset me-1"></i>Ayuda</a></li>
                                    <li><a class="dropdown-item{{ request()->routeIs('bug-report.*') ? ' active' : '' }}" href="{{ route('bug-report.create') }}"><i class="fa fa-bug me-1"></i>Reportar bug</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item dropdown mt-3" id="notif-nav">
                            <a class="nav-link dropdown-toggle d-flex align-items-center text-white position-relative" href="#" id="notif-bell">
                                <i class="fa fa-bell fa-lg"></i>
                                <span id="notif-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.55rem;display:none">0</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" id="notif-dropdown" style="width:420px;max-height:450px;overflow-y:auto">
                                <li class="dropdown-header text-muted small">Notificaciones</li>
                                <div id="notif-list"><li><span class="dropdown-item-text small text-muted">Cargando...</span></li></div>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item small text-center" href="{{ route('notificaciones.index') }}">Ver todas</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center text-white" href="#">
                                <span class="me-2">{{ auth()->user()->name }}</span>
                                @switch(auth()->user()->role)
                                    @case('admin') <span class="badge border border-2 border-warning text-white bg-transparent px-3 py-2"><i class="fa fa-shield-halved me-1"></i>Admin</span> @break
                                    @case('administrador') <span class="badge border border-2 border-warning text-white bg-transparent px-3 py-2"><i class="fa fa-shield me-1"></i>Admin</span> @break
                                    @case('medico') <span class="badge border border-2 border-success text-white bg-transparent px-3 py-2"><i class="fa fa-user-doctor me-1"></i>Médico</span> @break
                                    @case('paciente') <span class="badge border border-2 border-primary text-white bg-transparent px-3 py-2"><i class="fa fa-user me-1"></i>Paciente</span> @break
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

    @endauth
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>if(typeof marked!=='undefined'){marked.setOptions({breaks:true,gfm:true})}</script>
    @stack('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var bell = document.getElementById('notif-bell');
        var badge = document.getElementById('notif-badge');
        var list = document.getElementById('notif-list');
        var notifNav = document.getElementById('notif-nav');

        function cargarNotificaciones() {
            fetch('{{ route('notificaciones.dropdown') }}')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.unread > 0) {
                        badge.textContent = data.unread > 99 ? '99+' : data.unread;
                        badge.style.display = 'inline';
                    } else {
                        badge.style.display = 'none';
                    }
                    if (data.items.length === 0) {
                        list.innerHTML = '<li><span class="dropdown-item-text small text-muted">Sin notificaciones</span></li>';
                        return;
                    }
                    list.innerHTML = data.items.map(function (n) {
                        var clase = n.read_at ? 'text-muted' : 'fw-bold';
                        var color = n.estado ? colorEstado(n.estado) : '';
                        var dot = n.estado ? '<span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:' + color + '"></span>' : '';
                        return '<li><a class="dropdown-item small ' + clase + '" href="#" data-id="' + n.id + '" data-cita="' + (n.cita_id || '') + '">' + dot + escapeHtml(n.message) + ' <small class="text-muted">' + (n.estado || '') + '</small><br><small class="text-muted">' + n.time + '</small></a></li>';
                    }).join('');
                    list.querySelectorAll('a[data-id]').forEach(function (el) {
                        el.addEventListener('click', function (e) {
                            e.preventDefault();
                            var id = this.dataset.id;
                            var citaId = this.dataset.cita;
                            fetch('/notificaciones/' + id + '/leida', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') } });
                            this.classList.remove('fw-bold');
                            this.classList.add('text-muted');
                            if (citaId) window.location.href = '/citas/' + citaId;
                        });
                    });
                });
        }

        notifNav.addEventListener('click', function (e) {
            if (e.target.closest('#notif-list a')) return;
            cargarNotificaciones();
        });
        cargarNotificaciones();
        setInterval(cargarNotificaciones, 15000);
    });

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function colorEstado(estado) {
        var colores = {
            'pendiente': '#ffc107',
            'confirmada': '#00b894',
            'en_espera': '#0dcaf0',
            'en_consulta': '#0d6efd',
            'finalizada': '#6c757d',
            'cancelada': '#dc3545',
            'no_asistio': '#dc143c',
            'reprogramada': '#fd7e14'
        };
        return colores[estado] || '#6c757d';
    }

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
