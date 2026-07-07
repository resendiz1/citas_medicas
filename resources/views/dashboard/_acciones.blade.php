@php $user = auth()->user(); @endphp
@if ($user->esMedico())
@php
    $esHoy = $cita->fecha_hora->isToday();
    $disabledAttr = 'style="font-size:0.65rem;opacity:0.4;pointer-events:none;background:#888;color:#fff;border:none;cursor:not-allowed;display:inline-block;padding:0.25rem 0.5rem"';
    $ds = function($label, $tooltip) use ($disabledAttr) {
        return '<span data-mdb-toggle="tooltip" title="'.$tooltip.'"><span class="btn btn-outline-secondary btn-sm" '.$disabledAttr.'>'.$label.'</span></span>';
    };
@endphp
<div class="d-flex flex-wrap gap-1 mb-1">
    @if ($cita->estado === 'pendiente')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="btn btn-success btn-sm"><i class="fa fa-check-circle me-1"></i>Confirmar</button>
        </form>
        {!! $ds('En espera', 'Confirma la cita primero') !!}
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i>Cancelar</button>
        </form>
        {!! $ds('No asistió', 'Confirma la cita primero') !!}
    @elseif ($cita->estado === 'confirmada')
        @if ($esHoy)
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_espera">
            <button class="btn btn-warning btn-sm"><i class="fa fa-clock me-1"></i>En espera</button>
        </form>
        @else
            {!! $ds('En espera', 'Solo disponible el día de la cita') !!}
        @endif
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i>Cancelar</button>
        </form>
        @if ($esHoy)
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="no_asistio">
            <button class="btn btn-danger btn-sm"><i class="fa fa-user-slash me-1"></i>No asistió</button>
        </form>
        @else
            {!! $ds('No asistió', 'Solo disponible el día de la cita') !!}
        @endif
    @elseif ($cita->estado === 'en_espera')
        @if ($esHoy)
        <a href="{{ route('consulta-medica.create', $cita->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-stethoscope me-1"></i>En consulta</a>
        @else
        {!! $ds('En consulta', 'Solo disponible el día de la cita') !!}
        @endif
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i>Cancelar</button>
        </form>
        @if ($esHoy)
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Marcar como no asistió?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="no_asistio">
            <button class="btn btn-danger btn-sm"><i class="fa fa-user-slash me-1"></i>No asistió</button>
        </form>
        @else
            {!! $ds('No asistió', 'Solo disponible el día de la cita') !!}
        @endif
    @elseif ($cita->estado === 'en_consulta')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="finalizada">
            <button class="btn btn-secondary btn-sm"><i class="fa fa-check-double me-1"></i>Finalizar</button>
        </form>
    @elseif ($cita->estado === 'reprogramada')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="btn btn-success btn-sm"><i class="fa fa-check-circle me-1"></i>Confirmar</button>
        </form>
    @endif
</div>
<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('medico.paciente.show', $cita->paciente->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-user me-1"></i>Perfil</a>
    <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-comment-dots me-1"></i>Chat</a>
    @if (!in_array($cita->estado, ['cancelada', 'no_asistio']))
    @if ($cita->consultaMedica)
        <a href="{{ route('consulta-medica.show', $cita->id) }}" class="btn btn-success btn-sm"><i class="fa fa-stethoscope me-1"></i>Consulta</a>
    @elseif ($cita->estado === 'confirmada')
        @if ($esHoy)
        <a href="{{ route('consulta-medica.create', $cita->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-plus me-1"></i>+ Consulta</a>
        @else
        {!! $ds('+ Consulta', 'Solo disponible el día de la cita') !!}
        @endif
    @else
        {!! $ds('+ Consulta', 'Primero confirma la cita') !!}
    @endif
    @if ($cita->ultimaReceta)
        <a href="{{ route('recetas.show', $cita->ultimaReceta->id) }}" class="btn btn-warning btn-sm"><i class="fa fa-prescription me-1"></i>Receta</a>
    @else
        @php $esHoy = $cita->fecha_hora->isToday(); @endphp
        @if ($esHoy)
        <a href="{{ route('recetas.create', $cita->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-plus me-1"></i>+ Receta</a>
        @else
        <span data-mdb-toggle="tooltip" title="Solo disponible el día de la cita"><span style="font-size:0.65rem;opacity:0.4;pointer-events:none;background:#888;color:#fff;border:none;cursor:not-allowed;display:inline-block;padding:0.25rem 0.5rem">+ Receta</span></span>
        @endif
    @endif
    @endif
</div>
@elseif ($user->esPaciente())
<div class="d-flex flex-wrap gap-1">
    <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-eye me-1"></i>Ver detalles</a>
    <a href="{{ route('citas.show', $cita->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-comment-dots me-1"></i>Chat</a>
    @if ($cita->estado === 'pendiente')
    <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
        @csrf @method('PUT')
        <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i>Cancelar</button>
    </form>
    @elseif ($cita->estado === 'reprogramada' && $cita->fecha_reprogramada)
        <span class="text-muted" style="font-size:0.7rem;width:100%;margin-bottom:2px">
            Nueva fecha propuesta: {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}
        </span>
        <form action="{{ route('citas.reprogramacion.confirmar', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Confirmar la reprogramación para el {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}?')">
            @csrf
            <button class="btn btn-success btn-sm"><i class="fa fa-circle-check me-1"></i>Aceptar</button>
        </form>
        <form action="{{ route('citas.reprogramacion.cancelar', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Rechazar la reprogramación? La cita mantendrá su fecha original.')">
            @csrf
            <button class="btn btn-danger btn-sm"><i class="fa fa-ban me-1"></i>Rechazar</button>
        </form>
    @endif
</div>
@else
<div class="d-flex flex-wrap gap-1" style="max-width:200px">
    @if ($cita->estado === 'pendiente')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="confirmada">
            <button class="btn btn-success btn-sm"><i class="fa fa-check-circle me-1"></i>Confirmar</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i>Cancelar</button>
        </form>
    @elseif ($cita->estado === 'confirmada')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_espera">
            <button class="btn btn-warning btn-sm"><i class="fa fa-clock me-1"></i>En espera</button>
        </form>
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Cancelar esta cita?')">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="cancelada">
            <button class="btn btn-danger btn-sm"><i class="fa fa-circle-xmark me-1"></i>Cancelar</button>
        </form>
    @elseif ($cita->estado === 'en_espera')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="en_consulta">
            <button class="btn btn-primary btn-sm"><i class="fa fa-stethoscope me-1"></i>En consulta</button>
        </form>
    @elseif ($cita->estado === 'en_consulta')
        <form action="{{ route('citas.estado', $cita->id) }}" method="POST" class="d-inline">
            @csrf @method('PUT')
            <input type="hidden" name="estado" value="finalizada">
            <button class="btn btn-secondary btn-sm"><i class="fa fa-check-double me-1"></i>Finalizar</button>
        </form>
    @else
        <span class="text-muted" style="font-size:0.75rem">—</span>
    @endif
</div>
@endif
