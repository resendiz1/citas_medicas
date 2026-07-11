@extends('layouts.app')

@section('title', 'Nueva Cita')

@push('head')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css" rel="stylesheet">
<style>
#calendar {
    max-width: 100%;
    background: transparent;
}
.fc-theme-standard td, .fc-theme-standard th {
    border-color: rgba(255,255,255,0.08);
}
.fc .fc-daygrid-day-frame {
    min-height: 70px;
    cursor: pointer;
}
.fc .fc-daygrid-day-number {
    color: var(--text-primary);
    font-size: 0.85rem;
}
.fc .fc-col-header-cell-cushion {
    color:#1266f1;
    font-size: 0.75rem;
    text-transform: uppercase;
    font-weight: 600;
}
.fc .fc-button-primary {
    background: #fff !important;
    border: none !important;
    box-shadow: 4px 4px 8px #ccc, -4px -4px 8px #f5f5f5;
    color: var(--text-primary) !important;
    font-size: 0.8rem !important;
    padding: 0.3rem 0.8rem !important;
    border-radius: 10px !important;
}
.fc .fc-button-primary:hover {
    color:#1266f1 !important;
}
.fc .fc-button-primary:not(:disabled).fc-button-active {
    background:#1266f1 !important;
    color: #121212 !important;
}
.fc .fc-toolbar-title {
    color: var(--text-primary);
    font-size: 1.1rem !important;
    font-weight: 600;
}
.fc .fc-day-today {
    background: rgba(240, 192, 0, 0.08) !important;
}
.fc .fc-day-selected {
    background: rgba(240, 192, 0, 0.2) !important;
    outline: 2px solid #1266f1;
    outline-offset: -2px;
    border-radius: 6px;
}
.fc-day-disabled {
    opacity: 0.35;
    pointer-events: none;
}
.avail-indicator {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    margin-top: 2px;
}
.avail-dot-green { background: #00b894; }
.avail-dot-red { background: #ff4444; }
.avail-dot-gray { background: #555; }
.horario-chip {
    display: inline-block;
    padding: 0.4rem 0.9rem;
    margin: 0.25rem;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease;
}
.horario-chip:hover {
    transform: translateY(-1px);
}
.horario-chip.selected {
    background: #00b894 !important;
    color: #fff !important;
    box-shadow: 0 0 12px rgba(0, 184, 148, 0.5) !important;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card shadow-2 p-4">
                <h4 class="mb-4 fw-bold">Solicitar Cita Médica</h4>
                <form method="POST" action="{{ route('citas.store') }}" id="citaForm">
                    @csrf

                    <div class="mb-4">
                        <label for="medico_id" class="form-label">Médico</label>
                        <select id="medico_id" name="medico_id"
                                class="form-select @error('medico_id') is-invalid @enderror" required>
                            <option value="">Seleccionar médico...</option>
                            @foreach ($medicos as $medico)
                                <option value="{{ $medico->id }}" {{ old('medico_id', $medicoSeleccionado) == $medico->id ? 'selected' : '' }}>
                                    {{ $medico->name }} — {{ optional(optional($medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? 'General' }}
                                </option>
                            @endforeach
                        </select>
                        @error('medico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4" id="calendar-wrapper" style="display:none">
                        <label class="form-label">Selecciona un día disponible</label>
                        <div id="calendar"></div>
                        <div id="sin-horarios-msg" class="flex-column align-items-center py-4" style="display:none">
                            <i class="fa fa-clock fa-2x text-muted opacity-50 mb-2"></i>
                            <p class="fw-bold text-muted mb-0" style="font-size:1.1rem">Este médico aún no ha configurado sus horarios.</p>
                        </div>
                        <div id="horarios-container" class="mt-3" style="display:none">
                            <label class="form-label">Horarios disponibles</label>
                            <div id="horarios-list" class="d-flex flex-wrap"></div>
                            <small class="text-muted">Los horarios en gris ya están ocupados.</small>
                        </div>
                        <input type="hidden" name="fecha_hora" id="fecha_hora" value="{{ old('fecha_hora') }}">
                        @error('fecha_hora') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="motivo" class="form-label">Motivo de la consulta</label>
                        <textarea id="motivo" name="motivo" rows="4"
                                  class="form-control @error('motivo') is-invalid @enderror"
                                  required>{{ old('motivo') }}</textarea>
                        @error('motivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fa fa-xmark me-1"></i><span class="btn-text">Cancelar</span></a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-calendar-check me-1"></i><span class="btn-text">Solicitar cita</span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    const bloqueosPorMedico = @json($bloqueosPorMedico);
    const horariosPorMedico = @json($horariosPorMedico);
    const citasPorMedico = @json($citasPorMedico);
    const intervalosPorMedico = @json($intervalosPorMedico);
    let calendar = null;
    let fechaSeleccionada = null;
    let horaSeleccionada = null;

    function hoyLocal() {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function getDiasActivos(medicoId) {
        const horarios = horariosPorMedico[medicoId] ?? [];
        const dias = new Set();
        horarios.forEach(function (h) { dias.add(h.dia_semana); });
        return dias;
    }

    function getHorariosDelDia(medicoId, diaSemana) {
        return (horariosPorMedico[medicoId] ?? []).filter(function (h) { return h.dia_semana === diaSemana; });
    }

    function getCitasOcupadas(medicoId, dateStr) {
        return (citasPorMedico[medicoId] ?? []).filter(function (c) { return c.startsWith(dateStr); });
    }

    function generarSlots(horaInicio, horaFin, intervaloMinutos) {
        const slots = [];
        const [hI, mI] = horaInicio.split(':').map(Number);
        const [hF, mF] = horaFin.split(':').map(Number);
        let minActual = hI * 60 + mI;
        const minFin = hF * 60 + mF;
        while (minActual + intervaloMinutos <= minFin) {
            const hh = String(Math.floor(minActual / 60)).padStart(2, '0');
            const mm = String(minActual % 60).padStart(2, '0');
            slots.push(hh + ':' + mm);
            minActual += intervaloMinutos;
        }
        return slots;
    }

    function marcarDia(medicoId) {
        if (!calendar) return;
        const diasActivos = getDiasActivos(medicoId);
        const ranges = bloqueosPorMedico[medicoId] ?? [];

        calendar.removeAllEvents();

        calendar.setOption('dayCellClassNames', function (arg) {
            const d = arg.date;
            const dateStr = arg.dateStr;
            const diaSem = d.getDay();
            const esHoy = dateStr === hoyLocal();
            if (d < new Date() && !esHoy) return ['fc-day-disabled'];

            for (const r of ranges) {
                if (dateStr >= r.from && dateStr <= r.to) return ['fc-day-disabled'];
            }

            if (!diasActivos.has(diaSem)) return ['fc-day-disabled'];

            return [];
        });
    }

    function mostrarHorarios(medicoId, dateStr) {
        const date = new Date(dateStr + 'T12:00:00');
        const diaSem = date.getDay();
        const horarios = getHorariosDelDia(medicoId, diaSem);
        const ocupadas = getCitasOcupadas(medicoId, dateStr);
        const container = document.getElementById('horarios-list');
        const wrapper = document.getElementById('horarios-container');

        if (horarios.length === 0) {
            wrapper.style.display = 'none';
            return;
        }

        container.innerHTML = '';
        const ahora = new Date();
        const esHoy = dateStr === hoyLocal();

        const intervalo = intervalosPorMedico[medicoId] ?? 30;
        let todosSlots = [];
        horarios.forEach(function (h) {
            const slots = generarSlots(h.hora_inicio, h.hora_fin, intervalo);
            todosSlots = todosSlots.concat(slots);
        });

        const ocupadasSet = new Set(ocupadas.map(function (c) { return c.split(' ')[1]?.slice(0, 5); }));

        todosSlots.forEach(function (slot) {
            if (esHoy) {
                const [h, m] = slot.split(':').map(Number);
                const slotDate = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate(), h, m);
                if (slotDate <= ahora) return;
            }

            const estaOcupada = ocupadasSet.has(slot);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'horario-chip';
            btn.textContent = slot;
            btn.dataset.hora = slot;
            btn.dataset.disponible = estaOcupada ? 'false' : 'true';

            if (estaOcupada) {
                btn.style.background = '#444';
                btn.style.color = '#888';
                btn.style.cursor = 'not-allowed';
            } else {
                btn.style.background = '#fff';
                btn.style.color = 'var(--text-primary)';
                btn.style.boxShadow = '4px 4px 8px #ccc, -4px -4px 8px #f5f5f5';
                btn.addEventListener('click', function () {
                    document.querySelectorAll('.horario-chip.selected').forEach(function (el) { el.classList.remove('selected'); });
                    this.classList.add('selected');
                    fechaSeleccionada = dateStr;
                    horaSeleccionada = slot;
                    document.getElementById('fecha_hora').value = dateStr + ' ' + slot + ':00';
                });
            }
            container.appendChild(btn);
        });

        wrapper.style.display = todosSlots.length > 0 ? 'block' : 'none';
        
    }

    document.addEventListener('DOMContentLoaded', function () {
        const medicoSelect = document.getElementById('medico_id');
        const calendarWrapper = document.getElementById('calendar-wrapper');
        const calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            firstDay: 1,
            height: 'auto',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: ''
            },
            dayCellDidMount: function (info) {
                const dateStr = info.dateStr;
                const medicoId = medicoSelect.value;
                if (!medicoId) return;

                const diasActivos = getDiasActivos(medicoId);
                const diaSem = info.date.getDay();
                const ranges = bloqueosPorMedico[medicoId] ?? [];
                const esHoy = dateStr === hoyLocal();
                if (info.date < new Date() && !esHoy) return;

                let bloqueado = false;
                for (const r of ranges) {
                    if (dateStr >= r.from && dateStr <= r.to) { bloqueado = true; break; }
                }

                let dot = document.createElement('div');
                dot.className = 'avail-indicator';
                if (bloqueado || !diasActivos.has(diaSem)) {
                    dot.classList.add('avail-dot-red');
                } else {
                    dot.classList.add('avail-dot-green');
                }
                info.el.querySelector('.fc-daygrid-day-top')?.appendChild(dot);
            },
            dateClick: function (info) {
                const date = info.date;
                const dateStr = info.dateStr;
                const medicoId = medicoSelect.value;
                if (!medicoId) return;

                const diaSem = date.getDay();
                const diasActivos = getDiasActivos(medicoId);
                const ranges = bloqueosPorMedico[medicoId] ?? [];
                const esHoy = dateStr === hoyLocal();
                if (date < new Date() && !esHoy) return;

                let bloqueado = false;
                for (const r of ranges) {
                    if (dateStr >= r.from && dateStr <= r.to) { bloqueado = true; break; }
                }

                if (bloqueado || !diasActivos.has(diaSem)) return;

                document.querySelectorAll('.fc-day-selected').forEach(function (el) { el.classList.remove('fc-day-selected'); });
                var dayCell = info.dayEl;
                if (dayCell) dayCell.classList.add('fc-day-selected');

                mostrarHorarios(medicoId, dateStr);
            }
        });

        calendar.render();

        function initCalendar(medicoId) {
            const sinHorarios = document.getElementById('sin-horarios-msg');
            const horarios = horariosPorMedico[medicoId] ?? [];
            if (horarios.length === 0) {
                calendarWrapper.style.display = 'block';
                calendarEl.style.display = 'none';
                sinHorarios.classList.add('d-flex');
                sinHorarios.style.display = 'flex';
                document.getElementById('horarios-container').style.display = 'none';
                return;
            }
            calendarEl.style.display = 'block';
            sinHorarios.classList.remove('d-flex');
            sinHorarios.style.display = 'none';
            calendarWrapper.style.display = 'block';
            marcarDia(medicoId);
            calendar.refetchEvents();
        }

        function clearCalendar() {
            calendarWrapper.style.display = 'none';
            document.getElementById('horarios-container').style.display = 'none';
            const sinHorarios = document.getElementById('sin-horarios-msg');
            sinHorarios.classList.remove('d-flex');
            sinHorarios.style.display = 'none';
            calendarEl.style.display = 'block';
            document.getElementById('fecha_hora').value = '';
            fechaSeleccionada = null;
            horaSeleccionada = null;
        }

        if (medicoSelect.value) {
            initCalendar(medicoSelect.value);
            setTimeout(function () {
                calendar.updateSize();
            }, 100);
        }

        medicoSelect.addEventListener('change', function () {
            if (this.value) {
                initCalendar(this.value);
                clearHorarios();
                calendar.updateSize();
            } else {
                clearCalendar();
            }
        });

        function clearHorarios() {
            document.getElementById('horarios-container').style.display = 'none';
            document.getElementById('horarios-list').innerHTML = '';
            document.getElementById('fecha_hora').value = '';
            document.querySelectorAll('.fc-day-selected').forEach(function (el) { el.classList.remove('fc-day-selected'); });
            fechaSeleccionada = null;
            horaSeleccionada = null;
        }
    });
</script>
@endpush
