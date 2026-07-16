<div id="chat-widget" class="chat-widget"
     data-chat-citas-url="{{ route('chat.citas') }}"
     data-user-id="{{ auth()->id() }}">
    <button id="chat-fab" class="chat-fab" onclick="toggleChatWidget()">
        <i class="fas fa-comment-dots"></i>
        <span id="chat-badge" class="chat-badge d-none">0</span>
    </button>
    <div id="chat-panel" class="chat-panel d-none">
        <div class="chat-panel-header text-white fw-bold">
            <div style="display:flex;align-items:center;gap:0.3rem;flex:1;min-width:0">
                <span id="chat-unread-dot" class="d-none" style="width:8px;height:8px;border-radius:50%;background:#ff4444;flex-shrink:0"></span>
                <select id="chat-cita-select" class="form-select form-select-sm bg-transparent text-white border-0 fw-bold p-0" style="font-size:0.8rem;max-width:220px;cursor:pointer;flex:1;min-width:0">
                    <option value="">Cargando...</option>
                </select>
                <i class="fas fa-chevron-down" style="font-size:0.65rem;color:#fff;opacity:0.7;cursor:pointer;flex-shrink:0" onclick="var s=document.getElementById('chat-cita-select');s.showPicker?s.showPicker():s.focus()"></i>
            </div>
            <button class="btn btn-sm p-0 text-white fs-5" onclick="toggleChatWidget()">&times;</button>
        </div>
        <div id="chat-mensajes" class="chat-mensajes" style="max-height:400px;overflow-y:auto;display:flex;flex-direction:column;gap:0.5rem;padding:0.5rem;background:rgba(0,0,0,0.15);border-radius:0;flex:1"></div>
        <div class="chat-widget-input">
            <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Escribe un mensaje..." maxlength="2000">
            <button id="chat-send" class="btn btn-primary btn-sm"><i class="fa fa-paper-plane me-1"></i><span class="btn-text">Enviar</span></button>
        </div>
    </div>
</div>
