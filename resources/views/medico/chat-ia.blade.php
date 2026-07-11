@extends('layouts.app')

@section('title', 'Asistente IA')

@section('content')
<div class="container-fluid h-100 d-flex flex-column" style="min-height:calc(100vh - 100px)">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="fw-bold" style="color:#1266f1"><i class="fa fa-robot me-2"></i>Asistente de Citas</h2>
            <p class="text-muted mb-0" style="font-size:1.1rem">Pregunta sobre tus pacientes, citas, diagnósticos o tratamientos.</p>
        </div>
    </div>

    <div id="medico-chat-container" class="flex-grow-1 d-flex flex-column" style="background:#fff;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden">
        <div id="medico-chat-messages" class="flex-grow-1 p-4" style="overflow-y:auto;display:flex;flex-direction:column;gap:1rem;background:rgba(18,102,241,0.02)">
        </div>

        <div class="p-4" style="border-top:1px solid rgba(18,102,241,0.1);background:#fff">
            <form id="medico-chat-form" class="d-flex gap-3" autocomplete="off">
                @csrf
                <input type="text" id="medico-chat-input" class="form-control" placeholder="Escribe tu pregunta aquí..." style="font-size:1.1rem;padding:0.75rem 1rem;border-radius:12px;border:2px solid #e0e0e0" autofocus>
                <button type="submit" id="medico-chat-send" class="btn btn-primary" style="font-size:1.1rem;padding:0.75rem 2rem;border-radius:12px;white-space:nowrap">
                    <i class="fa fa-paper-plane me-2"></i>Enviar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var container = document.getElementById('medico-chat-messages');
    var input = document.getElementById('medico-chat-input');
    var sendBtn = document.getElementById('medico-chat-send');
    var form = document.getElementById('medico-chat-form');
    var chatUrl = '{{ route("medico.chat-ia.send") }}';
    var histUrl = '{{ route("medico.chat-ia.historial") }}';
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
            div.className = 'medico-ia-msg';
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
                addMsg('assistant', 'Hola, soy tu asistente. Pregúntame sobre tus pacientes, citas o tratamientos.');
            } else {
                data.forEach(function(m){addMsg(m.role, m.content)});
            }
            loaded = true;
        }).catch(function(){
            addMsg('assistant', 'Hola, soy tu asistente. Pregúntame sobre tus pacientes, citas o tratamientos.');
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
#medico-chat-messages::-webkit-scrollbar { width:6px }
#medico-chat-messages::-webkit-scrollbar-thumb { background:#ccc;border-radius:4px }
#medico-chat-container { max-height:calc(100vh - 220px) }
.medico-ia-msg strong { font-weight:700 }
.medico-ia-msg em { font-style:italic }
.medico-ia-msg u { text-decoration:underline }
.medico-ia-msg h1, .medico-ia-msg h2, .medico-ia-msg h3, .medico-ia-msg h4 { margin:0.5rem 0 0.25rem;font-weight:700;color:#0d47a1 }
.medico-ia-msg h1 { font-size:1.2rem }
.medico-ia-msg h2 { font-size:1.1rem }
.medico-ia-msg h3 { font-size:1.05rem }
.medico-ia-msg p { margin:0.25rem 0 }
.medico-ia-msg ul, .medico-ia-msg ol { margin:0.25rem 0;padding-left:1.5rem }
.medico-ia-msg li { margin:0.15rem 0 }
.medico-ia-msg table { border-collapse:collapse;width:100%;margin:0.5rem 0;font-size:0.9rem }
.medico-ia-msg th, .medico-ia-msg td { border:1px solid #d0d7de;padding:0.4rem 0.6rem;text-align:left }
.medico-ia-msg th { background:#e8f0fe;font-weight:700 }
.medico-ia-msg code { background:#f0f0f0;padding:0.1rem 0.3rem;border-radius:4px;font-size:0.9rem;font-family:monospace }
.medico-ia-msg pre { background:#f5f5f5;padding:0.75rem;border-radius:8px;overflow-x:auto;margin:0.5rem 0 }
.medico-ia-msg pre code { background:transparent;padding:0 }
.medico-ia-msg blockquote { border-left:3px solid #1266f1;padding-left:0.75rem;margin:0.5rem 0;color:#555 }
.medico-ia-msg hr { border:none;border-top:1px solid #e0e0e0;margin:0.75rem 0 }
</style>
@endpush
