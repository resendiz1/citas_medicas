@php $user = auth()->user(); @endphp
@if ($user->esMedico())
@php
    $esHoy = $cita->fecha_hora->isToday();
    $disabledCls = 'dropdown-item-text text-muted';
@endphp
<div style="position:relative;display:inline-block" data-drop-wrap="{{ $cita->id }}">
    <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" type="button" onclick="toggleDrop({{ $cita->id }})" style="border:none;background:transparent;box-shadow:none">
        <i class="fa fa-ellipsis-vertical"></i>
    </button>
    <div data-drop-menu="{{ $cita->id }}" class="shadow-2" style="display:none;position:fixed;min-width:190px;font-size:0.85rem;z-index:99999;background:#fff;border:1px solid rgba(0,0,0,.15);border-radius:0.375rem;padding:0.5rem 0">
    <ul style="list-style:none;padding:0;margin:0">
    @if ($cita->estado === 'pendiente')
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="confirmada">
                <button type="submit" class="dropdown-item"><i class="fa fa-check-circle fa-fw me-1" style="color:#00b894"></i> Confirmar</button>
            </form>
        </li>
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem"><i class="fa fa-clock fa-fw me-1 text-muted"></i> En espera <small class="text-muted">(confirmar primero)</small></span></li>
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Cancelar esta cita?')">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="cancelada">
                <button type="submit" class="dropdown-item"><i class="fa fa-circle-xmark fa-fw me-1" style="color:#ff4444"></i> Cancelar</button>
            </form>
        </li>
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem"><i class="fa fa-user-slash fa-fw me-1 text-muted"></i> No asistió <small class="text-muted">(confirmar primero)</small></span></li>
        <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
        <li>
            <button type="button" class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
                <i class="fa fa-calendar fa-fw me-1" style="color:#9370db"></i> Reprogramar
            </button>
        </li>
    @elseif ($cita->estado === 'confirmada')
        @if ($esHoy)
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="en_espera">
                <button type="submit" class="dropdown-item"><i class="fa fa-clock fa-fw me-1" style="color:#ffa500"></i> En espera</button>
            </form>
        </li>
        @else
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem"><i class="fa fa-clock fa-fw me-1 text-muted"></i> En espera <small class="text-muted">(solo el día de la cita)</small></span></li>
        @endif
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Cancelar esta cita?')">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="cancelada">
                <button type="submit" class="dropdown-item"><i class="fa fa-circle-xmark fa-fw me-1" style="color:#ff4444"></i> Cancelar</button>
            </form>
        </li>
        @if ($esHoy)
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Marcar como no asistió?')">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="no_asistio">
                <button type="submit" class="dropdown-item"><i class="fa fa-user-slash fa-fw me-1" style="color:#dc143c"></i> No asistió</button>
            </form>
        </li>
        @else
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem"><i class="fa fa-user-slash fa-fw me-1 text-muted"></i> No asistió <small class="text-muted">(solo el día de la cita)</small></span></li>
        @endif
        <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
        <li>
            <button type="button" class="dropdown-item" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
                <i class="fa fa-calendar fa-fw me-1" style="color:#9370db"></i> Reprogramar
            </button>
        </li>
    @elseif ($cita->estado === 'en_espera')
        @if ($esHoy)
        <li>
            <a href="{{ route('consulta-medica.create', $cita->id) }}" class="dropdown-item"><i class="fa fa-stethoscope fa-fw me-1" style="color:#1e90ff"></i> En consulta</a>
        </li>
        @else
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem"><i class="fa fa-stethoscope fa-fw me-1 text-muted"></i> En consulta <small class="text-muted">(solo el día de la cita)</small></span></li>
        @endif
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Cancelar esta cita?')">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="cancelada">
                <button type="submit" class="dropdown-item"><i class="fa fa-circle-xmark fa-fw me-1" style="color:#ff4444"></i> Cancelar</button>
            </form>
        </li>
        @if ($esHoy)
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Marcar como no asistió?')">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="no_asistio">
                <button type="submit" class="dropdown-item"><i class="fa fa-user-slash fa-fw me-1" style="color:#dc143c"></i> No asistió</button>
            </form>
        </li>
        @else
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem"><i class="fa fa-user-slash fa-fw me-1 text-muted"></i> No asistió <small class="text-muted">(solo el día de la cita)</small></span></li>
        @endif
    @elseif ($cita->estado === 'en_consulta')
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="finalizada">
                <button type="submit" class="dropdown-item"><i class="fa fa-check-double fa-fw me-1" style="color:#555"></i> Finalizar</button>
            </form>
        </li>
    @elseif ($cita->estado === 'reprogramada')
        <li>
            <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline w-100">
                @csrf @method('PUT')
                <input type="hidden" name="estado" value="confirmada">
                <button type="submit" class="dropdown-item"><i class="fa fa-check-circle fa-fw me-1" style="color:#00b894"></i> Confirmar</button>
            </form>
        </li>
    @elseif (in_array($cita->estado, ['cancelada', 'no_asistio']))
        <li><span class="{{ $disabledCls }}" style="font-size:0.75rem">Sin acciones disponibles</span></li>
    @endif
    @if (!in_array($cita->estado, ['cancelada', 'no_asistio']))
    <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
    <li><a class="dropdown-item" href="{{ route('medico.paciente.show', $cita->paciente->id) }}"><i class="fa fa-user fa-fw me-1" style="color:#1266f1"></i> Perfil del paciente</a></li>
    <li><button class="dropdown-item" type="button" onclick="abrirChatCita({{ $cita->id }})"><i class="fa fa-comment-dots fa-fw me-1" style="color:#1266f1"></i> Chat</button></li>
    @if ($cita->consultaMedica)
        <li><a class="dropdown-item" href="{{ route('consulta-medica.show', $cita->id) }}"><i class="fa fa-stethoscope fa-fw me-1" style="color:#00b894"></i> Ver consulta</a></li>
    @elseif ($cita->estado === 'confirmada' && $esHoy)
        <li><a class="dropdown-item" href="{{ route('consulta-medica.create', $cita->id) }}"><i class="fa fa-plus fa-fw me-1" style="color:#00b894"></i> + Consulta</a></li>
    @endif
    @if ($cita->ultimaReceta)
        <li><a class="dropdown-item" href="{{ route('recetas.show', $cita->ultimaReceta->id) }}"><i class="fa fa-prescription fa-fw me-1" style="color:#ffa500"></i> Ver receta</a></li>
    @endif
    @endif
    </ul>
    </div>
</div>
@elseif ($user->esPaciente())
<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-eye me-1"></i><span class="btn-text">Ver detalles</span></a>
    <button type="button" class="btn btn-primary btn-sm" onclick="abrirChatCita({{ $cita->id }})"><i class="fa fa-comment-dots me-1"></i><span class="btn-text">Chat</span></button>
    @if ($cita->estado === 'pendiente')
    <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
        @csrf @method('PUT')
        <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i><span class="btn-text">Cancelar</span></button>
    </form>
    @elseif ($cita->estado === 'reprogramada' && $cita->fecha_reprogramada)
        <span class="text-muted" style="font-size:0.7rem;width:100%;margin-bottom:2px">
            Nueva fecha propuesta: {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}
        </span>
        <form action="{{ route('citas.reprogramacion.confirmar', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Confirmar la reprogramación para el {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}?')">
            @csrf
            <button class="btn btn-success btn-sm"><i class="fa fa-circle-check me-1"></i><span class="btn-text">Aceptar</span></button>
        </form>
        <form action="{{ route('citas.reprogramacion.cancelar', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Rechazar la reprogramación? La cita mantendrá su fecha original.')">
            @csrf
            <button class="btn btn-danger btn-sm"><i class="fa fa-ban me-1"></i><span class="btn-text">Rechazar</span></button>
        </form>
    @endif
</div>
@else
<div class="d-flex flex-wrap gap-1" style="max-width:200px">
    @if ($cita->estado === 'pendiente')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="btn btn-success btn-sm"><i class="fa fa-check-circle me-1"></i><span class="btn-text">Confirmar</span></button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i><span class="btn-text">Cancelar</span></button>
        </form>
    @elseif ($cita->estado === 'confirmada')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_espera">
            <button class="btn btn-warning btn-sm"><i class="fa fa-clock me-1"></i><span class="btn-text">En espera</span></button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i><span class="btn-text">Cancelar</span></button>
        </form>
    @elseif ($cita->estado === 'en_espera')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_consulta">
            <button class="btn btn-primary btn-sm"><i class="fa fa-stethoscope me-1"></i><span class="btn-text">En consulta</span></button>
        </form>
    @elseif ($cita->estado === 'en_consulta')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="finalizada">
            <button class="btn btn-secondary btn-sm"><i class="fa fa-check-double me-1"></i><span class="btn-text">Finalizar</span></button>
        </form>
    @else
        <span class="text-muted" style="font-size:0.75rem">—</span>
    @endif
</div>
@endif