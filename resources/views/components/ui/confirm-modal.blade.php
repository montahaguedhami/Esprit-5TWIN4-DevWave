{{-- resources/views/components/ui/confirm-modal.blade.php --}}
{{-- Modal de confirmation universel AquaSecure (Suppression & Modification) --}}
{{-- Version améliorée : impact visuel, badges de risque, actions secondaires, animations --}}
<div
    id="aqua-confirm-modal"
    class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-xl hidden transition-all duration-300"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
>
    {{-- Overlay avec dégradé subtil --}}
    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/90 to-slate-900/80 backdrop-blur-sm"></div>

    <div
        id="aqua-confirm-dialog"
        class="aqua-confirm-card relative w-full max-w-md rounded-3xl border border-cyan-400/25 bg-slate-900 p-7 shadow-2xl ring-1 ring-white/10 overflow-hidden transform transition-all duration-300 scale-90 opacity-0"
        onclick="event.stopPropagation()"
    >
        {{-- Lueur d'ambiance dynamique selon le type --}}
        <div id="aqua-confirm-glow" class="absolute -top-32 -left-32 h-64 w-64 rounded-full blur-[100px] pointer-events-none transition-colors duration-500"></div>

        <div class="relative z-10">
            {{-- En-tête avec Icône --}}
            <div class="flex items-start gap-5">
                <div id="aqua-confirm-icon-box" class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-[2rem] border transition-all duration-300">
                    {{-- Icône Danger (Poubelle) --}}
                    <svg id="aqua-icon-danger" class="h-7 w-7 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{-- Icône Warning / Modification --}}
                    <svg id="aqua-icon-warning" class="h-7 w-7 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{-- Icône Info / Modification Cyan --}}
                    <svg id="aqua-icon-info" class="h-7 w-7 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{-- Icône Success (Archivage) --}}
                    <svg id="aqua-icon-success" class="h-7 w-7 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="flex-1 space-y-2">
                    <h3 id="aqua-confirm-title" class="text-lg font-bold text-white tracking-tight leading-tight">
                        Confirmation requise
                    </h3>
                    <p id="aqua-confirm-message" class="text-sm text-cyan-100/80 leading-relaxed">
                        Êtes-vous certain de vouloir effectuer cette action ?
                    </p>
                </div>
            </div>

            {{-- Badge optionnel (Nom de l'élément ciblé) --}}
            <div id="aqua-confirm-badge-container" class="mt-4 hidden">
                <span id="aqua-confirm-badge" class="inline-flex items-center gap-2 rounded-xl border border-cyan-400/20 bg-slate-950/60 px-3 py-2 text-xs font-semibold text-cyan-200 shadow-sm">
                    <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="aqua-confirm-badge-text"></span>
                </span>
            </div>

            {{-- Panel d'impact visuel (risque, conséquences) --}}
            <div id="aqua-confirm-impact-panel" class="mt-4 hidden rounded-xl border p-3 text-xs leading-relaxed"></div>

            {{-- Zone d'alerte contextuelle (ex: avertissement Soft Delete ou blocage) --}}
            <div id="aqua-confirm-hint" class="mt-3.5 hidden rounded-xl border p-3 text-xs leading-relaxed"></div>

            {{-- Boutons d'action --}}
            <div class="mt-7 flex items-center justify-end gap-3 pt-5 border-t border-white/10">
                <button
                    type="button"
                    id="aqua-confirm-btn-cancel"
                    class="aqua-confirm-btn-cancel rounded-xl border border-white/10 bg-slate-800/90 px-5 py-3 text-sm font-medium text-cyan-100/80 hover:bg-white/10 hover:text-white transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-400/30"
                >
                    Annuler
                </button>
                {{-- Bouton secondaire optionnel (ex: archiver sans supprimer) --}}
                <button
                    type="button"
                    id="aqua-confirm-btn-secondary"
                    class="hidden rounded-xl border border-white/10 bg-slate-800/90 px-5 py-3 text-sm font-medium text-cyan-300 hover:bg-white/10 hover:text-white transition-all duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-400/30"
                ></button>
                <button
                    type="button"
                    id="aqua-confirm-btn-confirm"
                    class="aqua-confirm-btn-confirm rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-200 focus:outline-none focus:ring-2"
                >
                    Confirmer
                </button>
            </div>

            {{-- Ligne de pied de page informative --}}
            <div id="aqua-confirm-footer" class="mt-5 flex items-center justify-between text-[10px] text-slate-500/60 hidden">
                <span id="aqua-confirm-footer-left"></span>
                <span id="aqua-confirm-footer-right"></span>
            </div>
        </div>
    </div>
</div>

<style>
    /* Forcer le rendu haute fidélité du modal dans tous les thèmes */
    #aqua-confirm-modal .aqua-confirm-card {
        background-color: #0c1524 !important;
        color: #e2e8f0 !important;
        border-color: rgba(6, 182, 212, 0.25) !important;
        box-shadow: 0 25px 50px -12px rgba(2, 6, 23, 0.7) !important;
    }
    #aqua-confirm-modal .aqua-confirm-card [class*="text-white"],
    #aqua-confirm-modal .aqua-confirm-card h3 {
        color: #ffffff !important;
    }
    #aqua-confirm-modal .aqua-confirm-card [class*="text-cyan-100"] {
        color: #cbd5e1 !important;
    }

    /* === Design DANGER (suppression) – rouge/rose soutenu === */
    #aqua-confirm-modal.variant-danger .aqua-confirm-card {
        border-color: rgba(239, 68, 68, 0.35) !important;
    }
    #aqua-confirm-modal.variant-danger #aqua-confirm-glow {
        background: radial-gradient(600px at 20% 20%, rgba(239, 68, 68, 0.35), transparent 40%) !important;
    }
    #aqua-confirm-modal.variant-danger #aqua-confirm-title {
        color: #fca5a5 !important;
    }
    #aqua-confirm-modal.variant-danger #aqua-confirm-message {
        color: #e2e8f0 !important;
    }
    #aqua-confirm-modal.variant-danger .aqua-confirm-btn-confirm {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
        border: none !important;
        box-shadow: 0 0 20px rgba(220, 38, 38, 0.45) !important;
    }
    #aqua-confirm-modal.variant-danger .aqua-confirm-btn-confirm:hover {
        background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%) !important;
        box-shadow: 0 0 30px rgba(220, 38, 38, 0.6) !important;
        transform: translateY(-1px);
    }
    #aqua-confirm-modal.variant-danger .aqua-confirm-btn-cancel {
        background: rgba(15, 23, 42, 0.6) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    #aqua-confirm-modal.variant-danger .aqua-confirm-btn-cancel:hover {
        background: rgba(255, 255, 255, 0.08) !important;
        color: #ffffff !important;
    }
    #aqua-confirm-modal.variant-danger #aqua-confirm-hint {
        background: rgba(239, 68, 68, 0.1) !important;
        border-color: rgba(239, 68, 68, 0.3) !important;
        color: #fecaca !important;
    }
    #aqua-confirm-modal.variant-danger #aqua-confirm-impact-panel {
        background: rgba(15, 23, 42, 0.4) !important;
        border: 1px dashed rgba(239, 68, 68, 0.25) !important;
        color: #e2e8f0 !important;
    }
    #aqua-confirm-modal.variant-danger #aqua-confirm-badge {
        background: rgba(239, 68, 68, 0.15) !important;
        border-color: rgba(239, 68, 68, 0.35) !important;
        color: #fca5a5 !important;
    }

    /* Animation de shake pour le bouton confirmer (actions dangereuses) */
    @keyframes aqua-shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-3px); }
        20%, 40%, 60%, 80% { transform: translateX(3px); }
    }
    .aqua-shake {
        animation: aqua-shake 0.4s ease-in-out;
    }
    /* Pulse sur l'icône pour attirer l'attention */
    @keyframes aqua-pulse-icon {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
        70% { box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    .aqua-pulse-icon {
        animation: aqua-pulse-icon 2s infinite;
    }
</style>

<script>
    (function () {
        let currentCallback = null;
        let currentCancelCallback = null;
        let currentSecondaryCallback = null;

        const modal = document.getElementById('aqua-confirm-modal');
        const dialog = document.getElementById('aqua-confirm-dialog');
        const titleEl = document.getElementById('aqua-confirm-title');
        const messageEl = document.getElementById('aqua-confirm-message');
        const badgeContainer = document.getElementById('aqua-confirm-badge-container');
        const badgeTextEl = document.getElementById('aqua-confirm-badge-text');
        const hintEl = document.getElementById('aqua-confirm-hint');
        const impactPanelEl = document.getElementById('aqua-confirm-impact-panel');
        const iconBox = document.getElementById('aqua-confirm-icon-box');
        const glowEl = document.getElementById('aqua-confirm-glow');
        const iconDanger = document.getElementById('aqua-icon-danger');
        const iconWarning = document.getElementById('aqua-icon-warning');
        const iconInfo = document.getElementById('aqua-icon-info');
        const iconSuccess = document.getElementById('aqua-icon-success');
        const btnCancel = document.getElementById('aqua-confirm-btn-cancel');
        const btnConfirm = document.getElementById('aqua-confirm-btn-confirm');
        const btnSecondary = document.getElementById('aqua-confirm-btn-secondary');
        const footerEl = document.getElementById('aqua-confirm-footer');
        const footerLeft = document.getElementById('aqua-confirm-footer-left');
        const footerRight = document.getElementById('aqua-confirm-footer-right');

        window.openAquaConfirm = function (options) {
            options = options || {};
            const variant = options.variant || 'danger'; // 'danger', 'warning', 'info', 'success'

            titleEl.textContent = options.title || 'Confirmation requise';
            messageEl.textContent = options.message || 'Êtes-vous certain de vouloir effectuer cette opération ?';

            // Badge cible
            if (options.badge) {
                badgeTextEl.textContent = options.badge;
                badgeContainer.classList.remove('hidden');
            } else {
                badgeContainer.classList.add('hidden');
            }

            // Panel d'impact visuel
            if (options.impact) {
                impactPanelEl.innerHTML = options.impact;
                impactPanelEl.classList.remove('hidden');
            } else {
                impactPanelEl.classList.add('hidden');
            }

            // Avertissement contextuel
            if (options.hint) {
                hintEl.textContent = options.hint;
                hintEl.classList.remove('hidden');
            } else {
                hintEl.classList.add('hidden');
            }

            // Boutons
            btnConfirm.textContent = options.confirmText || (variant === 'danger' ? 'Supprimer' : 'Confirmer');
            btnCancel.textContent = options.cancelText || 'Annuler';

            // Bouton secondaire optionnel
            if (options.secondaryText && typeof options.onSecondary === 'function') {
                btnSecondary.textContent = options.secondaryText;
                btnSecondary.classList.remove('hidden');
                currentSecondaryCallback = options.onSecondary;
            } else {
                btnSecondary.classList.add('hidden');
                currentSecondaryCallback = null;
            }

            // Pied de page optionnel
            if (options.footerLeft || options.footerRight) {
                if (options.footerLeft) { footerLeft.textContent = options.footerLeft; }
                if (options.footerRight) { footerRight.textContent = options.footerRight; }
                footerEl.classList.remove('hidden');
            } else {
                footerEl.classList.add('hidden');
            }

            // Réinitialiser les icônes et classes
            [iconDanger, iconWarning, iconInfo, iconSuccess].forEach(ic => ic.classList.add('hidden'));
            iconBox.className = 'flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-[2rem] border transition-all duration-300';
            glowEl.className = 'absolute -top-32 -left-32 h-64 w-64 rounded-full blur-[100px] pointer-events-none transition-colors duration-500';
            btnConfirm.className = 'rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-200 focus:outline-none focus:ring-2';
            btnConfirm.classList.remove('aqua-shake');
            iconBox.classList.remove('aqua-pulse-icon');

            // Style selon la variante
            if (variant === 'danger') {
                iconDanger.classList.remove('hidden');
                iconBox.className = 'flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-[2rem] border border-rose-500/30 bg-rose-500/10 text-rose-400 ring-4 ring-rose-500/10 aqua-pulse-icon';
                glowEl.className = 'absolute -top-32 -left-32 h-64 w-64 rounded-full blur-[100px] pointer-events-none bg-rose-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs text-rose-200 leading-relaxed';
                btnConfirm.className += ' bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 shadow-lg shadow-rose-950/40 focus:ring-rose-400/40 aqua-shake';
            } else if (variant === 'warning') {
                iconWarning.classList.remove('hidden');
                iconBox.className = 'flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-[2rem] border border-amber-500/30 bg-amber-500/10 text-amber-400 ring-4 ring-amber-500/10';
                glowEl.className = 'absolute -top-32 -left-32 h-64 w-64 rounded-full blur-[100px] pointer-events-none bg-amber-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-200 leading-relaxed';
                btnConfirm.className += ' bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 shadow-lg shadow-amber-950/40 focus:ring-amber-400/40';
            } else if (variant === 'success') {
                iconSuccess.classList.remove('hidden');
                iconBox.className = 'flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-[2rem] border border-emerald-500/30 bg-emerald-500/10 text-emerald-400 ring-4 ring-emerald-500/10';
                glowEl.className = 'absolute -top-32 -left-32 h-64 w-64 rounded-full blur-[100px] pointer-events-none bg-emerald-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-xs text-emerald-200 leading-relaxed';
                btnConfirm.className += ' bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 shadow-lg shadow-emerald-950/40 focus:ring-emerald-400/40';
            } else {
                iconInfo.classList.remove('hidden');
                iconBox.className = 'flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-[2rem] border border-cyan-400/30 bg-cyan-500/10 text-cyan-300 ring-4 ring-cyan-500/10';
                glowEl.className = 'absolute -top-32 -left-32 h-64 w-64 rounded-full blur-[100px] pointer-events-none bg-cyan-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-cyan-400/30 bg-cyan-500/10 p-3 text-xs text-cyan-200 leading-relaxed';
                btnConfirm.className += ' bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 shadow-lg shadow-cyan-950/40 focus:ring-cyan-400/40';
            }

            currentCallback = typeof options.onConfirm === 'function' ? options.onConfirm : null;
            currentCancelCallback = typeof options.onCancel === 'function' ? options.onCancel : null;

            // Classe variant sur le modal pour le ciblage CSS (danger, warning, etc.)
            modal.classList.remove('variant-danger', 'variant-warning', 'variant-info', 'variant-success');
            if (variant === 'danger') modal.classList.add('variant-danger');
            if (variant === 'warning') modal.classList.add('variant-warning');
            if (variant === 'info') modal.classList.add('variant-info');
            if (variant === 'success') modal.classList.add('variant-success');

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                dialog.classList.remove('scale-90', 'opacity-0');
                dialog.classList.add('scale-100', 'opacity-100');
                btnCancel.focus();
            });
        };

        window.closeAquaConfirm = function () {
            dialog.classList.remove('scale-100', 'opacity-100');
            dialog.classList.add('scale-90', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                currentCallback = null;
                currentCancelCallback = null;
                currentSecondaryCallback = null;
            }, 300);
        };

        btnCancel.addEventListener('click', function () {
            if (currentCancelCallback) currentCancelCallback();
            window.closeAquaConfirm();
        });

        btnConfirm.addEventListener('click', function () {
            const cb = currentCallback;
            window.closeAquaConfirm();
            if (cb) cb();
        });

        btnSecondary.addEventListener('click', function () {
            const cb = currentSecondaryCallback;
            window.closeAquaConfirm();
            if (cb) cb();
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                if (currentCancelCallback) currentCancelCallback();
                window.closeAquaConfirm();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                if (currentCancelCallback) currentCancelCallback();
                window.closeAquaConfirm();
            }
        });
    })();
</script>