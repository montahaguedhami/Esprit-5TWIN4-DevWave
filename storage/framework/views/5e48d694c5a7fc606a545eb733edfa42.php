<!-- AI Assistant Floating Button & Chat Panel -->
<div class="ai-assistant-wrapper">
    <!-- Floating Button -->
    <button 
        id="ai-assistant-toggle" 
        onclick="toggleAIAssistant()"
        class="fixed bottom-6 right-6 z-50 w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 shadow-2xl hover:shadow-purple-500/50 transition-all duration-300 hover:scale-110 flex items-center justify-center group"
        aria-label="Ouvrir l'assistant IA"
    >
        <!-- AI Icon -->
        <div id="ai-icon" class="transition-transform duration-300">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
        </div>
        <!-- Close Icon (hidden by default) -->
        <div id="ai-close-icon" class="hidden transition-transform duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <!-- Pulse Animation -->
        <span class="absolute -inset-1 rounded-full bg-purple-500/30 animate-ping"></span>
    </button>

    <!-- Chat Panel -->
    <div 
        id="ai-chat-panel" 
        class="fixed bottom-24 right-6 z-50 w-96 h-[600px] glass-strong rounded-2xl shadow-2xl border border-purple-500/20 hidden flex-col overflow-hidden"
        style="animation: slideUp 0.3s ease-out;"
    >
        <!-- Header -->
        <div class="p-4 border-b border-white/10 bg-gradient-to-r from-purple-500/20 to-pink-600/20">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">AquaSecure AI</h3>
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-xs text-emerald-400">En ligne</span>
                        </div>
                    </div>
                </div>
                <button 
                    onclick="toggleAIAssistant()" 
                    class="p-2 rounded-lg hover:bg-white/10 transition-colors text-slate-400 hover:text-white"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Messages Container -->
        <div id="ai-messages" class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Welcome Message -->
            <div class="flex items-start gap-3 animate-fadeIn">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="glass p-3 rounded-2xl rounded-tl-none">
                        <p class="text-sm text-white mb-2">👋 Bonjour ! Je suis votre assistant IA AquaSecure.</p>
                        <p class="text-sm text-slate-300">Comment puis-je vous aider aujourd'hui ?</p>
                    </div>
                    <span class="text-xs text-slate-500 mt-1 block">À l'instant</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-2">
                <button 
                    onclick="sendQuickMessage('Quelle est la qualité de l\'eau dans ma zone ?')"
                    class="text-xs px-3 py-2 rounded-lg glass hover:glass-strong transition-all text-cyan-400 hover:text-cyan-300"
                >
                    💧 Qualité de l'eau
                </button>
                <button 
                    onclick="sendQuickMessage('Comment signaler un problème ?')"
                    class="text-xs px-3 py-2 rounded-lg glass hover:glass-strong transition-all text-cyan-400 hover:text-cyan-300"
                >
                    🔧 Signaler un problème
                </button>
                <button 
                    onclick="sendQuickMessage('Consulter ma facture')"
                    class="text-xs px-3 py-2 rounded-lg glass hover:glass-strong transition-all text-cyan-400 hover:text-cyan-300"
                >
                    📄 Ma facture
                </button>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-white/10 bg-slate-900/50">
            <form id="ai-chat-form" onsubmit="sendAIMessage(event)" class="flex items-end gap-2">
                <div class="flex-1">
                    <textarea 
                        id="ai-input"
                        placeholder="Posez votre question..."
                        rows="1"
                        class="w-full px-4 py-3 rounded-xl glass text-white placeholder-slate-500 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-purple-500/50"
                        onkeydown="handleAIInputKeydown(event)"
                    ></textarea>
                </div>
                <button 
                    type="submit"
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 transition-all flex items-center justify-center flex-shrink-0"
                >
                    <i data-lucide="send" class="w-5 h-5 text-white"></i>
                </button>
            </form>
            <p class="text-xs text-slate-500 mt-2 text-center">
                IA peut faire des erreurs. Vérifiez les informations importantes.
            </p>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
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

    /* Custom scrollbar for messages */
    #ai-messages::-webkit-scrollbar {
        width: 6px;
    }

    #ai-messages::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    #ai-messages::-webkit-scrollbar-thumb {
        background: rgba(139, 92, 246, 0.3);
        border-radius: 10px;
    }

    #ai-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(139, 92, 246, 0.5);
    }

    /* Typing indicator */
    .typing-indicator {
        display: flex;
        gap: 4px;
        padding: 12px;
    }

    .typing-indicator span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(139, 92, 246, 0.6);
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
            opacity: 0.6;
        }
        30% {
            transform: translateY(-10px);
            opacity: 1;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // AI Assistant Data
    const aiResponses = {
        'qualité': {
            response: "D'après les dernières analyses, la qualité de l'eau dans votre zone (Tunis Nord) est **excellente** avec un score de 98/100. 💧\n\n✅ Taux de chlore: Normal\n✅ pH: 7.2 (optimal)\n✅ Turbidité: Faible\n\nDernière analyse: Il y a 2 heures",
            delay: 1500
        },
        'problème': {
            response: "Pour signaler un problème, c'est très simple ! 🔧\n\n1. Cliquez sur **Nouvelle Déclaration** dans votre dashboard\n2. Décrivez le problème (fuite, pression, qualité)\n3. Ajoutez une photo si possible\n4. Validez votre signalement\n\nUn technicien sera notifié immédiatement et vous recevrez un numéro de suivi.",
            delay: 2000
        },
        'facture': {
            response: "Voici vos informations de facturation 📄\n\n**Dernière facture:** Septembre 2026\n💰 Montant: 87.50 TND\n📊 Consommation: 18.5 m³\n📅 Échéance: 05/10/2026\n\nStatut: ⏳ En attente de paiement\n\n[Voir la facture détaillée](#)",
            delay: 1800
        },
        'maintenance': {
            response: "Les prochaines maintenances programmées dans votre zone 🛠️\n\n📅 **28 septembre 2026**\n⏰ 9h00 - 12h00\n📍 Zone: Tunis Nord\n\nImpact: Interruption possible du service\n\nVous recevrez une notification 24h avant l'intervention.",
            delay: 1600
        },
        'aide': {
            response: "Je peux vous aider avec:\n\n💧 Qualité de l'eau et analyses\n🔧 Signalement de problèmes\n📄 Factures et consommation\n🛠️ Interventions et maintenance\n📊 Statistiques et rapports\n⚙️ Utilisation de la plateforme\n\nPosez-moi votre question ou choisissez un sujet ci-dessus!",
            delay: 1400
        },
        'default': {
            response: "Je comprends votre question. Laissez-moi vous orienter vers les bonnes ressources.\n\nVous pouvez:\n• Consulter la **documentation** dans le centre d'aide\n• Contacter le **support technique** au +216 71 123 456\n• Explorer votre **tableau de bord** pour plus d'informations\n\nSouhaitez-vous que je vous aide avec autre chose ?",
            delay: 1700
        }
    };

    function toggleAIAssistant() {
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
            
            // Auto-scroll to bottom
            setTimeout(() => {
                const messages = document.getElementById('ai-messages');
                messages.scrollTop = messages.scrollHeight;
            }, 100);
        } else {
            panel.classList.add('hidden');
            panel.classList.remove('flex');
            aiIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
            button.classList.remove('rotate-90');
        }
        
        // Reinitialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function sendAIMessage(event) {
        event.preventDefault();
        const input = document.getElementById('ai-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        // Add user message
        addUserMessage(message);
        input.value = '';
        
        // Show typing indicator
        showTypingIndicator();
        
        // Get AI response
        const responseData = getAIResponse(message);
        
        // Simulate AI thinking
        setTimeout(() => {
            hideTypingIndicator();
            addAIMessage(responseData.response);
        }, responseData.delay);
    }

    function sendQuickMessage(message) {
        document.getElementById('ai-input').value = message;
        document.getElementById('ai-chat-form').dispatchEvent(new Event('submit'));
    }

    function addUserMessage(message) {
        const messagesContainer = document.getElementById('ai-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3 justify-end animate-fadeIn';
        messageDiv.innerHTML = `
            <div class="flex-1 flex flex-col items-end">
                <div class="glass-strong p-3 rounded-2xl rounded-tr-none bg-gradient-to-br from-cyan-500/20 to-blue-600/20">
                    <p class="text-sm text-white">${escapeHtml(message)}</p>
                </div>
                <span class="text-xs text-slate-500 mt-1">À l'instant</span>
            </div>
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="user" class="w-4 h-4 text-white"></i>
            </div>
        `;
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
        
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function addAIMessage(message) {
        const messagesContainer = document.getElementById('ai-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-3 animate-fadeIn';
        
        // Convert markdown-style bold to HTML
        const formattedMessage = message
            .replace(/\*\*(.*?)\*\*/g, '<strong class="text-purple-400">$1</strong>')
            .replace(/\n/g, '<br>');
        
        messageDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <div class="glass p-3 rounded-2xl rounded-tl-none">
                    <p class="text-sm text-white">${formattedMessage}</p>
                </div>
                <span class="text-xs text-slate-500 mt-1 block">À l'instant</span>
            </div>
        `;
        messagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }

    function showTypingIndicator() {
        const messagesContainer = document.getElementById('ai-messages');
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'flex items-start gap-3';
        typingDiv.innerHTML = `
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <div class="glass p-3 rounded-2xl rounded-tl-none">
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }

    function hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    function getAIResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        if (lowerMessage.includes('qualité') || lowerMessage.includes('eau') || lowerMessage.includes('analyse')) {
            return aiResponses.qualité;
        } else if (lowerMessage.includes('problème') || lowerMessage.includes('signaler') || lowerMessage.includes('fuite') || lowerMessage.includes('panne')) {
            return aiResponses.problème;
        } else if (lowerMessage.includes('facture') || lowerMessage.includes('payer') || lowerMessage.includes('consommation')) {
            return aiResponses.facture;
        } else if (lowerMessage.includes('maintenance') || lowerMessage.includes('intervention') || lowerMessage.includes('travaux')) {
            return aiResponses.maintenance;
        } else if (lowerMessage.includes('aide') || lowerMessage.includes('help') || lowerMessage.includes('comment')) {
            return aiResponses.aide;
        } else {
            return aiResponses.default;
        }
    }

    function scrollToBottom() {
        const messages = document.getElementById('ai-messages');
        setTimeout(() => {
            messages.scrollTop = messages.scrollHeight;
        }, 100);
    }

    function handleAIInputKeydown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            document.getElementById('ai-chat-form').dispatchEvent(new Event('submit'));
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\ghada\Desktop\5ème\projet_Laravel\aquasecure\resources\views/components/ai-assistant.blade.php ENDPATH**/ ?>