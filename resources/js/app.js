import 'mdb-ui-kit/js/mdb.umd.min.js';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import { Notyf } from 'notyf';

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
});
