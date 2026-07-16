@extends('layouts.app')

@section('title', 'Asistente IA')

@section('content')
<div class="container-fluid h-100 d-flex flex-column" style="min-height:calc(100vh - 100px)">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="fw-bold" style="color:#1266f1"><i class="fa fa-robot me-2"></i>Asistente de Medicamentos</h2>
            <p class="text-muted mb-0" style="font-size:1.1rem">Pregunta sobre tus medicamentos, citas, doctores o tratamientos.</p>
        </div>
    </div>

    <div id="paciente-chat-container" class="flex-grow-1 d-flex flex-column" style="background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden">
        <div id="paciente-chat-messages" class="flex-grow-1 p-4" style="overflow-y:auto;display:flex;flex-direction:column;gap:1rem;background:rgba(18,102,241,0.02)">
        </div>

        <div class="p-4" style="border-top:1px solid rgba(18,102,241,0.1);background:#fff">
            <form id="paciente-chat-form" autocomplete="off">
                @csrf
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input type="text" id="paciente-chat-input" class="form-control" placeholder="Escribe tu pregunta aquí..." style="font-size:1.1rem;padding:0.75rem 1rem;border-radius:12px;border:2px solid #e0e0e0" autofocus>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" id="paciente-chat-send" class="btn btn-primary w-100" style="font-size:1.1rem;padding:0.75rem 2rem;border-radius:12px;white-space:nowrap">
                            <i class="fa fa-paper-plane me-2"></i>Enviar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var container = document.getElementById('paciente-chat-messages');
    var input = document.getElementById('paciente-chat-input');
    var sendBtn = document.getElementById('paciente-chat-send');
    var form = document.getElementById('paciente-chat-form');
    var chatUrl = '{{ route("paciente.chat-ia") }}';
    var histUrl = '{{ route("paciente.chat-ia.historial") }}';
    var loaded = false;

    function esc(t){var d=document.createElement('div');d.textContent=t;return d.innerHTML}

    function mdToHtml(t){try{return marked.parse(t)}catch(e){return esc(t)}}

    function addMsg(role, text) {
        var div = document.createElement('div');
        div.style.cssText = 'max-width:80%;padding:1rem 1.5rem;border-radius:16px;font-size:1.05rem;line-height:1.6;word-wrap:break-word';
        if (role === 'user') {
            div.style.cssText += ';align-self:flex-end;background:#1266f1;color:#fff;border-radius:16px 16px 4px 16px';
            div.textContent = text;
        } else {
            div.className = 'paciente-ia-msg';
            div.style.cssText += ';align-self:flex-start;background:#e8f0fe;color:#121212;border-radius:16px 16px 16px 4px';
            div.innerHTML = '<i class="fa fa-robot me-2" style="color:#1266f1;font-size:1.2rem"></i>' + mdToHtml(text);
        }
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function cargarHistorial() {
        if (loaded) return;
        container.innerHTML = '';
        fetch(histUrl).then(function(r){return r.json()}).then(function(data){
            if (data.length === 0) {
                addMsg('assistant', 'Hola, soy tu asistente. Pregúntame sobre tus medicamentos, citas o doctores.');
            } else {
                data.forEach(function(m){addMsg(m.role, m.content)});
            }
            loaded = true;
        }).catch(function(){
            addMsg('assistant', 'Hola, soy tu asistente. Pregúntame sobre tus medicamentos, citas o doctores.');
            loaded = true;
        });
    }

    cargarHistorial();

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text) return;

        addMsg('user', text);
        input.value = '';
        input.disabled = true;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

        var tt1 = setTimeout(function(){
            addMsg('assistant','⏳ Pensando...');
        }, 10000);

        var tt2 = setTimeout(function(){
            var lastMsg = container.lastElementChild;
            if(lastMsg&&lastMsg.textContent.includes('Pensando...')){
                lastMsg.innerHTML = lastMsg.innerHTML.replace('⏳ Pensando...', '⏳ <span style="display:inline-flex;align-items:center;gap:0.5rem">Pensando profundamente <span class="spinner-border spinner-border-sm" style="width:1rem;height:1rem"></span></span>');
            }
        }, 30000);

        fetch(chatUrl, {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            body:JSON.stringify({message:text})
        })
        .then(function(r){return r.json()})
        .then(function(d){
            clearTimeout(tt1);clearTimeout(tt2);
            var lm=container.lastElementChild;
            if(lm&&(lm.textContent.includes('Pensando')))lm.remove();
            if(d.error)addMsg('assistant','⚠️ '+d.error);
            else addMsg('assistant',d.reply);
        })
        .catch(function(){
            clearTimeout(tt1);clearTimeout(tt2);
            var lm=container.lastElementChild;
            if(lm&&(lm.textContent.includes('Pensando')))lm.remove();
            addMsg('assistant','⚠️ Error de conexión. Intenta de nuevo.');
        })
        .finally(function(){
            input.disabled=false;
            sendBtn.disabled=false;
            sendBtn.innerHTML='<i class="fa fa-paper-plane me-2"></i>Enviar';
            input.focus();
        });
    });
})();
</script>
<style>
#paciente-chat-messages::-webkit-scrollbar { width:6px }
#paciente-chat-messages::-webkit-scrollbar-thumb { background:#ccc;border-radius:4px }
#paciente-chat-container { max-height:calc(100vh - 220px) }
.paciente-ia-msg strong { font-weight:700 }
.paciente-ia-msg em { font-style:italic }
.paciente-ia-msg u { text-decoration:underline }
.paciente-ia-msg h1, .paciente-ia-msg h2, .paciente-ia-msg h3, .paciente-ia-msg h4 { margin:0.5rem 0 0.25rem;font-weight:700;color:#0d47a1 }
.paciente-ia-msg h1 { font-size:1.2rem }
.paciente-ia-msg h2 { font-size:1.1rem }
.paciente-ia-msg h3 { font-size:1.05rem }
.paciente-ia-msg p { margin:0.25rem 0 }
.paciente-ia-msg ul, .paciente-ia-msg ol { margin:0.25rem 0;padding-left:1.5rem }
.paciente-ia-msg li { margin:0.15rem 0 }
.paciente-ia-msg table { border-collapse:collapse;width:100%;margin:0.5rem 0;font-size:0.9rem }
.paciente-ia-msg th, .paciente-ia-msg td { border:1px solid #d0d7de;padding:0.4rem 0.6rem;text-align:left }
.paciente-ia-msg th { background:#e8f0fe;font-weight:700 }
.paciente-ia-msg code { background:#f0f0f0;padding:0.1rem 0.3rem;border-radius:4px;font-size:0.9rem;font-family:monospace }
.paciente-ia-msg pre { background:#f5f5f5;padding:0.75rem;border-radius:8px;overflow-x:auto;margin:0.5rem 0 }
.paciente-ia-msg pre code { background:transparent;padding:0 }
.paciente-ia-msg blockquote { border-left:3px solid #1266f1;padding-left:0.75rem;margin:0.5rem 0;color:#555 }
.paciente-ia-msg hr { border:none;border-top:1px solid #e0e0e0;margin:0.75rem 0 }
</style>
@endpush
