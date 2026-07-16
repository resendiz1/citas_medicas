import 'mdb-ui-kit/js/mdb.umd.min.js';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import toastr from 'toastr';
import 'toastr/build/toastr.min.css';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { initChatWidget } from './chat-widget.js';
import gsap from 'gsap';

window.Pusher = Pusher;

(function () {
    try {
        var key = document.querySelector('meta[name="reverb-key"]')?.getAttribute('content');
        var host = document.querySelector('meta[name="reverb-host"]')?.getAttribute('content');
        var port = document.querySelector('meta[name="reverb-port"]')?.getAttribute('content');
        var scheme = document.querySelector('meta[name="reverb-scheme"]')?.getAttribute('content');

        if (!key || !host) {
            console.warn('Reverb meta tags not found, Echo disabled');
            window.Echo = null;
            return;
        }

        var isTls = scheme === 'https';

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: key,
            wsHost: host,
            wsPort: isTls ? 443 : (parseInt(port) || 8080),
            wssPort: 443,
            forceTLS: isTls,
            enabledTransports: isTls ? ['ws', 'wss'] : ['ws', 'wss'],
        });
    } catch (e) {
        console.warn('Echo init failed:', e);
        window.Echo = null;
    }
})();

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

window.initMdbTooltips = function (container) {
    (container || document).querySelectorAll('[data-mdb-toggle="tooltip"]').forEach(function (el) {
        try { new mdb.Tooltip(el); } catch (e) {}
    });
};

window.actualizarEstadoCita = function (citaId, nuevoEstado) {
    const badge = document.getElementById('estado-badge-' + citaId);
    if (badge) {
        const config = {
            pendiente:     { text: 'Pendiente',     cls: 'badge bg-warning text-dark' },
            confirmada:    { text: 'Confirmada',    cls: 'badge bg-success' },
            en_espera:     { text: 'En espera',     cls: 'badge bg-warning text-dark' },
            en_consulta:   { text: 'En consulta',   cls: 'badge bg-primary' },
            finalizada:    { text: 'Finalizada',    cls: 'badge bg-secondary' },
            cancelada:     { text: 'Cancelada',     cls: 'badge bg-danger' },
            no_asistio:    { text: 'No asistió',    cls: 'badge bg-danger' },
            reprogramada:  { text: 'Reprogramada',  cls: 'badge bg-info' },
        };
        const cfg = config[nuevoEstado] || { text: nuevoEstado, cls: 'badge bg-secondary' };
        badge.textContent = cfg.text;
        badge.className = cfg.cls;
    }
    const accionesTd = document.querySelector('td[data-cita-acciones="' + citaId + '"]');
    if (accionesTd) {
        fetch('/citas/' + citaId + '/acciones')
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (data && data.html) {
                    accionesTd.innerHTML = data.html;
                    window.initMdbTooltips(accionesTd);
                }
            })
            .catch(function () {});
    }
};

window.toggleDrop = function toggleDrop(id) {
    var menu = document.querySelector('[data-drop-menu="' + id + '"]');
    var btn = document.querySelector('[data-drop-wrap="' + id + '"] button');
    if (!menu || !btn) return;
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
        return;
    }
    document.querySelectorAll('[data-drop-menu]').forEach(function(m) { m.style.display = 'none'; });
    menu.style.display = 'block';
    menu.style.visibility = 'hidden';
    var r = btn.getBoundingClientRect();
    var h = menu.offsetHeight;
    var spaceAbove = r.top;
    var spaceBelow = window.innerHeight - r.bottom;
    menu.style.left = Math.max(4, r.right - 220) + 'px';
    if (spaceAbove >= h + 8) {
        menu.style.top = (r.top - h - 4) + 'px';
    } else {
        menu.style.top = (r.bottom + 4) + 'px';
    }
    menu.style.maxHeight = Math.min(h, Math.max(spaceAbove, spaceBelow) - 8) + 'px';
    menu.style.overflowY = 'auto';
    menu.style.visibility = 'visible';
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
    localStorage.removeItem('theme');
    document.documentElement.removeAttribute('data-theme');

    // Geolocation logging
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (pos) {
            var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrf) return;
            var fd = new FormData();
            fd.append('lat', pos.coords.latitude);
            fd.append('lng', pos.coords.longitude);
            fd.append('_token', csrf);
            navigator.sendBeacon('/user/logs/geo', fd);
        }, function () {}, { timeout: 5000, enableHighAccuracy: false });
    }

    const chatWidget = document.getElementById('chat-widget');
    if (chatWidget) {
        initChatWidget(
            chatWidget.dataset.chatCitasUrl,
            parseInt(chatWidget.dataset.userId)
        );
    }

    window.initMdbTooltips();

    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const wasOpen = this.classList.contains('show');
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                menu.previousElementSibling?.classList.remove('show');
            });
            if (!wasOpen) {
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.add('show');
                    this.classList.add('show');
                }
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
        if (!e.target.closest('[data-drop-wrap]')) {
            document.querySelectorAll('[data-drop-menu]').forEach(function(m) { m.style.display = 'none'; });
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

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000,
        showDuration: 300,
        hideDuration: 1000,
    };

    document.querySelectorAll('.alert-success, .alert-danger').forEach(function (el) {
        if (el.offsetParent === null) return;
        var msg = el.textContent.trim();
        if (msg) {
            if (el.classList.contains('alert-success')) toastr.success(msg);
            else if (el.classList.contains('alert-danger')) toastr.error(msg);
        }
        el.style.display = 'none';
    });

    const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    const docForm = document.querySelector('#documentosModal form');
    if (docForm) {
        docForm.addEventListener('submit', function (e) {
            const input = this.querySelector('input[name="documento"]');
            if (!input || !input.files || !input.files[0]) return;
            const ext = input.files[0].name.split('.').pop().toLowerCase();
            if (!allowedExtensions.includes(ext)) {
                e.preventDefault();
                toastr.error('Solo se permiten archivos PDF e imágenes (JPG, PNG, GIF, WebP).');
            }
        });
    }

    const notifPollUrl = document.querySelector('meta[name="notificaciones-poll"]')?.getAttribute('content');
    if (notifPollUrl) {
        const shown = new Set(JSON.parse(localStorage.getItem('shown_notif_ids') || '[]'));
        function saveShown() {
            localStorage.setItem('shown_notif_ids', JSON.stringify([...shown]));
        }
        window.addEventListener('beforeunload', saveShown);
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') saveShown();
        });
        setInterval(async () => {
            try {
                const res = await fetch(notifPollUrl);
                if (!res.ok) return;
                const notifications = await res.json();
                for (const n of notifications) {
                    if (n.tipo === 'mensaje') continue;
                    if (n.tipo === 'creada') continue;
                    if (shown.has(n.id)) continue;
                    shown.add(n.id);
                    toastr.success(n.message || 'Nueva notificación');
                }
                saveShown();
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
                .listen('.MedicoAprobado', function () {
                    var alert = document.getElementById('pending-approval-alert');
                    if (alert) {
                        gsap.to(alert, { opacity: 0, y: -10, duration: 0.4, ease: 'power2.in', onComplete: function() { alert.remove(); } });
                    }
                });
        } catch (err) {
            console.warn('Echo sub for MedicoAprobado failed', err);
        }
        try {
            window.Echo.private('App.Models.User.' + userId)
                .listen('.CitaCreada', function () {
                    setTimeout(function () { location.reload(); }, 2000);
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

    // Dashboard animations
    var cont = document.querySelector('.container');
    if (cont && cont.querySelector('.stat-icon')) {
        var cards = cont.querySelectorAll('.row.g-4 > div > .card, .row.g-4 > div a > .card');
        if (cards.length) {
            gsap.from(cards, { opacity: 0, y: 30, stagger: 0.1, duration: 0.5, ease: 'power2.out' });
        }
    }

    if (cont && cont.querySelector('.neu-table')) {
        var tipoUser = document.querySelector('meta[name="user-id"]');
        // solo animar en dashboard (no en admin CRUDs)
        var rows = cont.querySelectorAll('.neu-table tbody tr');
        if (rows.length && cont.querySelector('h5.fw-bold')) {
            gsap.from(rows, { opacity: 0, x: -20, stagger: 0.04, duration: 0.3, ease: 'power2.out', delay: 0.3 });
        }
    }

});
