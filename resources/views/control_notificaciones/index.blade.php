@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Control de Notificaciones</h1>
    <p class="mb-4">Conexión base URL: <strong id="urlbase">{{ $urlbase ?? '' }}</strong></p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Status Panel -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 transition-shadow hover:shadow-lg">
            <h2 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2 justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Estado
                </div>
                <div id="socketStatus" class="text-xs px-2 py-1 rounded-full bg-gray-200 text-gray-600 flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    Desconectado
                </div>
            </h2>
            <button id="btnStatus" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors mb-3">Consultar Estado</button>
            <pre id="statusArea" class="text-xs bg-gray-50 p-3 rounded-lg border border-gray-200 overflow-auto max-h-40 font-mono text-gray-600"></pre>
        </div>

        <!-- Account Info Panel -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 transition-shadow hover:shadow-lg">
            <h2 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Información de Cuenta
            </h2>
            <button id="btnMe" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors mb-3">Consultar Info</button>
            <pre id="meArea" class="text-xs bg-gray-50 p-3 rounded-lg border border-gray-200 overflow-auto max-h-40 font-mono text-gray-600"></pre>
        </div>

        <!-- QR Panel -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 transition-shadow hover:shadow-lg">
            <h2 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                Código QR
            </h2>
            <div id="qrArea" class="flex flex-col justify-center items-center bg-gray-50 rounded-lg p-4 border border-gray-200 min-h-[200px]">
                <div id="qrRender" class="flex justify-center items-center"></div>
                <img id="qrImg" src="" alt="QR" class="hidden max-h-48 object-contain" />
                <div id="qrPlaceholder" class="text-sm text-gray-400 mt-2 flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    QR no disponible
                </div>
                <div id="qrUpdatedAt" class="text-xs text-gray-400 mt-2"></div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 justify-center">
                <button id="btnQr" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium transition-colors">Refrescar</button>
                <button id="btnGenQr" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">Generar Nuevo</button>
                <a id="btnDownloadQr" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-medium transition-colors hidden" download="qr.png">Descargar</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chats List -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 col-span-1 flex flex-col h-[600px]">
            <h2 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2 justify-between">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <div class="flex items-center gap-2">
                    <span>Chats</span>
                    <span id="chatsCount" class="text-xs text-gray-500">0 chats</span>
                </div>
            </h2>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-sm text-gray-500">Mostrar:</span>
                <div class="inline-flex rounded-md bg-gray-100 p-1">
                    <button id="chat-filter-all" class="px-3 py-1 text-sm text-gray-700 rounded-md bg-white font-medium">Todos</button>
                    <button id="chat-filter-active" class="px-3 py-1 text-sm text-gray-700 rounded-md">Activos</button>
                </div>
            </div>
            <div id="chatsList" class="space-y-2 overflow-y-auto flex-1 pr-2 custom-scrollbar"></div>
        </div>

        <!-- Chat Interface -->
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 col-span-2 flex flex-col h-[600px]">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <div>
                    <h2 class="font-bold text-lg text-gray-800">Chat</h2>
                    <div id="chatHeader" class="text-sm text-gray-500 font-medium h-5">Selecciona un chat</div>
                </div>
                <div id="chatMeta" class="text-xs text-gray-500"></div>
                <div class="ml-3">
                    <button id="btnDeleteChat" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">Eliminar chat</button>
                </div>
            </div>
            
            <div id="chatMessages" class="flex-1 overflow-y-auto bg-gray-50 p-4 rounded-xl border border-gray-200 mb-4 custom-scrollbar">
                <div class="h-full flex items-center justify-center text-gray-400 text-sm">
                    Selecciona un chat para ver los mensajes
                </div>
            </div>
            
            <div class="flex gap-2 items-center mb-3">
                <input id="chatInput" class="flex-1 rounded-lg border-gray-300 border px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" placeholder="Escribe tu mensaje aquí..." />
                <button id="btnSendFile" class="p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition-colors" title="Enviar archivo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                </button>
                <button id="btnSend" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2">
                    <span>Enviar</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
            
            <div class="flex flex-wrap gap-2 pt-3 border-t">
                <button id="btnBlock" class="px-4 py-2 bg-orange-100 hover:bg-orange-200 text-orange-700 rounded-lg text-sm font-medium transition-colors">Bloquear</button>
                <!-- Rechazar llamada removido: las llamadas ahora son gestionadas por Baileys (se bloquean por defecto) -->
                <div class="flex-1"></div>
                <button id="btnDeleteSession" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">Eliminar sesión</button>
            </div>
        </div>
    </div>
    
    <template id="chat-item-template">
        <div class="chat-item p-3 cursor-pointer border border-gray-100 rounded-lg hover:bg-blue-50 transition-colors flex flex-col gap-1 mb-2">
            <div class="chat-jid font-semibold text-gray-800 truncate text-sm"></div>
            <div class="chat-last text-xs text-gray-500 truncate"></div>
            <div class="mt-2 flex justify-end">
                <button type="button" class="chat-delete text-red-600 hover:text-red-800 text-xs px-2 py-1 rounded-md border border-red-100 bg-red-50 hidden" title="Eliminar chat">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 inline-block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H3a1 1 0 100 2h14a1 1 0 100-2h-2V3a1 1 0 00-1-1H6zm2 6a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                </button>
            </div>
        </div>
    </template>

    <input type="file" id="fileUpload" class="hidden" />

    <!-- Toast container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <!-- Modal for confirm/prompt -->
    <div id="modalOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm items-center justify-center z-50 transition-opacity duration-300">
        <div id="modalBox" class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 transform scale-100 transition-transform duration-300">
            <h3 id="modalTitle" class="text-xl font-bold text-gray-800 mb-3"></h3>
            <div id="modalBody" class="text-gray-600 mb-6 leading-relaxed"></div>
            <input id="modalInput" class="w-full rounded-lg border-gray-300 border px-4 py-2.5 mb-6 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all hidden" />
            <div class="flex justify-end gap-3">
                <button id="modalCancel" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">Cancelar</button>
                <button id="modalConfirm" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-md hover:shadow-lg transition-all">Confirmar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
const urlbase = document.getElementById('urlbase').innerText.trim();
// Use the socket URL passed from the controller
const socketUrl = '{{ $socketUrl }}';

if (!urlbase) {
    console.warn('NOTIFICATIONS_URL_BASE not set');
}

    function apiFetch(endpoint, opts = {}){
    // If opts.skipBase is true, don't prepend the socket urlbase (use absolute app path)
    const base = (opts && opts.skipBase) ? '' : (urlbase || '');
    const url = base + endpoint;
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : null;
    opts = opts || {};
    // ensure credentials are sent so Laravel can access session cookie
    if (!opts.credentials) opts.credentials = 'same-origin';
    // default headers
    opts.headers = opts.headers || {};
    if (!opts.headers['X-Requested-With']) opts.headers['X-Requested-With'] = 'XMLHttpRequest';
    if (!opts.headers['Accept']) opts.headers['Accept'] = 'application/json';
    if (csrf && !opts.headers['X-CSRF-TOKEN']) opts.headers['X-CSRF-TOKEN'] = csrf;

        return fetch(url, opts).then(async res => {
        if (res.status === 419) {
            showToast('error','Sesión expirada o token CSRF inválido. Recarga la página para actualizar la sesión.');
            // Optionally reload to refresh CSRF token
            // location.reload();
            throw new Error('CSRF token mismatch (419)');
        }
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) return res.json();
        const text = await res.text();
        return text;
    }).catch(err => {
        console.error('apiFetch error', err);
        throw err;
    });
}

    function isBase64String(str) {
    if (typeof str !== 'string') return false;
    const s = str.trim().replace(/\s+/g, '');
    // Quick heuristic for base64: length multiple of 4 and allowed characters
        return s.length % 4 === 0 && /^[A-Za-z0-9+/=]+$/.test(s);
}

        // Helper: if the string is multiple base64 segments separated by commas (WhatsApp style),
        // join them into one base64 string and return it. Otherwise return null.
        function joinBase64CommaSegments(str) {
            if (!str || typeof str !== 'string') return null;
            if (!str.includes(',')) return null;
            const parts = str.split(',').map(p => p.trim()).filter(Boolean);
            if (parts.length < 2) return null;
            // Accept parts that may contain non-base64 prefixes (e.g. "2@") or stray characters.
            // Filter each part to base64 chars only.
            const filtered = parts.map(p => p.replace(/[^A-Za-z0-9+/=]/g, ''));
            const chunkRegex = /^[A-Za-z0-9+/=]+$/;
            for (const p of filtered) {
                if (!p || !chunkRegex.test(p)) return null;
            }
            const joined = filtered.join('');
            if (joined.length % 4 !== 0) return null;
            return joined;
        }

    function setQrSrcFromValue(val) {
    const img = document.getElementById('qrImg');
    const placeholder = document.getElementById('qrPlaceholder');
    if (!img) return;
    if (!val) { img.classList.add('hidden'); img.src = ''; return; }
    // hide placeholder by default, we'll show it when there is no QR
    if (placeholder) placeholder.classList.add('hidden');
    if (typeof val === 'string') {
        const trimmed = val.trim();
        if (trimmed.startsWith('data:image')) {
            img.src = trimmed;
            img.classList.remove('hidden');
            return;
        }
        if (isBase64String(trimmed)) {
            const src = 'data:image/png;base64,' + trimmed.replace(/\s/g, '');
            img.src = src;
            img.classList.remove('hidden');
            if (document.getElementById('qrRender')) document.getElementById('qrRender').innerHTML = '';
            const dl = document.getElementById('btnDownloadQr'); if (dl) { dl.classList.remove('hidden'); dl.href = src; }
            console.log('setQrSrcFromValue: set from raw base64 trimmed length', trimmed.length);
            return;
        }
        // Some services (WhatsApp) return base64 in comma-separated chunks. Try joining them.
        const joined = joinBase64CommaSegments(trimmed);
        if (joined) {
            const src = 'data:image/png;base64,' + joined;
            img.src = src;
            img.classList.remove('hidden');
            if (document.getElementById('qrRender')) document.getElementById('qrRender').innerHTML = '';
            const dl = document.getElementById('btnDownloadQr'); if (dl) { dl.classList.remove('hidden'); dl.href = src; }
            console.log('setQrSrcFromValue: set from joined base64, parts', trimmed.split(',').length);
            return;
        }
        // unknown string - hide and show placeholder
        img.classList.add('hidden');
        img.src = '';
        if (placeholder) { placeholder.innerText = 'QR no disponible'; placeholder.classList.remove('hidden'); }
        return;
    }
    if (typeof val === 'object') {
        // support new formats: dataUrl and svg
        if (val.dataUrl) {
            if (placeholder) placeholder.classList.add('hidden');
            img.src = val.dataUrl;
            img.classList.remove('hidden');
            if (document.getElementById('qrRender')) document.getElementById('qrRender').innerHTML = '';
            const dl = document.getElementById('btnDownloadQr'); if (dl) { dl.classList.remove('hidden'); dl.href = val.dataUrl; }
            return;
        }
        if (val.svg) {
            // show svg
            img.classList.add('hidden');
            img.src = '';
            if (placeholder) placeholder.classList.add('hidden');
            if (document.getElementById('qrRender')) {
                document.getElementById('qrRender').innerHTML = val.svg;
            } else {
                // fallback: insert SVG replacing img
                const container = document.getElementById('qrArea');
                container.innerHTML = val.svg + (document.getElementById('qrUpdatedAt') ? '<div id="qrUpdatedAt" class="text-xs text-gray-500 mt-2"></div>' : '');
            }
                    try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = true; } catch(e) {}
            const dl = document.getElementById('btnDownloadQr'); if (dl) { dl.classList.add('hidden'); }
            return;
        }
        if (val.qr) {
            setQrSrcFromValue(val.qr);
            return;
        }
        if (val.base64) {
            setQrSrcFromValue(val.base64);
            return;
        }
        // object returned but no known field - show a debug placeholder
        img.classList.add('hidden');
        img.src = '';
        if (placeholder) { placeholder.innerText = 'QR no disponible (Respuesta: ' + JSON.stringify(val).slice(0, 200) + ')'; placeholder.classList.remove('hidden'); }
        return;
    }
}

    // Global error handlers to surface runtime errors
    window.addEventListener('error', (ev) => {
        try { if (ev && ev.error && ev.error.message) showToast('error', 'JS error: ' + ev.error.message); } catch(e){}
        console.error('Global Error', ev);
    });
    window.addEventListener('unhandledrejection', (ev) => { try{ showToast('error', 'Unhandled promise rejection'); }catch(e){}; console.warn('Unhandled rejection', ev); });

    // UI helpers - toasts and modal
    function showToast(type, message, timeout = 6000){
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const id = 't-' + Date.now();
        const bg = type === 'error' ? 'bg-red-500' : (type === 'success' ? 'bg-green-500' : 'bg-blue-500');
        const el = document.createElement('div');
        el.id = id;
        el.className = 'text-white px-3 py-2 rounded shadow ' + bg + ' flex items-center space-x-2';
        el.innerHTML = '<div class="text-sm">' + message + '</div><button class="ml-2 text-xs opacity-80">Cerrar</button>';
        const closeBtn = el.querySelector('button');
        closeBtn.addEventListener('click', ()=>{ container.removeChild(el); });
        container.appendChild(el);
        setTimeout(()=>{ if (container.contains(el)) container.removeChild(el); }, timeout);
    }

    function showModal({title = '', body = '', showInput = false}){
        return new Promise((resolve)=>{
            const overlay = document.getElementById('modalOverlay');
            const titleEl = document.getElementById('modalTitle');
            const bodyEl = document.getElementById('modalBody');
            const input = document.getElementById('modalInput');
            const confirmBtn = document.getElementById('modalConfirm');
            const cancelBtn = document.getElementById('modalCancel');
            titleEl.innerText = title;
            bodyEl.innerText = body;
            if (showInput) { input.classList.remove('hidden'); input.value = ''; } else { input.classList.add('hidden'); }
            const hideModal = ()=>{ overlay.classList.add('hidden'); overlay.classList.remove('flex'); };
            overlay.classList.remove('hidden'); overlay.classList.add('flex');
            const onConfirm = ()=>{
                hideModal();
                cleanup();
                resolve(showInput ? input.value : true);
            };
            const onCancel = ()=>{
                hideModal();
                cleanup();
                resolve(showInput ? null : false);
            };
            const cleanup = ()=>{ confirmBtn.removeEventListener('click', onConfirm); cancelBtn.removeEventListener('click', onCancel); };
            confirmBtn.addEventListener('click', onConfirm);
            cancelBtn.addEventListener('click', onCancel);
        });
    }

    function setLoading(btn, loading = true, label = null){
        if (!btn) return;
        if (loading) {
            btn.disabled = true;
            btn.dataset._label = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>' + (label || '');
        } else {
            btn.disabled = false;
            if (btn.dataset._label) btn.innerHTML = btn.dataset._label;
        }
    }

    // Replace alerts with toasts or modals by wrapping most interactions below

    document.getElementById('btnStatus').addEventListener('click', async () => {
    const content = await apiFetch('/status');
    document.getElementById('statusArea').innerText = JSON.stringify(content, null, 2);
});

document.getElementById('btnMe').addEventListener('click', async () => {
    const content = await apiFetch('/me');
    document.getElementById('meArea').innerText = JSON.stringify(content, null, 2);
});

    document.getElementById('btnQr').addEventListener('click', async () => {
        const btn = document.getElementById('btnQr');
        try {
            setLoading(btn, true, 'Refrescando QR');
            
            // Use the image endpoint directly to display the converted image
            const img = document.getElementById('qrImg');
            const placeholder = document.getElementById('qrPlaceholder');
            const timestamp = new Date().getTime();
            // We use the proxy endpoint with format=image. The controller will fetch base64 from upstream and convert it.
            const src = '/admin/notificaciones/qr?format=image&t=' + timestamp;
            
            // Preload to ensure it exists
            const tempImg = new Image();
            tempImg.onload = function() {
                img.src = src;
                img.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                if (document.getElementById('qrRender')) document.getElementById('qrRender').innerHTML = '';
                const dl = document.getElementById('btnDownloadQr'); 
                if (dl) { dl.classList.remove('hidden'); dl.href = src; }
                
                showToast('success', 'QR actualizado.');
                if (document.getElementById('qrUpdatedAt')) document.getElementById('qrUpdatedAt').innerText = 'Última actualización: ' + new Date().toLocaleString();
                setLoading(btn, false);
            };
            tempImg.onerror = function() {
                // If image fails, maybe it's not available or session exists
                img.classList.add('hidden');
                if (placeholder) {
                    placeholder.innerText = 'QR no disponible o error al cargar';
                    placeholder.classList.remove('hidden');
                }
                showToast('error', 'No se pudo cargar la imagen del QR');
                setLoading(btn, false);
            };
            tempImg.src = src;

        } catch (e) {
            showToast('error', 'Error al iniciar carga de QR');
            setLoading(btn, false);
        }
    });

    document.getElementById('btnGenQr').addEventListener('click', async () => {
        const btn = document.getElementById('btnGenQr');
        try {
            setLoading(btn, true, 'Solicitando...');
            const payload = await apiFetch('/generate-qr', {method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ format: 'png' })});
            // If backend returned dataUrl or svg in payload, display it
            if (payload && (payload.dataUrl || payload.svg || payload.qr)) {
                setQrSrcFromValue(payload);
            }
            showToast('success', 'Solicitud enviada. Generando QR...');
            // then refresh
            document.getElementById('btnQr').click();
        } catch (e) {
            showToast('error', 'Error al solicitar generacion de QR');
        } finally {
            setLoading(btn, false);
        }
    });

const chatsListEl = document.getElementById('chatsList');
let selectedJid = null;
// Chat list filter: show only active chats or all chats (default: all)
const chatFilterAllBtn = document.getElementById('chat-filter-all');
const chatFilterActiveBtn = document.getElementById('chat-filter-active');
let chatFilterOnlyActive = false; // false = all chats, true = only active chats

function setChatFilter(onlyActive) {
    chatFilterOnlyActive = !!onlyActive;
    if (chatFilterAllBtn && chatFilterActiveBtn) {
        if (chatFilterOnlyActive) {
            chatFilterActiveBtn.classList.add('bg-white', 'font-medium', 'text-gray-800', 'shadow-sm');
            chatFilterAllBtn.classList.remove('bg-white', 'font-medium', 'text-gray-800', 'shadow-sm');
        } else {
            chatFilterAllBtn.classList.add('bg-white', 'font-medium', 'text-gray-800', 'shadow-sm');
            chatFilterActiveBtn.classList.remove('bg-white', 'font-medium', 'text-gray-800', 'shadow-sm');
        }
    }
}

if (chatFilterAllBtn) chatFilterAllBtn.addEventListener('click', () => { setChatFilter(false); loadChats(); });
if (chatFilterActiveBtn) chatFilterActiveBtn.addEventListener('click', () => { setChatFilter(true); loadChats(); });

// Default to all chats
setChatFilter(false);

// Load mapping from system clients (phone -> registered name). This helps show customer name instead of raw phone.
let clientsMap = {};
// Also expose the map on window so the console can inspect it (debug-friendly)
try { window.clientsMap = clientsMap; } catch(e) {}
// Normalized lookup function to attempt multiple formats and suffix matching
function lookupClient(phone) {
    if (!phone) return null;
    // normalize digits-only for robust matching (handle JIDs with '-1234' suffix)
    const normalized = ('' + phone).replace(/\D/g, '');
    if (!normalized) return null;
    if (clientsMap[normalized]) return clientsMap[normalized];
    // try without leading country code (591)
    const without591 = normalized.replace(/^591/, '');
    if (clientsMap[without591]) return clientsMap[without591];
    const with591 = '591' + without591;
    if (clientsMap[with591]) return clientsMap[with591];
    // fallback: match by suffix to handle weird formatting
    for (const k in clientsMap) {
        if (!k) continue;
        if (k.endsWith(normalized) || normalized.endsWith(k)) return clientsMap[k];
    }
    return null;
}
// expose lookupClient for console debugging
try { window.lookupClient = lookupClient; } catch(e) {}
const authPhone = @json($authPhone ?? null);
const authName = @json($authName ?? null);
async function loadClientsMap(){
    try {
        // First try admin endpoint (full clients map) if current user has permissions
        const res = await apiFetch('/admin/clientes/json', { skipBase: true });
        if (res && typeof res === 'object' && Object.keys(res).length > 0) {
            clientsMap = res;
            console.log('Loaded admin clients map with', Object.keys(res).length, 'entries');
            try { window.clientsMap = clientsMap; } catch(e) {}
            return;
        }
    } catch (e) {
        // If this fails, it's probably because the current user is not an admin
        console.warn('Admin clients map not available (not admin?), trying personal client info...', e);
    }

    try {
        // Fallback: fetch only the current user's client info (safe for non-admins)
        const res2 = await apiFetch('/clientes/info/json', { skipBase: true });
        if (res2 && typeof res2 === 'object' && Object.keys(res2).length > 0) {
            clientsMap = res2;
            console.log('Loaded personal client info for chat resolution');
            try { window.clientsMap = clientsMap; } catch(e) {}
            return;
        }
    } catch (e) {
        console.warn('Could not load personal client mapping', e);
    }
    // If both attempts failed, clientsMap remains empty
    // If we have the authenticated user's phone, ensure it's in the clients map so the UI shows the name
    try {
        if (authPhone) {
            const normalizedAuth = ('' + authPhone).replace(/\D/g, '');
            if (!clientsMap[normalizedAuth]) {
                clientsMap[normalizedAuth] = { id: null, nombre_completo: (authName || authPhone), telefono: normalizedAuth };
                // also add version with 591 prefix
                clientsMap['591' + normalizedAuth] = clientsMap[normalizedAuth];
                console.log('Inserted authPhone into clientsMap for display:', normalizedAuth);
                try { window.clientsMap = clientsMap; } catch(e) {}
            }
        }
    } catch (e) { console.warn('Error applying authPhone fallback to clientsMap', e); }
}

// Try to populate clients map before loading chats
loadClientsMap();
try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = true; } catch(e) {}
// Disable the delete chat button initially
try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = true; } catch(e) {}

async function loadChats(onlyActive = chatFilterOnlyActive){
    let chats;
    try {
        const endpoint = '/chats' + (onlyActive ? '?active=1' : '');
        console.log('Fetching chats from:', (urlbase || '') + endpoint);
        chats = await apiFetch(endpoint);
        console.log('Chats response:', chats);
        if (typeof chats === 'string') {
            // backend may return stringified JSON
            const trimmed = chats.trim();
            if (trimmed.startsWith('{') || trimmed.startsWith('[')) {
                try { chats = JSON.parse(trimmed); console.log('Parsed chats from string:', chats); } catch(e) { console.warn('Could not parse chats JSON string', e); }
            }
        }
    } catch (e) {
        showToast('error', 'Error al cargar chats: ' + (e.message || 'No se pudo conectar'));
        chatsListEl.innerHTML = '';
        return;
    }
    console.log('chatsListEl exists?', !!chatsListEl, 'id:', chatsListEl ? chatsListEl.id : null);
    chatsListEl.innerHTML = '';
    // If upstream returns an object wrapper { chats: [...] } or { ok: true, chats: {...} }
    if (!Array.isArray(chats) && chats && chats.chats && Array.isArray(chats.chats)) {
        // Some services wrap list under 'chats'
        chats = chats.chats;
    } else if (!Array.isArray(chats) && chats && chats.chats && typeof chats.chats === 'object') {
        // Some services return a mapping: { chats: { jid: <meta> } }
        chats = Object.entries(chats.chats).map(([k, v]) => ({ jid: k, id: k, name: k, last_message_preview: (typeof v === 'string' ? v : ''), unread: (typeof v === 'number' ? v : 0) }));
    }
    if (!Array.isArray(chats) && chats && chats.data && Array.isArray(chats.data)) {
        // Some proxies return { data: [...] }
        chats = chats.data;
    }
    // If upstream returned an object map like { jid: 1, jid2: 1 }, convert it to an array
    if (!Array.isArray(chats) && chats && typeof chats === 'object') {
        // Avoid converting objects that are clearly not a chat map (e.g. { ok: true })
        const keys = Object.keys(chats);
        // Heuristic: if keys look like jids or contain @s.whatsapp.net, treat as mapping
        const looksLikeChatMap = keys.length > 0 && keys.every(k => k.includes('@') || /^\d+$/.test(k));
        if (looksLikeChatMap) {
            chats = Object.entries(chats).map(([k, v]) => ({ jid: k, id: k, name: k, last_message_preview: (typeof v === 'string' ? v : ''), unread: (typeof v === 'number' ? v : 0) }));
        }
    }
    if (!Array.isArray(chats)) {
        if (chats && chats.error) showToast('error', chats.message || 'Servicio de notificaciones no disponible');
        return;
    }
    for (const c of chats) {
        console.log('rendering chat item:', c);
        try {
        const t = document.getElementById('chat-item-template').content.cloneNode(true);
        const root = t.querySelector('.chat-item');
        const jidVal = (c && (c.jid || c.id)) || (typeof c === 'string' ? c : null);
        const phoneFromJid = jidVal && jidVal.includes('@') ? jidVal.split('@')[0] : jidVal;
        // normalize digits-only (strip hyphen suffixes etc.)
        const phoneNormalized = phoneFromJid ? (('' + phoneFromJid).replace(/\D/g, '')) : null;
        // Try to find a client entry in clientsMap with multiple normalization fallbacks
        const cliente = lookupClient(phoneNormalized || phoneFromJid);
        const pretty = cliente ? (cliente.nombre_completo || phoneFromJid) : ((c && (c.name || c.pushName || c.contactName)) || phoneFromJid);
        t.querySelector('.chat-jid').innerText = pretty || (typeof c === 'object' ? JSON.stringify(c).slice(0,80) : (c || ''));
        const lastPreview = (c && (c.last_message_preview || c.lastMessage || c.last_message)) || '';
        const unreadCount = (c && c.unread) ? c.unread : 0;
        t.querySelector('.chat-last').innerText = lastPreview + (unreadCount ? (' • ' + unreadCount + ' nuevos') : '');
        root.dataset.jid = jidVal || '';
        if (jidVal) root.addEventListener('click', ()=>selectChat(jidVal));
        // Show delete button for each chat and wire up handler
        const delBtn = t.querySelector('.chat-delete');
        if (delBtn) {
            delBtn.classList.remove('hidden');
            delBtn.addEventListener('click', async (ev) => {
                // Prevent click propagation to parent (select)
                ev.stopPropagation();
                const jid = jidVal || c.jid || c.id || c;
                const confirmed = await showModal({ title: 'Eliminar chat', body: '¿Está seguro de eliminar este chat? Esta acción borra carpeta y mensajes.' });
                    if (!confirmed) { showToast('info','Acción cancelada'); return; }
                try {
                    setLoading(delBtn, true, 'Eliminando');
                    const res = await apiFetch('/chats/' + encodeURIComponent(jid), { method: 'DELETE' });
                    if (res && res.error) { showToast('error', res.message || 'Error eliminando chat'); }
                    else {
                        showToast('success', 'Chat eliminado');
                        // If the deleted chat is currently selected, clear selection
                        if (selectedJid === jid) {
                            selectedJid = null;
                            document.getElementById('chatHeader').innerText = 'Selecciona un chat';
                            document.getElementById('chatMessages').innerHTML = '<div class="h-full flex items-center justify-center text-gray-400 text-sm">Selecciona un chat para ver los mensajes</div>';
                            try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = true; } catch(e) {}
                        }
                        // reload chats list
                        loadChats();
                    }
                } catch (e) {
                    showToast('error','Error eliminando chat');
                } finally {
                    setLoading(delBtn, false);
                }
            });
        }
        chatsListEl.appendChild(t);
        } catch (innerErr) {
            console.error('Error rendering chat item:', innerErr, 'item:', c);
        }
    }
    // Update subtle badge with chat count instead of showing a success toast continuously
    try { 
        const n = (chats?.length || 0);
        const el = document.getElementById('chatsCount');
        if (el) el.innerText = n === 1 ? '1 chat' : (n + ' chats');
    } catch(e) {}
}

async function selectChat(jid){
    console.log('selectChat called for jid:', jid);
    selectedJid = jid;
    const phone = jid && jid.includes('@') ? jid.split('@')[0] : jid;
    const phoneNormalized = phone ? (('' + phone).replace(/\D/g, '')) : null;
    const clientInfo = lookupClient(phoneNormalized || phone);
    document.getElementById('chatHeader').innerText = clientInfo ? (clientInfo.nombre_completo || phone) : phone;
    try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = false; } catch(e) {}
        document.getElementById('chatMeta').innerText = 'Cargando...';
    document.getElementById('chatMessages').innerHTML = 'Cargando...';
    Poll.getMessages(jid);
        // Enable delete button for selected chat
        try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = false; } catch(e) {}
        // Fetch extra meta information (blocked, call logs) if available
        try {
            const meta = await apiFetch('/chats/' + encodeURIComponent(jid));
            if (meta && meta.error) { showToast('error', meta.message || 'Error cargando información del chat'); return; }
            // If meta is array of messages, we might need a header; else, if object with meta keys
            if (meta && meta.blocked !== undefined) {
                document.getElementById('chatMeta').innerHTML = '<span>Bloqueado: <strong>' + (meta.blocked ? 'Sí' : 'No') + '</strong></span>';
                const btn = document.getElementById('btnBlock');
                if (btn) {
                    btn.innerText = meta.blocked ? 'Desbloquear' : 'Bloquear';
                }
            } else {
                document.getElementById('chatMeta').innerText = '';
            }
            if (meta && meta.call_logs) {
                document.getElementById('chatMeta').innerHTML += '<div>Registros de Llamadas:</div>' + meta.call_logs.map(c => '<div class="text-xs">' + c.callId + ' - ' + c.status + ' - ' + c.timestamp + '</div>').join('');
            }
        } catch(e){
            console.warn('Error cargando meta', e);
        }
}

class Poll {
    static interval = null;
    static async getMessages(jid){
        if (!jid) {
            document.getElementById('chatMessages').innerHTML = '<div class="h-full flex items-center justify-center text-gray-400 text-sm">Selecciona un chat para ver los mensajes</div>';
            return;
        }
        try {
            let msgs = await apiFetch('/chats/' + encodeURIComponent(jid));
            if (!Array.isArray(msgs) && msgs && msgs.messages && Array.isArray(msgs.messages)) {
                msgs = msgs.messages;
            }
            if (!Array.isArray(msgs) && msgs && msgs.data && Array.isArray(msgs.data)) {
                msgs = msgs.data;
            }
            if (msgs && msgs.error) { showToast('error', msgs.message || 'No se pudieron cargar mensajes'); document.getElementById('chatMessages').innerText = 'Error cargando mensajes'; return; }
            const container = document.getElementById('chatMessages');
            if (!Array.isArray(msgs)) {
                container.innerText = JSON.stringify(msgs, null, 2);
                return;
            }
            container.innerHTML = '';
            const wrapper = document.createElement('div');
            wrapper.className = 'flex flex-col space-y-3';
            
            for (const m of msgs) {
                const el = document.createElement('div');
                // Simple heuristic: if from contains 'myself' or isMe property exists
                const isMe = (m.sender && m.sender === 'me') || m.fromMe || (m.key && m.key.fromMe) || (m.from && (m.from === 'me'));
                
                el.className = `max-w-[85%] p-3 rounded-2xl shadow-sm text-sm ${isMe ? 'bg-blue-100 text-blue-900 self-end rounded-br-none' : 'bg-white text-gray-800 self-start rounded-bl-none border border-gray-100'}`;
                
                // Use client mapping for sender name when available
                let sender = m.pushName || m.from || m.sender || 'Desconocido';
                const senderPhone = (sender && sender.includes('@')) ? sender.split('@')[0] : (sender && /^\d+$/.test(sender) ? sender : null);
                if (!isMe && senderPhone) {
                    const map = lookupClient(senderPhone);
                    if (map) sender = map.nombre_completo || senderPhone;
                } else if (isMe) {
                    sender = 'Yo';
                } else if (sender && sender.includes('@')) {
                    sender = sender.split('@')[0];
                }
                
                // Handle message content safely
                // Parse message content from multiple shapes: prefer m.content (newer format)
                let msgContent = m.content || m.message || m.conversation || null;
                let contentText = '';
                try {
                    if (msgContent && typeof msgContent === 'object') {
                        if (msgContent.type === 'text' && msgContent.text) contentText = msgContent.text;
                        else if (msgContent.type === 'image' && msgContent.caption) contentText = msgContent.caption;
                        else if (msgContent.type === 'video' && msgContent.caption) contentText = msgContent.caption;
                        else if (msgContent.extendedTextMessage && msgContent.extendedTextMessage.text) contentText = msgContent.extendedTextMessage.text;
                        else if (msgContent.conversation) contentText = msgContent.conversation;
                        else contentText = JSON.stringify(msgContent);
                    } else if (typeof msgContent === 'string') {
                        contentText = msgContent;
                    }
                } catch (e) {
                    contentText = JSON.stringify(msgContent);
                }
                
                // Only show the message text bubble (sender shown in header only)
                el.innerHTML = `
                    <div class="leading-relaxed whitespace-pre-wrap break-words">${contentText}</div>
                `;
                wrapper.appendChild(el);
            }
            container.appendChild(wrapper);
            container.scrollTop = container.scrollHeight;
        } catch(e){
            document.getElementById('chatMessages').innerText = 'Error cargando mensajes.';
            console.error(e);
        }
    }
    static start(){
        if (this.interval) clearInterval(this.interval);
        this.interval = setInterval(()=>{
            if (selectedJid) this.getMessages(selectedJid);
            loadChats();
        }, 5000);
    }
}

    document.getElementById('btnSend').addEventListener('click', async () => {
    const text = document.getElementById('chatInput').value.trim();
    if (!selectedJid || text === '') return;
    const btn = document.getElementById('btnSend');
    try {
        setLoading(btn, true, 'Enviando');
        await apiFetch('/send', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ to: selectedJid.replace('@s.whatsapp.net', ''), message: text })
    });
    document.getElementById('chatInput').value = '';
    // immediately refresh
    Poll.getMessages(selectedJid);
        showToast('success', 'Mensaje enviado');
    } catch (e) {
        showToast('error', 'Error al enviar mensaje');
    } finally {
        setLoading(btn, false);
    }
});

    document.getElementById('btnBlock').addEventListener('click', async () => {
        if (!selectedJid) { showToast('error','Seleccione un chat'); return; }
        const currentText = document.getElementById('btnBlock').innerText.toLowerCase();
        const action = currentText.includes('desblo') ? 'unblock' : 'block';
        const to = selectedJid.replace('@s.whatsapp.net', '');
        const btn = document.getElementById('btnBlock');
        try {
            setLoading(btn, true, action === 'block' ? 'Bloqueando' : 'Desbloqueando');
            const res = await apiFetch('/block', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ to, action }) });
        // If the API returned a blocked state, update the UI accordingly
        if (res && res.blocked !== undefined) {
            if (btn) btn.innerText = res.blocked ? 'Desbloquear' : 'Bloquear';
            // show a friendly message
            showToast('success', 'Acción aplicada: ' + (res.blocked ? 'Bloqueado' : 'Desbloqueado'));
        } else if (btn) {
            // Fallback: toggle based on previous state
            btn.innerText = action === 'block' ? 'Desbloquear' : 'Bloquear';
            showToast('success', 'Acción aplicada: ' + action);
        }
        // Update meta
        try {
            const meta = await apiFetch('/chats/' + encodeURIComponent(selectedJid));
            if (meta && meta.blocked !== undefined) {
                document.getElementById('chatMeta').innerHTML = '<span>Bloqueado: <strong>' + (meta.blocked ? 'Sí' : 'No') + '</strong></span>';
            }
        } catch(e) { console.warn(e); showToast('error','Error actualizando estado del chat'); }
        // end inner try/catch for meta
        } catch (e) { showToast('error','Error al realizar acción de bloqueo/desbloqueo'); }
        finally { setLoading(document.getElementById('btnBlock'), false); }
    });

    // El botón y la acción de rechazar llamada fueron eliminados porque Baileys bloquea llamadas por defecto.

document.getElementById('btnSendFile').addEventListener('click', () => {
    document.getElementById('fileUpload').click();
});

    document.getElementById('fileUpload').addEventListener('change', async (evt) => {
    const f = evt.target.files[0];
    if (!selectedJid || !f) return;
    const reader = new FileReader();
        reader.onload = async (e)=>{
        const base64 = e.target.result.split(',')[1];
        const mimetype = f.type;
        const type = mimetype.startsWith('image/') ? 'image' : (mimetype.startsWith('video/') ? 'video' : 'document');
        const btn = document.getElementById('btnSendFile');
        try {
            setLoading(btn, true, 'Enviando...');
            await apiFetch('/send-file', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ to: selectedJid.replace('@s.whatsapp.net', ''), type, filename: f.name, mimetype, fileBase64: base64 })
        });
        Poll.getMessages(selectedJid);
            showToast('success','Archivo enviado');
        } catch (e) { showToast('error','Error enviando archivo'); }
        finally { setLoading(btn, false); }
    };
    reader.readAsDataURL(f);
});

    document.getElementById('btnDeleteSession').addEventListener('click', async () => {
        const confirmed = await showModal({ title:'Eliminar sesión', body:'¿Eliminar sesión? Esto borra archivos de autenticación.' });
        if (!confirmed) { showToast('info','Acción cancelada'); return; }
        const btn = document.getElementById('btnDeleteSession');
        try { setLoading(btn, true, 'Eliminando');
            await apiFetch('/delete-session', { method: 'POST' });
            showToast('success','Sesión eliminada');
        } catch (e) { showToast('error','Error eliminando sesión'); }
        finally { setLoading(btn, false); }
    });

    // Delete currently selected chat
    document.getElementById('btnDeleteChat').addEventListener('click', async () => {
        if (!selectedJid) { showToast('error','Seleccione un chat'); return; }
        const confirmed = await showModal({ title:'Eliminar chat', body:'¿Eliminar este chat? Esto borra carpeta y mensajes.' });
        if (!confirmed) { showToast('info','Acción cancelada'); return; }
        const btn = document.getElementById('btnDeleteChat');
        try {
            setLoading(btn, true, 'Eliminando');
            const res = await apiFetch('/chats/' + encodeURIComponent(selectedJid), { method: 'DELETE' });
            if (res && res.error) { showToast('error', res.message || 'Error eliminando chat'); }
                else {
                showToast('success','Chat eliminado');
                selectedJid = null;
                document.getElementById('chatHeader').innerText = 'Selecciona un chat';
                document.getElementById('chatMessages').innerHTML = '<div class="h-full flex items-center justify-center text-gray-400 text-sm">Selecciona un chat para ver los mensajes</div>';
                    try { if (document.getElementById('btnDeleteChat')) document.getElementById('btnDeleteChat').disabled = true; } catch(e) {}
                loadChats();
            }
        } catch (e) { showToast('error','Error eliminando chat'); }
        finally { setLoading(btn, false); }
    });

    // Start polling and load chats on page load
loadClientsMap().then(()=> loadChats()).then(()=>{
    Poll.start();
    // Try to load QR automatically on page load
    try { document.getElementById('btnQr').click(); } catch(e) {}
    // Initialize socket.io / Echo to receive real-time updates when available
    try {
        if (window.Echo) {
            // Initialize Echo listeners
            Echo.channel('chats').listen('NewChat', (e) => {
                // reload chat list
                loadChats();
            });
            if (selectedJid) {
                Echo.channel('chat.' + selectedJid).listen('NewMessage', (e) => {
                    Poll.getMessages(selectedJid);
                });
            }
            Echo.channel('qr-updates').listen('QrUpdated', (e) => {
                setQrSrcFromValue(e && e.qr ? e.qr : e);
            });
        } else if (window.io) {
            const sUrl = socketUrl || window.location.origin;
            console.log('Initializing Socket.io connection to:', sUrl);
            
            // Connect with transports explicitly set to avoid issues with some proxies
            const socket = io(sUrl, {
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionAttempts: 10
            });
            
            const updateSocketStatus = (status) => {
                const el = document.getElementById('socketStatus');
                if (!el) return;
                if (status === 'connected') {
                    el.className = 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-700 flex items-center gap-1';
                    el.innerHTML = '<div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>Conectado';
                } else if (status === 'disconnected') {
                    el.className = 'text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 flex items-center gap-1';
                    el.innerHTML = '<div class="w-2 h-2 rounded-full bg-red-500"></div>Desconectado';
                } else {
                    el.className = 'text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 flex items-center gap-1';
                    el.innerHTML = '<div class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></div>Conectando...';
                }
            };

            updateSocketStatus('connecting');
            
            socket.on('connect', () => {
                console.log('Socket connected successfully');
                updateSocketStatus('connected');
                showToast('success', 'Conectado al servidor de eventos');
            });

            socket.on('disconnect', () => {
                console.log('Socket disconnected');
                updateSocketStatus('disconnected');
            });

            socket.on('connect_error', (err) => {
                console.warn('Socket connection error:', err);
                updateSocketStatus('disconnected');
            });

            // Debug: Listen to all events
            socket.onAny((event, ...args) => {
                console.log(`Socket Event: ${event}`, args);
            });

            socket.on('chats', (data) => loadChats());
            
            // Handle QR updates
            const handleQrUpdate = (data) => {
                console.log('Socket: QR received', data ? 'data present' : 'null');
                // If we receive a QR, it means we are not connected or re-authenticating
                // Reset the placeholder to default if it was showing "Connected"
                const placeholder = document.getElementById('qrPlaceholder');
                if (placeholder && placeholder.innerText.includes('Conectado')) {
                    placeholder.innerHTML = `
                        <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        QR no disponible
                    `;
                }
                
                setQrSrcFromValue(data && data.qr ? data.qr : data);
                showToast('info', 'Código QR actualizado');
            };

            socket.on('qr', handleQrUpdate);
            socket.on('qr-updates', handleQrUpdate);

            // Function to handle successful connection UI
            const handleConnectionSuccess = () => {
                console.log('Connection confirmed');
                
                // Hide QR image
                const img = document.getElementById('qrImg');
                if (img) {
                    img.classList.add('hidden');
                    img.src = '';
                }
                
                // Show success message in placeholder
                const placeholder = document.getElementById('qrPlaceholder');
                if (placeholder && !placeholder.innerText.includes('Conectado')) {
                    placeholder.innerHTML = `
                        <div class="flex flex-col items-center text-green-600 animate-pulse">
                            <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-bold text-xl">¡Conectado Exitosamente!</span>
                            <span class="text-sm text-gray-500 mt-1">Ya puedes enviar y recibir mensajes</span>
                        </div>
                    `;
                    placeholder.classList.remove('hidden');
                    showToast('success', '¡Vinculación exitosa! Dispositivo conectado.');
                    
                    // Auto-refresh status and info
                    if(document.getElementById('btnStatus')) document.getElementById('btnStatus').click();
                    if(document.getElementById('btnMe')) document.getElementById('btnMe').click();
                }
            };

            // Handle Connection Success Events
            socket.on('connection-open', handleConnectionSuccess);
            socket.on('open', handleConnectionSuccess); // Some implementations use 'open'
            socket.on('connection.update', (data) => {
                if (data && (data.status === 'open' || data.connection === 'open')) {
                    handleConnectionSuccess();
                }
            });

            // Fallback: Poll for QR and Status every 5 seconds
            setInterval(async () => {
                const placeholder = document.getElementById('qrPlaceholder');
                const isConnectedUI = placeholder && placeholder.innerText.includes('Conectado');
                const socketStatusEl = document.getElementById('socketStatus');
                // Check if we have a true socket connection
                const isSocketConnected = socket.connected;

                // If socket is connected, we rely on events.
                if (isSocketConnected) return;

                // If socket is NOT connected, we poll via HTTP
                
                // 1. Check Status
                try {
                    const statusData = await apiFetch('/status');
                    const s = typeof statusData === 'string' ? statusData : (statusData.status || statusData.connection || '');
                    
                    // Update status indicator to show HTTP is working
                    if (socketStatusEl) {
                        socketStatusEl.className = 'text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 flex items-center gap-1';
                        socketStatusEl.innerHTML = '<div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>Sincronizando (HTTP)';
                    }

                    if (s.toLowerCase().includes('connect') || s.toLowerCase() === 'open') {
                        if (!isConnectedUI) {
                            handleConnectionSuccess();
                        }
                        return; 
                    }
                } catch(e) {
                    if (socketStatusEl) {
                        socketStatusEl.className = 'text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 flex items-center gap-1';
                        socketStatusEl.innerHTML = '<div class="w-2 h-2 rounded-full bg-red-500"></div>Sin conexión';
                    }
                }

                // 2. Poll QR if not connected
                if (!isConnectedUI) {
                    const timestamp = new Date().getTime();
                    const src = '/admin/notificaciones/qr?format=image&t=' + timestamp;
                    
                    const tempImg = new Image();
                    tempImg.onload = function() {
                        const img = document.getElementById('qrImg');
                        if (img && !img.classList.contains('hidden')) {
                            img.src = src;
                            if (document.getElementById('qrUpdatedAt')) {
                                document.getElementById('qrUpdatedAt').innerText = 'Última actualización: ' + new Date().toLocaleString();
                            }
                        }
                    };
                    tempImg.src = src;
                }
            }, 5000);
            
            socket.on('connection-close', (data) => {
                console.log('Socket: Connection closed');
                showToast('error', 'La sesión de WhatsApp se ha cerrado/desconectado.');
                // Reset placeholder
                const placeholder = document.getElementById('qrPlaceholder');
                if (placeholder) {
                    placeholder.innerHTML = 'Esperando nuevo QR...';
                    placeholder.classList.remove('hidden');
                }
            });

            socket.on('message', (data) => {
                if (data && data.jid && data.message) {
                    // If for current chat
                    if (selectedJid && data.jid === selectedJid) {
                        Poll.getMessages(selectedJid);
                    }
                    loadChats();
                }
            });
        }
    } catch (e) {
        console.warn('Real-time socket init failed', e);
    }
});

});
</script>
@endpush

@endsection
