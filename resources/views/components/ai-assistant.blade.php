<!-- AI Assistant Floating Button & Chat Panel (RAG + Groq + Tool Calling + RBAC) -->
<div class="ai-assistant-wrapper">
    <!-- Floating Button -->
    <button
        id="ai-assistant-toggle"
        onclick="aqChat.toggle()"
        class="fixed bottom-6 right-6 z-50 w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-700 shadow-lg shadow-cyan-900/20 hover:shadow-cyan-700/25 transition-all duration-300 hover:-translate-y-0.5 flex items-center justify-center group"
        aria-label="Ouvrir l'assistant IA"
    >
        <div id="ai-icon" class="transition-transform duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
        </div>
        <div id="ai-close-icon" class="hidden transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
    </button>

    <!-- Chat Panel -->
    <div
        id="ai-chat-panel"
        class="fixed bottom-24 right-6 z-50 w-96 h-[600px] bg-[#04121b]/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-cyan-200/20 hidden flex-col overflow-hidden"
        style="animation: slideUp 0.3s ease-out;"
    >
        <!-- Header -->
        <div class="p-4 border-b border-white/10 bg-[#04121b]/80 backdrop-blur-md">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white leading-tight">AquaSecure AI</h3>
                        <div class="flex items-center gap-1">
                            <span id="ai-status-dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                            <span id="ai-status-text" class="text-xs text-emerald-400 font-medium">En ligne</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        onclick="aqChat.refresh()"
                        class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-cyan-400 hover:text-cyan-300 transition-all flex items-center justify-center"
                        title="Actualiser la conversation"
                    >
                        <i data-lucide="rotate-cw" class="w-5 h-5"></i>
                    </button>
                    <button onclick="aqChat.toggle()" class="w-8 h-8 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-white/70 hover:text-white transition-all flex items-center justify-center">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Messages Container -->
        <div id="ai-messages" class="flex-1 overflow-y-auto p-5 space-y-4 scroll-smooth">
            <!-- Welcome Message -->
            <div class="flex items-start gap-3 animate-fadeIn">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/30">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="p-4 rounded-3xl rounded-tl-none bg-white/5 border border-white/10 text-sm text-slate-300 leading-relaxed">
                        <p class="font-medium text-white mb-2">Bonjour ! Je suis votre assistant IA AquaSecure pour le module Projets et Financements.</p>
                        <p class="text-slate-400" id="ai-welcome-sub">Posez-moi une question sur les projets, le financement, vos contributions ou les procédures.</p>
                        <p id="ai-unavailable-msg" class="text-[11px] text-rose-400 mt-2 hidden">⚠️ L'assistant IA est indisponible : configurez GROK_API_KEY dans votre .env</p>
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">À l'instant</span>
                </div>
            </div>

            <!-- Role-based Suggestions -->
            <div class="flex flex-wrap gap-2 mb-4 pt-1">
                @if (session('user.role') === 'citoyen' || session('user.role_key') === 'citoyen')
                    <button onclick="aqChat.sendQuick('Quels projets puis-je financer ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Projets à financer</button>
                    <button onclick="aqChat.sendQuick('Comment financer un projet ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Comment financer</button>
                    <button onclick="aqChat.sendQuick('Quel est le montant minimum de contribution ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Montant minimum</button>
                    <button onclick="aqChat.sendQuick('Consulte mes contributions')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Mes contributions</button>
                    <button onclick="aqChat.sendQuick('Quel est le statut de ma demande ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Statut de ma demande</button>
                    <button onclick="aqChat.sendQuick('Comment fonctionne le paiement simulé ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Paiement simulé</button>
                @elseif (session('user.role') === 'manager' || session('user.role_key') === 'manager')
                    <button onclick="aqChat.sendQuick('Résume la situation des financements')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Résumé financements</button>
                    <button onclick="aqChat.sendQuick('Quelles demandes sont en attente ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Demandes en attente</button>
                    <button onclick="aqChat.sendQuick('Consulte les budgets des projets')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Budgets projets</button>
                    <button onclick="aqChat.sendQuick('Quels projets nécessitent encore des financements ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Projets à financer</button>
                    <button onclick="aqChat.sendQuick('Explique les règles de décision')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Règles de décision</button>
                @else
                    <button onclick="aqChat.sendQuick('Quels projets sont ouverts au financement ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Projets ouverts</button>
                    <button onclick="aqChat.sendQuick('Comment financer un projet ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Comment financer</button>
                    <button onclick="aqChat.sendQuick('Quelles sont les règles de financement ?')" class="px-3 py-1.5 rounded-xl bg-white/5 border border-white/10 text-xs text-cyan-300 hover:bg-white/10 hover:text-cyan-200 transition-all whitespace-nowrap">Règles de financement</button>
                @endif
            </div>

            <!-- Messages -->
            <div id="ai-message-list"></div>

            <!-- Typing / Loading -->
            <template id="ai-typing-template">
                <div class="flex items-start gap-3 typing-block">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/30">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div class="glass p-3 rounded-2xl rounded-tl-none bg-white/5 border border-white/10">
                        <div class="typing-indicator">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-white/10 bg-[#04121b]/80 backdrop-blur-md">
            <form id="ai-chat-form" onsubmit="aqChat.sendMessage(event)" class="flex items-end gap-2">
                <div class="relative flex-1">
                    <textarea
                        id="ai-input"
                        placeholder="Posez votre question sur Projets et Financements..."
                        rows="1"
                        class="w-full px-4 py-3 rounded-2xl border border-white/10 bg-white/5 text-white placeholder-slate-500 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all"
                        onkeydown="aqChat.handleKeydown(event)"
                    ></textarea>
                    <button
                        type="button"
                        onclick="aqChat.clearConversation()"
                        class="absolute top-2 right-2 w-5 h-5 rounded-full bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white transition-all flex items-center justify-center"
                        title="Nouvelle conversation"
                        aria-label="Nouvelle conversation"
                    >
                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                    </button>
                </div>
                <button
                    type="submit"
                    id="ai-send-btn"
                    class="w-10 h-10 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/30 hover:shadow-cyan-700/20 transition-all flex items-center justify-center flex-shrink-0"
                    disabled
                >
                    <span id="ai-send-icon" class="w-5 h-5 text-white"><i data-lucide="send" class="w-5 h-5"></i></span>
                    <span id="ai-send-loader" class="hidden w-5 h-5 text-white"><svg class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                </button>
            </form>
            <p class="text-[10px] text-slate-500 mt-2 text-center">Spécialisé dans le module Projets et Financements</p>
        </div>
    </div>
</div>

@push('styles')
<style>
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    #ai-input:focus {
        outline: none;
    }

    #ai-message-list::-webkit-scrollbar {
        width: 5px;
    }
    #ai-message-list::-webkit-scrollbar-track {
        background: transparent;
    }
    #ai-message-list::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 10px;
    }
    #ai-message-list::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.2);
    }

    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 10px;
    }
    .typing-indicator span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(16,185,129,0.6);
        animation: typing 1.4s infinite;
    }
    .typing-indicator span:nth-child(2) {
        animation-delay: 0.2s;
    }
    .typing-indicator span:nth-child(3) {
        animation-delay: 0.4s;
    }
    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.5;
        }
        30% {
            transform: translateY(-6px);
            opacity: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked@9/marked.min.js"></script>
<script>
    window.aqChat = {
        messages: [],   // historique [{role, content}] pour le contexte serveur
        loading: false,
        groqAvailable: null,
        role: 'inconnu',

        init: function() {
            this.loadStatus();
        },

        toggle: function() {
            const panel = document.getElementById('ai-chat-panel');
            const button = document.getElementById('ai-assistant-toggle');
            const aiIcon = document.getElementById('ai-icon');
            const closeIcon = document.getElementById('ai-close-icon');

            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                panel.classList.add('flex');
                aiIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
                button.classList.add('rotate-90');
                setTimeout(() => {
                    const messages = document.getElementById('ai-message-list');
                    messages.scrollTop = messages.scrollHeight;
                }, 100);
            } else {
                panel.classList.add('hidden');
                panel.classList.remove('flex');
                aiIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                button.classList.remove('rotate-90');
            }
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        },

        refresh: function() {
            const list = document.getElementById('ai-message-list');
            list.innerHTML = '';
            const welcome = document.createElement('div');
            welcome.className = 'flex items-start gap-3 animate-fadeIn';
            welcome.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/30">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="p-4 rounded-3xl rounded-tl-none bg-white/5 border border-white/10 text-sm text-slate-300 leading-relaxed">
                        <p class="font-medium text-white mb-2">Conversation réinitialisée. Posez-moi une nouvelle question.</p>
                        <p class="text-slate-400">Je suis spécialisé dans le module Projets et Financements.</p>
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">À l'instant</span>
                </div>
            `;
            list.appendChild(welcome);
            this.scrollToBottom();
        },

        clearConversation: function() {
            this.refresh();
        },

        loadStatus: function() {
            fetch('{{ route('chatbot.status') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                this.groqAvailable = data.groq_available;
                const dot = document.getElementById('ai-status-dot');
                const text = document.getElementById('ai-status-text');
                const msg = document.getElementById('ai-unavailable-msg');
                const btn = document.getElementById('ai-send-btn');
                const sub = document.getElementById('ai-welcome-sub');

                if (data.groq_available) {
                    dot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.6)]';
                    text.textContent = 'En ligne';
                    text.className = 'text-xs text-emerald-400 font-medium';
                    if (msg) msg.classList.add('hidden');
                    btn.disabled = false;
                    if (sub) sub.textContent = 'Posez-moi une question sur les projets, le financement, vos contributions ou les procédures.';
                } else {
                    dot.className = 'w-2 h-2 rounded-full bg-rose-500';
                    text.textContent = 'Indisponible';
                    text.className = 'text-xs text-rose-400 font-medium';
                    if (msg) msg.classList.remove('hidden');
                    btn.disabled = true;
                    if (sub) sub.textContent = 'Configurez GROK_API_KEY dans votre .env pour activer l\'assistant IA.';
                }
            })
            .catch(() => {
                this.groqAvailable = false;
                const dot = document.getElementById('ai-status-dot');
                const text = document.getElementById('ai-status-text');
                const msg = document.getElementById('ai-unavailable-msg');
                const btn = document.getElementById('ai-send-btn');
                dot.className = 'w-2 h-2 rounded-full bg-rose-500';
                text.textContent = 'Indisponible';
                text.className = 'text-xs text-rose-400 font-medium';
                if (msg) msg.classList.remove('hidden');
                btn.disabled = true;
            });
        },

        sendQuick: function(text) {
            // Ne pas envoyer si déjà en chargement
            if (this.loading) return;
            const input = document.getElementById('ai-input');
            input.value = text;
            // Appeler sendMessage directement avec un faux event
            this.sendMessage({ preventDefault: () => {} });
        },

        sendMessage: function(event) {
            event.preventDefault();
            const input = document.getElementById('ai-input');
            const text = input.value.trim();
            if (!text || this.loading || !this.groqAvailable) return;

            this.addUserMessage(text);
            input.value = '';
            input.style.height = 'auto';
            this.loading = true;
            document.getElementById('ai-send-btn').disabled = true;
            this.showTyping();

            // Garder l'historique pour le contexte (max 10 tours)
            const history = this.messages.slice(-10);

            fetch('{{ route('chatbot.ask') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: text, history: history })
            })
            .then(res => {
                if (res.status === 429) {
                    return { response: 'Trop de requêtes envoyées. Attendez quelques secondes avant de réessayer.', error: null, sources: [], tool_calls: [] };
                }
                if (res.status === 422) {
                    return { response: 'Message invalide ou trop long. Veuillez reformuler.', error: null, sources: [], tool_calls: [] };
                }
                if (!res.ok && res.status !== 200) {
                    throw new Error('HTTP ' + res.status);
                }
                return res.json();
            })
            .then(data => {
                this.hideTyping();
                if (data.response) {
                    // Sauvegarder dans l'historique (tronquer si trop long)
                    const maxHistoryContent = 3000;
                    const userContent    = text.length > maxHistoryContent ? text.substring(0, maxHistoryContent) : text;
                    const assistantContent = data.response.length > maxHistoryContent
                        ? data.response.substring(0, maxHistoryContent) + '...'
                        : data.response;
                    this.messages.push({ role: 'user', content: userContent });
                    this.messages.push({ role: 'assistant', content: assistantContent });
                    // Garder max 10 tours (20 messages)
                    if (this.messages.length > 20) {
                        this.messages = this.messages.slice(-20);
                    }
                    this.addAIMessage(data.response, data.sources || [], data.error || null);
                } else if (data.error) {
                    this.addAIMessage(data.message || 'Une erreur est survenue.', [], data.error);
                } else {
                    this.addAIMessage('Aucune réponse reçue. Veuillez réessayer.', [], null);
                }
            })
            .catch((err) => {
                this.hideTyping();
                this.addAIMessage('Impossible de joindre le serveur. Vérifiez votre connexion.', [], 'network');
            })
            .finally(() => {
                // Toujours réinitialiser le loading
                this.loading = false;
                document.getElementById('ai-send-btn').disabled = !this.groqAvailable;
                // Re-focus sur l'input pour faciliter la saisie suivante
                document.getElementById('ai-input').focus();
            });
        },

        handleKeydown: function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                aqChat.sendMessage({ preventDefault: () => {} });
            }
            const target = event.target;
            target.style.height = 'auto';
            target.style.height = (target.scrollHeight) + 'px';
        },

        addUserMessage: function(text) {
            const list = document.getElementById('ai-message-list');
            const div = document.createElement('div');
            div.className = 'flex items-start gap-3 animate-fadeIn justify-end';
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            div.innerHTML = `
                <div class="flex-1 flex flex-col items-end">
                    <div class="p-3 rounded-3xl rounded-tr-none bg-cyan-500/10 border border-cyan-400/30">
                        <p class="text-sm text-slate-200 leading-relaxed">${this.escapeHtml(text)}</p>
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1">${time}</span>
                </div>
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/40">
                    <i data-lucide="user" class="w-4 h-4 text-white"></i>
                </div>
            `;
            list.appendChild(div);
            this.scrollToBottom();
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        },

        addAIMessage: function(text, sources, error) {
            const list = document.getElementById('ai-message-list');
            const div = document.createElement('div');
            div.className = 'flex items-start gap-3 animate-fadeIn';
            const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            const formatted = this.formatText(text);

            let sourcesHtml = '';
            if (sources && sources.length > 0) {
                sourcesHtml = `
                    <div class="mt-3 border-t border-white/10 pt-2">
                        <p class="text-[10px] font-semibold text-slate-400 mb-2 uppercase tracking-wider">Sources documentaires</p>
                        <div class="flex flex-wrap gap-1">
                            ${sources.map(src => `
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white/5 border border-white/10 text-[10px] text-cyan-300">
                                    <i data-lucide="file-text" class="w-2.5 h-2.5 text-cyan-400 flex-shrink-0"></i>
                                    ${this.escapeHtml(src.document_title)}
                                    ${src.section ? `<span class="text-cyan-500"> • ${this.escapeHtml(src.section)}</span>` : ''}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            let errorHtml = '';
            if (error && (error === 'groq_non_configure' || error === 'groq_erreur')) {
                errorHtml = `<p class="text-[10px] text-rose-400 mt-2">⚠️ L'assistant IA est indisponible. Vérifiez GROK_API_KEY dans votre .env.</p>`;
            } else if (error === 'network') {
                errorHtml = `<p class="text-[10px] text-rose-400 mt-2">⚠️ Erreur de connexion au serveur.</p>`;
            }

            div.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-cyan-500/40">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="p-3.5 rounded-3xl rounded-tl-none bg-white/5 border border-white/10 text-sm text-slate-200 leading-relaxed overflow-hidden ${error ? 'bg-rose-500/10 border-rose-500/30' : ''}">
                        <p class="font-medium text-white mb-2">AquaSecure AI</p>
                        <div class="leading-relaxed prose-chatbot">${formatted}</div>
                        ${sourcesHtml}
                        ${errorHtml}
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">${time}</span>
                </div>
            `;
            list.appendChild(div);
            this.scrollToBottom();
            if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        },

        showTyping: function() {
            const list = document.getElementById('ai-message-list');
            const template = document.getElementById('ai-typing-template');
            const clone = template.content.cloneNode(true);
            clone.querySelector('.flex.items-start.gap-3').id = 'typing-indicator';
            list.appendChild(clone);
            this.scrollToBottom();
        },

        hideTyping: function() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) indicator.remove();
        },

        formatText: function(text) {
            if (!text) return '';

            // Utiliser marked.js si disponible (rendu Markdown complet et fiable)
            if (typeof marked !== 'undefined') {
                marked.setOptions({
                    breaks: true,       // \n → <br>
                    gfm: true,          // GitHub Flavored Markdown (tableaux, etc.)
                    pedantic: false,
                });

                // Renderer personnalisé pour le style dark AquaSecure
                const renderer = new marked.Renderer();

                // Tableaux
                renderer.table = (header, body) =>
                    `<div class="overflow-x-auto my-3"><table class="w-full text-xs border-collapse">`
                    + `<thead class="bg-cyan-500/20">${header}</thead>`
                    + `<tbody>${body}</tbody>`
                    + `</table></div>`;

                renderer.tablerow = (content) => `<tr class="border-b border-white/5">${content}</tr>`;
                renderer.tablecell = (content, flags) => flags.header
                    ? `<th class="px-2 py-1.5 text-left font-semibold text-cyan-300 border border-white/10 whitespace-nowrap">${content}</th>`
                    : `<td class="px-2 py-1.5 text-slate-200 border border-white/10">${content}</td>`;

                // Titres
                renderer.heading = (text, level) => {
                    const sizes = { 1: 'text-base', 2: 'text-sm', 3: 'text-sm' };
                    return `<p class="${sizes[level] || 'text-sm'} font-bold text-white mt-3 mb-1">${text}</p>`;
                };

                // Listes — numérotées et à puces
                renderer.list = (body, ordered) => ordered
                    ? `<ol class="my-2 space-y-1 text-slate-200 pl-1">${body}</ol>`
                    : `<ul class="my-2 space-y-1 text-slate-200 pl-1">${body}</ul>`;

                renderer.listitem = (text, ordered) => ordered === undefined
                    ? `<li class="flex gap-1.5 items-start py-0.5"><span class="text-cyan-400 shrink-0 mt-0.5">•</span><span>${text}</span></li>`
                    : `<li class="flex gap-2 items-start py-0.5"><span class="text-cyan-400 font-semibold shrink-0 min-w-[20px]"></span><span>${text}</span></li>`;

                // Listes numérotées avec vrai numéro
                let listCounter = 0;
                renderer.list = (body, ordered) => {
                    if (ordered) listCounter = 0;
                    return ordered
                        ? `<ol class="my-2 space-y-1 text-slate-200 pl-1">${body}</ol>`
                        : `<ul class="my-2 space-y-1 text-slate-200 pl-1">${body}</ul>`;
                };
                renderer.listitem = (text) => {
                    listCounter++;
                    return `<li class="flex gap-2 items-start py-0.5"><span class="text-cyan-400 font-semibold shrink-0">${listCounter}.</span><span>${text}</span></li>`;
                };

                // Paragraphes
                renderer.paragraph = (text) => `<p class="mb-1 leading-relaxed">${text}</p>`;

                // Gras / italique
                renderer.strong = (text) => `<strong class="font-semibold text-white">${text}</strong>`;
                renderer.em = (text) => `<em class="text-cyan-200">${text}</em>`;

                // Code inline
                renderer.codespan = (code) =>
                    `<code class="px-1 py-0.5 rounded bg-white/10 text-cyan-300 font-mono text-[11px]">${code}</code>`;

                // Séparateur
                renderer.hr = () => `<hr class="border-white/10 my-3">`;

                try {
                    return marked.parse(text, { renderer });
                } catch(e) {
                    console.warn('marked error:', e);
                }
            }

            // Fallback basique si marked non chargé
            return text
                .replace(/\*\*(.+?)\*\*/g, '<strong class="text-white">$1</strong>')
                .replace(/\n/g, '<br>');
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },

        scrollToBottom: function() {
            const list = document.getElementById('ai-message-list');
            setTimeout(() => { list.scrollTop = list.scrollHeight; }, 100);
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        window.aqChat.init();
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }
    });
</script>
@endpush