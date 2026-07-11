<div id="admin-ia-chat-widget" class="admin-ia-chat-widget" data-historial-url="{{ route('admin.medicos.chat-ia.historial', $user->id) }}" data-chat-url="{{ route('admin.medicos.chat-ia', $user->id) }}">
    <button id="admin-ia-chat-fab" class="admin-ia-chat-fab" onclick="toggleAdminIaChat()">
        <i class="fa fa-robot"></i>
        <span class="admin-ia-chat-badge d-none">Beta</span>
    </button>
    <div id="admin-ia-chat-panel" class="admin-ia-chat-panel d-none">
        <div class="admin-ia-chat-panel-header">
            <div>
                <i class="fa fa-robot me-1"></i>Asistente de Citas
                <span class="badge bg-info" style="font-size:0.6rem;font-weight:400;vertical-align:middle;margin-left:4px">Beta</span>
            </div>
            <button class="btn btn-sm p-0 text-white fs-5" onclick="toggleAdminIaChat()">&times;</button>
        </div>
        <p class="small text-muted px-3 py-1 mb-0" style="font-size:0.7rem;background:rgba(255,255,255,0.05)">Citas y actividad de {{ $user->name }}</p>
        <div id="admin-ia-chat-messages" class="admin-ia-chat-messages"></div>
        <div class="admin-ia-chat-input">
            <input type="text" id="admin-ia-chat-input" class="form-control form-control-sm" placeholder="Pregunta sobre las citas..." maxlength="2000">
            <button id="admin-ia-chat-send" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane me-1"></i><span class="btn-text">Enviar</span></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var container = document.getElementById('admin-ia-chat-messages');
    var input = document.getElementById('admin-ia-chat-input');
    var sendBtn = document.getElementById('admin-ia-chat-send');
    var panel = document.getElementById('admin-ia-chat-panel');
    var widget = document.getElementById('admin-ia-chat-widget');
    var chatUrl = widget.getAttribute('data-chat-url');
    var historialUrl = widget.getAttribute('data-historial-url');
    var isOpen = false;
    var historialCargado = false;

    function playNotifSound() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            var osc = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 660;
            osc.type = 'sine';
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.2);
        } catch(e) {}
    }

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function addIaMessage(role, text) {
        var div = document.createElement('div');
        div.className = 'admin-ia-message admin-ia-message-' + role;
        if (role === 'assistant') {
            div.innerHTML = '<i class="fa fa-robot me-1" style="color:#1266f1"></i>' + escapeHtml(text);
        } else {
            div.textContent = text;
        }
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function cargarHistorial() {
        if (historialCargado) return;
        container.innerHTML = '';
        fetch(historialUrl)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.length === 0) {
                    addIaMessage('assistant', 'Hola, pregunta sobre las citas, pacientes o actividad de este médico.');
                } else {
                    data.forEach(function(m) {
                        addIaMessage(m.role, m.content);
                    });
                }
                historialCargado = true;
            })
            .catch(function() {
                addIaMessage('assistant', 'Hola, pregunta sobre las citas de este médico.');
                historialCargado = true;
            });
    }

    window.toggleAdminIaChat = function() {
        isOpen = !isOpen;
        panel.classList.toggle('d-none', !isOpen);
        if (isOpen) {
            cargarHistorial();
            setTimeout(function() { container.scrollTop = container.scrollHeight; }, 100);
        }
    };

    function sendIaMessage() {
        var text = input.value.trim();
        if (!text) return;

        addIaMessage('user', text);
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        var thinkingTimer = setTimeout(function() {
            addIaMessage('assistant', '⏳ Pensando a fondo...');
        }, 30000);

        fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({message: text}),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            clearTimeout(thinkingTimer);
            var lastMsg = container.lastElementChild;
            if (lastMsg && lastMsg.textContent.includes('Pensando a fondo')) {
                lastMsg.remove();
            }
            if (data.error) {
                addIaMessage('assistant', '⚠️ ' + data.error);
            } else {
                addIaMessage('assistant', data.reply);
                if (!isOpen) playNotifSound();
            }
        })
        .catch(function() {
            clearTimeout(thinkingTimer);
            var lastMsg = container.lastElementChild;
            if (lastMsg && lastMsg.textContent.includes('Pensando a fondo')) {
                lastMsg.remove();
            }
            addIaMessage('assistant', '⚠️ Error de conexión. Intenta de nuevo.');
        })
        .finally(function() {
            input.disabled = false;
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i><span class="btn-text">Enviar</span>';
            input.focus();
        });
    }

    sendBtn.addEventListener('click', sendIaMessage);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') sendIaMessage();
    });

    if (window.location.hash === '#admin-ia-chat') {
        setTimeout(function() { toggleAdminIaChat(); }, 500);
    }
})();
</script>
<style>
.admin-ia-chat-widget { position:fixed; bottom:20px; left:20px; z-index:9999; font-family:inherit }
.admin-ia-chat-fab { width:52px;height:52px;border-radius:50%;border:none;background:#1266f1;color:#fff;font-size:1.3rem;cursor:pointer;box-shadow:0 4px 14px rgba(18,102,241,0.35);display:flex;align-items:center;justify-content:center;transition:transform 0.2s }
.admin-ia-chat-fab:hover { transform:scale(1.08) }
.admin-ia-chat-badge { position:absolute;top:-4px;right:-4px;background:#ff4444;color:#fff;font-size:0.55rem;padding:0.15rem 0.35rem;border-radius:6px;line-height:1 }
.admin-ia-chat-panel { position:absolute;bottom:65px;left:0;width:350px;max-height:480px;background:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.18);display:flex;flex-direction:column;overflow:hidden }
.admin-ia-chat-panel-header { display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;background:#1266f1;color:#fff;font-weight:700;font-size:0.85rem }
.admin-ia-chat-messages { height:300px;overflow-y:auto;padding:0.75rem;display:flex;flex-direction:column;gap:0.6rem;background:rgba(18,102,241,0.03) }
.admin-ia-message { max-width:88%;padding:0.5rem 0.85rem;border-radius:12px;font-size:0.82rem;line-height:1.5;word-wrap:break-word }
.admin-ia-message-user { align-self:flex-end;background:#1266f1;color:#fff;border-radius:12px 12px 4px 12px }
.admin-ia-message-assistant { align-self:flex-start;background:#e8f0fe;color:#121212;border-radius:12px 12px 12px 4px }
.admin-ia-chat-input { display:flex;gap:0.4rem;padding:0.6rem 0.75rem;border-top:1px solid rgba(18,102,241,0.1);background:#fff }
.admin-ia-chat-input input { border-radius:8px;font-size:0.82rem }
.admin-ia-chat-input button { border-radius:8px;white-space:nowrap;font-size:0.82rem }
</style>
@endpush
