@php $user = auth()->user(); @endphp
@if ($user->esMedico())
<div class="d-flex flex-wrap gap-1 mb-1">
    @if ($cita->estado === 'pendiente')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Confirmar</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="no_asistio">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#dc143c;color:#fff">No asistió</button>
        </form>
        <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#9370db;color:#fff" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
            Reprogramar
        </button>
        @if ($cita->reprogramacion_rechazada)
            <span class="neu-badge" style="background:#ff6b6b;color:#fff;font-size:0.6rem">Reprogramación rechazada</span>
        @endif
    @elseif ($cita->estado === 'confirmada')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_espera">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ffa500;color:#121212">En espera</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="no_asistio">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#dc143c;color:#fff">No asistió</button>
        </form>
        <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#9370db;color:#fff" data-mdb-toggle="modal" data-mdb-target="#reprogramarModal-{{ $cita->id }}">
            Reprogramar
        </button>
    @elseif ($cita->estado === 'en_espera')
        <a href="{{ route('consulta-medica.create', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">En consulta</a>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="no_asistio">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#dc143c;color:#fff">No asistió</button>
        </form>
    @elseif ($cita->estado === 'en_consulta')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="finalizada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#555;color:#fff">Finalizar</button>
        </form>
    @elseif ($cita->estado === 'reprogramada')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Confirmar</button>
        </form>
    @endif
</div>
<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('medico.paciente.show', $cita->paciente->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem">Perfil</a>
    <a href="{{ route('citas.show', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:var(--yellow);color:#121212">Chat</a>
    @if (!in_array($cita->estado, ['cancelada', 'no_asistio']))
    @if ($cita->consultaMedica)
        <a href="{{ route('consulta-medica.show', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Consulta</a>
    @else
        <a href="{{ route('consulta-medica.create', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">+ Consulta</a>
    @endif
    @if ($cita->ultimaReceta)
        <a href="{{ route('recetas.show', $cita->ultimaReceta->id) }}" class="neu-btn neu-btn-sm neu-btn-warning" style="font-size:0.65rem">Receta</a>
    @else
        @php $esHoy = $cita->fecha_hora->isToday(); @endphp
        <a href="{{ $esHoy ? route('recetas.create', $cita->id) : '#' }}"
           class="neu-btn neu-btn-sm {{ $esHoy ? 'neu-btn-primary' : '' }}"
           style="font-size:0.65rem{{ !$esHoy ? ';opacity:0.5;pointer-events:none' : '' }}"
           @if (!$esHoy)
               onclick="event.preventDefault(); alert('No es el día de la consulta. Solo puedes generar la receta el día de la cita.');"
           @endif
        >+ Receta</a>
    @endif
    @endif
</div>
@elseif ($user->esPaciente())
<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('citas.show', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem">Ver detalles</a>
    <a href="{{ route('citas.show', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:var(--yellow);color:#121212">Chat</a>
    @if ($cita->estado === 'pendiente')
    <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
        @csrf @method('PUT')
        <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
    </form>
    @elseif ($cita->estado === 'reprogramada' && $cita->fecha_reprogramada)
        <span class="text-muted" style="font-size:0.7rem;width:100%;margin-bottom:2px">
            Nueva fecha propuesta: {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}
        </span>
        <form action="{{ route('citas.reprogramacion.confirmar', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Confirmar la reprogramación para el {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}?')">
            @csrf
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Aceptar</button>
        </form>
        <form action="{{ route('citas.reprogramacion.cancelar', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Rechazar la reprogramación? La cita mantendrá su fecha original.')">
            @csrf
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Rechazar</button>
        </form>
    @endif
</div>
@else
<div class="d-flex flex-wrap gap-1" style="max-width:200px">
    @if ($cita->estado === 'pendiente')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#00b894;color:#fff">Confirmar</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
        </form>
    @elseif ($cita->estado === 'confirmada')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_espera">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ffa500;color:#121212">En espera</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Cancelar</button>
        </form>
    @elseif ($cita->estado === 'en_espera')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_consulta">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">En consulta</button>
        </form>
    @elseif ($cita->estado === 'en_consulta')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="finalizada">
            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#555;color:#fff">Finalizar</button>
        </form>
    @else
        <span class="text-muted" style="font-size:0.75rem">—</span>
    @endif
</div>
@endif
