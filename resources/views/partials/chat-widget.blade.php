<div id="chat-widget" class="chat-widget">
    <button id="chat-fab" class="chat-fab" onclick="toggleChatWidget()">
        <i class="fas fa-comment-dots"></i>
        <span id="chat-badge" class="chat-badge d-none">0</span>
    </button>
    <div id="chat-panel" class="chat-panel d-none">
        <div class="chat-panel-header">
            <div style="display:flex;align-items:center;gap:0.3rem;flex:1;min-width:0">
                <span id="chat-unread-dot" class="d-none" style="width:8px;height:8px;border-radius:50%;background:#ff4444;flex-shrink:0"></span>
                <select id="chat-cita-select" class="form-select form-select-sm" style="background:transparent;color:#121212;border:none;font-weight:700;font-size:0.8rem;max-width:220px;padding:0;cursor:pointer;flex:1;min-width:0">
                    <option value="">Cargando...</option>
                </select>
            </div>
            <button class="btn btn-sm p-0 text-white" onclick="toggleChatWidget()" style="font-size:1.2rem">&times;</button>
        </div>
        <div id="chat-mensajes" class="chat-mensajes" style="max-height:400px;overflow-y:auto;display:flex;flex-direction:column;gap:0.5rem;padding:0.5rem;background:rgba(0,0,0,0.15);border-radius:10px;flex:1"></div>
        <div class="chat-widget-input">
            <input type="text" id="chat-input" class="neu-input form-control form-control-sm" placeholder="Escribe un mensaje..." maxlength="2000">
            <button id="chat-send" class="neu-btn neu-btn-primary" style="white-space:nowrap;font-size:0.75rem;padding:0.25rem 0.6rem">Enviar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var chatCitasList = [];
    var chatCitaActual = null;
    var chatWidgetAbierto = false;
    var chatPollInterval = null;
    var chatBgInterval = null;
    var chatMensajesIds = new Set();
    var chatNoLeidos = {};
    var chatUltimosMsgs = {};
    var chatBgReady = false;

    function toggleChatWidget() {
        chatWidgetAbierto = !chatWidgetAbierto;
        document.getElementById('chat-panel').classList.toggle('d-none', !chatWidgetAbierto);
        if (chatWidgetAbierto) {
            abrirChat();
        }
    }

    function abrirChat() {
        detenerPoll();
        chatCitaActual = null;
        chatMensajesIds = new Set();
        var sel = document.getElementById('chat-cita-select');
        sel.innerHTML = '<option value="">Cargando...</option>';

        fetch('{{ route('chat.citas') }}')
            .then(function (r) { return r.json(); })
            .then(function (citas) {
                chatCitasList = citas;
                if (!citas.length) {
                    sel.innerHTML = '<option value="">Sin citas</option>';
                    document.getElementById('chat-mensajes').innerHTML = '<div class="text-center text-muted small p-3">Sin citas</div>';
                    return;
                }
                sel.innerHTML = citas.map(function (c) {
                    var marca = chatNoLeidos[c.id] ? '● ' : '';
                    return '<option value="' + c.id + '">' + marca + escapeHtml(c['con quien']) + ' (' + c.fecha + ')</option>';
                }).join('');
                sel.value = citas[0].id;
                chatNoLeidos = {};
                actualizarBadge();
                cambiarChatCita(citas[0].id);
            })
            .catch(function () {
                sel.innerHTML = '<option value="">Error</option>';
                document.getElementById('chat-mensajes').innerHTML = '<div class="text-center text-muted small p-3">Error al cargar</div>';
            });
    }

    function cambiarChatCita(citaId) {
        detenerPoll();
        chatCitaActual = citaId;
        chatMensajesIds = new Set();
        delete chatNoLeidos[citaId];
        actualizarBadge();
        cargarMensajesWidget(citaId);
        iniciarPoll(citaId);
    }

    function cargarMensajesWidget(citaId) {
        var el = document.getElementById('chat-mensajes');
        el.innerHTML = '<div class="text-center text-muted small p-3">Cargando...</div>';
        fetch('/citas/' + citaId + '/chat')
            .then(function (r) { return r.json(); })
            .then(function (msgs) {
                el.innerHTML = '';
                chatMensajesIds = new Set();
                msgs.forEach(function (m) {
                    chatMensajesIds.add(m.id);
                    agregarMensajeWidget(m);
                });
                el.scrollTop = el.scrollHeight;
            })
            .catch(function () {
                el.innerHTML = '<div class="text-center text-muted small p-3">Error</div>';
            });
    }

    function agregarMensajeWidget(m) {
        var el = document.getElementById('chat-mensajes');
        if (!el) return;
        var esPropio = parseInt(m.user_id) === {{ auth()->id() }};
        var div = document.createElement('div');
        var align = esPropio ? 'flex-end' : 'flex-start';
        var bg = esPropio ? 'var(--yellow)' : 'var(--neu-card)';
        var color = esPropio ? '#121212' : 'var(--text-primary)';
        div.innerHTML = '<div style="display:flex;justify-content:' + align + ';margin-bottom:0.25rem">' +
            '<div style="max-width:75%;padding:0.5rem 0.75rem;border-radius:12px;background:' + bg + ';color:' + color + ';box-shadow:2px 2px 6px var(--neu-shadow-dark),-2px -2px 6px var(--neu-shadow-light)">' +
            '<div style="font-size:0.65rem;opacity:0.7;margin-bottom:0.2rem">' + escapeHtml(m.nombre) + ' · ' + m.created_at + '</div>' +
            '<div style="font-size:0.85rem;line-height:1.4;word-break:break-word">' + escapeHtml(m.mensaje) + '</div></div></div>';
        el.appendChild(div.firstElementChild);
        el.scrollTop = el.scrollHeight;
    }

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function actualizarBadge() {
        var dot = document.getElementById('chat-unread-dot');
        var badge = document.getElementById('chat-badge');
        var total = Object.values(chatNoLeidos).reduce(function (a, b) { return a + b; }, 0);

        if (total > 0) {
            if (dot) dot.classList.remove('d-none');
            badge.textContent = total;
            badge.classList.remove('d-none');
        } else {
            if (dot) dot.classList.add('d-none');
            badge.classList.add('d-none');
        }

        var sel = document.getElementById('chat-cita-select');
        if (sel && chatCitasList.length) {
            sel.innerHTML = chatCitasList.map(function (c) {
                var marca = chatNoLeidos[c.id] ? '● ' : '';
                return '<option value="' + c.id + '">' + marca + escapeHtml(c['con quien']) + ' (' + c.fecha + ')</option>';
            }).join('');
            sel.value = chatCitaActual;
        }
    }

    function iniciarPoll(citaId) {
        detenerPoll();
        chatPollInterval = setInterval(function () {
            if (chatCitaActual !== citaId) return detenerPoll();
            var ultimoId = chatMensajesIds.size > 0 ? Math.max.apply(null, Array.from(chatMensajesIds)) : 0;
            fetch('/citas/' + citaId + '/chat?since_id=' + ultimoId)
                .then(function (r) { return r.json(); })
                .then(function (msgs) {
                    msgs.forEach(function (m) {
                        if (!chatMensajesIds.has(m.id)) {
                            chatMensajesIds.add(m.id);
                            agregarMensajeWidget(m);
                        }
                    });
                })
                .catch(function () {});
        }, 5000);
    }

    function detenerPoll() {
        if (chatPollInterval) {
            clearInterval(chatPollInterval);
            chatPollInterval = null;
        }
    }

    function iniciarBgPoll() {
        detenerBgPoll();
        chatBgInterval = setInterval(function () {
            fetch('{{ route('chat.citas') }}')
                .then(function (r) { return r.json(); })
                .then(function (citas) {
                    citas.forEach(function (c) {
                        var actual = c.ultimo_msg || '';
                        var anterior = chatUltimosMsgs[c.id] || '';

                        if (!chatBgReady) {
                            chatUltimosMsgs[c.id] = actual;
                            return;
                        }

                        if (actual && actual !== anterior) {
                            chatUltimosMsgs[c.id] = actual;
                            if (c.id !== chatCitaActual) {
                                chatNoLeidos[c.id] = (chatNoLeidos[c.id] || 0) + 1;
                            }
                        }
                    });
                    chatBgReady = true;
                    if (Object.keys(chatNoLeidos).length > 0) actualizarBadge();
                })
                .catch(function () {});
        }, 10000);
    }

    function detenerBgPoll() {
        if (chatBgInterval) {
            clearInterval(chatBgInterval);
            chatBgInterval = null;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        iniciarBgPoll();

        var sendBtn = document.getElementById('chat-send');
        var input = document.getElementById('chat-input');
        var sel = document.getElementById('chat-cita-select');
        if (!sendBtn || !input || !sel) return;

        sel.addEventListener('change', function () {
            var id = parseInt(this.value);
            if (id) cambiarChatCita(id);
        });

        sendBtn.addEventListener('click', function () {
            var msg = input.value.trim();
            if (!msg || !chatCitaActual) return;
            input.disabled = true;
            fetch('/citas/' + chatCitaActual + '/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
                },
                body: JSON.stringify({ mensaje: msg })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                chatMensajesIds.add(data.id);
                agregarMensajeWidget(data);
                input.value = '';
            })
            .catch(function () {})
            .finally(function () { input.disabled = false; input.focus(); });
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') sendBtn.click();
        });
    });
</script>
@endpush
