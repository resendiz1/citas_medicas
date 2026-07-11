<div id="ia-chat-widget" class="ia-chat-widget">
    <button id="ia-chat-fab" class="ia-chat-fab" onclick="toggleIaChat()">
        <i class="fa fa-robot"></i>
        <span class="ia-chat-badge d-none">Beta</span>
    </button>
    <div id="ia-chat-panel" class="ia-chat-panel d-none">
        <div class="ia-chat-panel-header">
            <div>
                <i class="fa fa-robot me-1"></i>Asistente de Medicamentos
                <span class="badge bg-info" style="font-size:0.6rem;font-weight:400;vertical-align:middle;margin-left:4px">Beta</span>
            </div>
            <button class="btn btn-sm p-0 text-white fs-5" onclick="toggleIaChat()">&times;</button>
        </div>
        <p class="small text-muted px-3 py-1 mb-0" style="font-size:0.7rem;background:rgba(255,255,255,0.05)">Pregunta sobre tus medicamentos, dosis u horarios.</p>
        <div id="ia-chat-messages" class="ia-chat-messages">
            <div class="ia-message ia-message-ai">
                <i class="fa fa-robot me-1" style="color:#1266f1"></i>Hola, soy tu asistente. Pregúntame sobre tus tratamientos.
            </div>
        </div>
        <div class="ia-chat-input">
            <input type="text" id="ia-chat-input" class="form-control form-control-sm" placeholder="Ej. ¿Cada cuánto debo tomarlo?" maxlength="2000">
            <button id="ia-chat-send" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane me-1"></i><span class="btn-text">Enviar</span></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var messages = [];
    var container = document.getElementById('ia-chat-messages');
    var input = document.getElementById('ia-chat-input');
    var sendBtn = document.getElementById('ia-chat-send');
    var panel = document.getElementById('ia-chat-panel');
    var isOpen = false;

    window.toggleIaChat = function() {
        isOpen = !isOpen;
        panel.classList.toggle('d-none', !isOpen);
        if (isOpen && container) container.scrollTop = container.scrollHeight;
    };

    function addIaMessage(role, text) {
        var div = document.createElement('div');
        div.className = 'ia-message ia-message-' + role;
        if (role === 'ai') {
            div.innerHTML = '<i class="fa fa-robot me-1" style="color:#1266f1"></i>' + escapeHtml(text);
        } else {
            div.textContent = text;
        }
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function sendIaMessage() {
        var text = input.value.trim();
        if (!text) return;

        addIaMessage('user', text);
        messages.push({role: 'user', content: text});
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('{{ route("paciente.chat-ia") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({messages: messages}),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                addIaMessage('ai', '⚠️ ' + data.error);
            } else {
                addIaMessage('ai', data.reply);
                messages.push({role: 'assistant', content: data.reply});
            }
        })
        .catch(function() {
            addIaMessage('ai', '⚠️ Error de conexión. Intenta de nuevo.');
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

    // If hash #ia-chat is present, open automatically
    if (window.location.hash === '#ia-chat') {
        setTimeout(function() { toggleIaChat(); }, 500);
    }
})();
</script>
<style>
.ia-chat-widget { position:fixed; bottom:20px; left:20px; z-index:9999; font-family:inherit }
.ia-chat-fab { width:52px;height:52px;border-radius:50%;border:none;background:#1266f1;color:#fff;font-size:1.3rem;cursor:pointer;box-shadow:0 4px 14px rgba(18,102,241,0.35);display:flex;align-items:center;justify-content:center;transition:transform 0.2s }
.ia-chat-fab:hover { transform:scale(1.08) }
.ia-chat-badge { position:absolute;top:-4px;right:-4px;background:#ff4444;color:#fff;font-size:0.55rem;padding:0.15rem 0.35rem;border-radius:6px;line-height:1 }
.ia-chat-panel { position:absolute;bottom:65px;left:0;width:350px;max-height:480px;background:#fff;border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.18);display:flex;flex-direction:column;overflow:hidden }
.ia-chat-panel-header { display:flex;justify-content:space-between;align-items:center;padding:0.75rem 1rem;background:#1266f1;color:#fff;font-weight:700;font-size:0.85rem }
.ia-chat-messages { height:300px;overflow-y:auto;padding:0.75rem;display:flex;flex-direction:column;gap:0.6rem;background:rgba(18,102,241,0.03) }
.ia-message { max-width:88%;padding:0.5rem 0.85rem;border-radius:12px;font-size:0.82rem;line-height:1.5;word-wrap:break-word }
.ia-message-ai { align-self:flex-start;background:#e8f0fe;color:#121212;border-radius:12px 12px 12px 4px }
.ia-message-user { align-self:flex-end;background:#1266f1;color:#fff;border-radius:12px 12px 4px 12px }
.ia-chat-input { display:flex;gap:0.4rem;padding:0.6rem 0.75rem;border-top:1px solid rgba(18,102,241,0.1);background:#fff }
.ia-chat-input input { border-radius:8px;font-size:0.82rem }
.ia-chat-input button { border-radius:8px;white-space:nowrap;font-size:0.82rem }
</style>
@endpush
