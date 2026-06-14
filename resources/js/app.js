import 'mdb-ui-kit/js/mdb.umd.min.js';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import { Notyf } from 'notyf';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;



    // window.Echo = new Echo({
    //     broadcaster: 'reverb',
    //     key: import.meta.env.VITE_REVERB_APP_KEY,
    //     wsHost: import.meta.env.VITE_REVERB_HOST,
    //     wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    //     wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    //     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    //     enabledTransports: ['ws', 'wss'],
    // });
    // window.Echo.connector.pusher.connection.bind('error', function () {});


console.log('BOOTSTRAP JS CARGADO');
console.log('REVERB KEY:', import.meta.env.VITE_REVERB_APP_KEY);
console.log('REVERB HOST:', import.meta.env.VITE_REVERB_HOST);
console.log('REVERB PORT:', import.meta.env.VITE_REVERB_PORT);
console.log('REVERB SCHEME:', import.meta.env.VITE_REVERB_SCHEME);

window.Pusher = Pusher;


// window.Echo = new Echo({
//     broadcaster: 'reverb',
//     key: import.meta.env.VITE_REVERB_APP_KEY,
//     wsHost: import.meta.env.VITE_REVERB_HOST,
//     wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
//     wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
//     forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

try {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: '9f3c8c0a7f1b2e4d9a6c2b8e1f0d3a55',
        wsHost: 'successful-heart-production-501a.up.railway.app',
        wsPort: 443,
        wssPort: 443,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
    });
} catch (e) {
    console.warn('Echo init failed:', e);
    window.Echo = null;
}


console.log('ECHO CREADO:', window.Echo);






function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.textContent = theme === 'dark' ? '🌙' : '☀️';
    }
}

window.openModal = function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('show');
    modal.style.display = 'block';
    modal.removeAttribute('aria-hidden');
    document.body.classList.add('modal-open');
    let backdrop = document.querySelector('.modal-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    } else {
        backdrop.classList.add('show');
    }
}

window.actualizarEstadoCita = function (citaId, nuevoEstado) {
    const badge = document.getElementById('estado-badge-' + citaId);
    if (badge) {
        const config = {
            pendiente:     { text: 'Pendiente',     bg: 'var(--yellow)',  color: '#121212' },
            confirmada:    { text: 'Confirmada',    bg: '#00b894',        color: '#fff' },
            en_espera:     { text: 'En espera',     bg: '#ffa500',        color: '#121212' },
            en_consulta:   { text: 'En consulta',   bg: '#1e90ff',        color: '#fff' },
            finalizada:    { text: 'Finalizada',    bg: '#555',           color: '#fff' },
            cancelada:     { text: 'Cancelada',     bg: '#ff4444',        color: '#fff' },
            no_asistio:    { text: 'No asistió',    bg: '#dc143c',        color: '#fff' },
            reprogramada:  { text: 'Reprogramada',  bg: '#9370db',        color: '#fff' },
        };
        const cfg = config[nuevoEstado] || { text: nuevoEstado, bg: '#888', color: '#fff' };
        badge.textContent = cfg.text;
        badge.style.background = cfg.bg;
        badge.style.color = cfg.color;
    }
    const accionesTd = document.querySelector('td[data-cita-acciones="' + citaId + '"]');
    if (accionesTd) {
        fetch('/citas/' + citaId + '/acciones')
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (data && data.html) {
                    accionesTd.innerHTML = data.html;
                }
            })
            .catch(function () {});
    }
};

window.closeModal = function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('show');
    modal.style.display = '';
    modal.setAttribute('aria-hidden', 'true');
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop && !document.querySelectorAll('.modal.show').length) {
        backdrop.classList.remove('show');
        backdrop.remove();
        document.body.classList.remove('modal-open');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const saved = localStorage.getItem('theme') || 'dark';
    applyTheme(saved);

    const btn = document.getElementById('theme-toggle');
    if (btn) {
        btn.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme');
            applyTheme(current === 'dark' ? 'light' : 'dark');
        });
    }

    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const menu = this.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.toggle('show');
                this.classList.toggle('show');
            }
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                menu.previousElementSibling?.classList.remove('show');
            });
        }
    });

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-mdb-toggle="modal"]');
        if (trigger) {
            e.preventDefault();
            const target = trigger.getAttribute('data-mdb-target');
            if (target) openModal(target.substring(1));
        }
        const dismiss = e.target.closest('[data-mdb-dismiss="modal"]');
        if (dismiss) {
            const modal = dismiss.closest('.modal');
            if (modal) closeModal(modal.id);
        }
    });

    flatpickr('input[type="date"], input.js-flatpickr-date', {
        locale: Spanish,
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd/m/Y',
        allowInput: true,
        disableMobile: true,
    });

    flatpickr('input[type="datetime-local"]:not(.js-flatpickr-simple)', {
        locale: Spanish,
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'd/m/Y H:i',
        time_24hr: true,
        allowInput: true,
        disableMobile: true,
    });

    flatpickr('input.js-flatpickr-simple', {
        locale: Spanish,
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        time_24hr: true,
    });

    const notyf = new Notyf({
        duration: 5000,
        position: { x: 'right', y: 'top' },
        dismissible: true,
        types: [
            {
                type: 'success',
                background: '#00b894',
                icon: false,
            },
            {
                type: 'error',
                background: '#ff4444',
                icon: false,
            },
        ],
    });

    const flashSuccess = document.getElementById('flash-success');
    if (flashSuccess) notyf.success(flashSuccess.dataset.message);

    const flashError = document.getElementById('flash-error');
    if (flashError) notyf.error(flashError.dataset.message);

    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    const docForm = document.querySelector('#documentosModal form');
    if (docForm) {
        docForm.addEventListener('submit', function (e) {
            const input = this.querySelector('input[name="documento"]');
            if (!input || !input.files || !input.files[0]) return;
            const ext = input.files[0].name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(ext)) {
                e.preventDefault();
                notyf.error('Solo se permiten archivos PDF e imágenes (JPG, PNG, GIF, WebP).');
            }
        });
    }

    const notifPollUrl = document.querySelector('meta[name="notificaciones-poll"]')?.getAttribute('content');
    if (notifPollUrl) {
        const shown = new Set();
        setInterval(async () => {
            try {
                const res = await fetch(notifPollUrl);
                if (!res.ok) return;
                const notifications = await res.json();
                for (const n of notifications) {
                    if (shown.has(n.id)) continue;
                    shown.add(n.id);
                    notyf.success(n.message || 'Nueva notificación');
                }
            } catch {
            }
        }, 10000);
    }

    const estadoTds = document.querySelectorAll('td[data-cita-id]');
    if (estadoTds.length) {
        const ids = [...new Set([...estadoTds].map(td => parseInt(td.dataset.citaId)))];

        if (typeof window.Echo !== 'undefined' && window.Echo) {
            ids.forEach(function (id) {
                try {
                    window.Echo.private('chat.cita.' + id)
                        .listen('.CitaEstadoActualizado', function (e) {
                            if (typeof window.actualizarEstadoCita === 'function') {
                                window.actualizarEstadoCita(e.cita_id, e.estado);
                            }
                        });
                } catch (err) {
                    console.warn('Echo sub failed for cita ' + id, err);
                }
            });
        }

        let estadoPollCache = {};
        ids.forEach(function (id) {
            const badge = document.getElementById('estado-badge-' + id);
            if (badge) estadoPollCache[id] = badge.textContent.trim();
        });

        setInterval(async function () {
            const idsToCheck = Object.keys(estadoPollCache).join(',');
            if (!idsToCheck) return;
            try {
                const res = await fetch('/citas/estados/poll?ids=' + idsToCheck);
                if (!res.ok) return;
                const estados = await res.json();
                for (const [id, nuevoEstado] of Object.entries(estados)) {
                    const cachedText = estadoPollCache[id];
                    const config = {
                        pendiente: 'Pendiente', confirmada: 'Confirmada',
                        en_espera: 'En espera', en_consulta: 'En consulta',
                        finalizada: 'Finalizada', cancelada: 'Cancelada',
                        no_asistio: 'No asistió', reprogramada: 'Reprogramada',
                    };
                    const label = config[nuevoEstado] || nuevoEstado;
                    if (cachedText !== label) {
                        estadoPollCache[id] = label;
                        if (typeof window.actualizarEstadoCita === 'function') {
                            window.actualizarEstadoCita(parseInt(id), nuevoEstado);
                        }
                    }
                }
            } catch {}
        }, 5000);
    }

    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    if (userId && typeof window.Echo !== 'undefined' && window.Echo) {
        try {
            window.Echo.private('App.Models.User.' + userId)
                .listen('.CitaCreada', function () {
                    location.reload();
                });
        } catch (err) {
            console.warn('Echo sub for CitaCreada failed', err);
        }
    }

    const citasTds = document.querySelectorAll('td[data-cita-id]');
    if (citasTds.length > 0) {
        let maxConocido = Math.max(...[...citasTds].map(td => parseInt(td.dataset.citaId)));
        setInterval(async function () {
            try {
                const res = await fetch('/dashboard/citas/check?max_id=' + maxConocido);
                if (!res.ok) return;
                const data = await res.json();
                if (data.nuevas) {
                    maxConocido = data.max_id;
                    location.reload();
                }
            } catch {}
        }, 10000);
    }
});
