{{-- resources/views/components/ui/confirm-modal.blade.php --}}
{{-- Modal de confirmation universel AquaSecure (Suppression & Modification) --}}
<div
    id="aqua-confirm-modal"
    class="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md hidden transition-all duration-200"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
>
    <div
        id="aqua-confirm-dialog"
        class="aqua-confirm-card relative w-full max-w-md rounded-2xl border border-cyan-400/25 bg-slate-900 p-6 shadow-2xl ring-1 ring-white/10 overflow-hidden transform transition-all duration-200 scale-95 opacity-0"
        onclick="event.stopPropagation()"
    >
        {{-- Lueur d'ambiance dynamique selon le type --}}
        <div id="aqua-confirm-glow" class="absolute -top-24 -left-24 h-48 w-48 rounded-full blur-3xl pointer-events-none transition-colors duration-300"></div>

        <div class="relative z-10">
            {{-- En-tête avec Icône --}}
            <div class="flex items-start gap-4">
                <div id="aqua-confirm-icon-box" class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border transition-colors duration-200">
                    {{-- Icône Danger (Poubelle) --}}
                    <svg id="aqua-icon-danger" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{-- Icône Warning / Modification --}}
                    <svg id="aqua-icon-warning" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    {{-- Icône Info / Modification Cyan --}}
                    <svg id="aqua-icon-info" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>

                <div class="flex-1 space-y-1">
                    <h3 id="aqua-confirm-title" class="text-lg font-bold text-white tracking-tight">
                        Confirmation requise
                    </h3>
                    <p id="aqua-confirm-message" class="text-xs text-cyan-100/70 leading-relaxed">
                        Êtes-vous certain de vouloir effectuer cette action ?
                    </p>
                </div>
            </div>

            {{-- Badge optionnel (Nom de l'élément ciblé) --}}
            <div id="aqua-confirm-badge-container" class="mt-3 hidden">
                <span id="aqua-confirm-badge" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-400/20 bg-slate-950/60 px-3 py-1.5 text-xs font-semibold text-cyan-200">
                </span>
            </div>

            {{-- Zone d'alerte contextuelle (ex: avertissement Soft Delete ou blocage) --}}
            <div id="aqua-confirm-hint" class="mt-3.5 hidden rounded-xl border p-3 text-xs leading-relaxed"></div>

            {{-- Boutons d'action --}}
            <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                <button
                    type="button"
                    id="aqua-confirm-btn-cancel"
                    class="rounded-xl border border-white/10 bg-slate-800/90 px-4 py-2.5 text-xs font-medium text-cyan-100/80 hover:bg-white/10 hover:text-white transition shadow-sm focus:outline-none focus:ring-2 focus:ring-cyan-400/30"
                >
                    Annuler
                </button>
                <button
                    type="button"
                    id="aqua-confirm-btn-confirm"
                    class="rounded-xl px-4 py-2.5 text-xs font-semibold text-white shadow-lg transition focus:outline-none focus:ring-2"
                >
                    Confirmer
                </button>
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
</style>

<script>
    (function () {
        let currentCallback = null;
        let currentCancelCallback = null;

        const modal = document.getElementById('aqua-confirm-modal');
        const dialog = document.getElementById('aqua-confirm-dialog');
        const titleEl = document.getElementById('aqua-confirm-title');
        const messageEl = document.getElementById('aqua-confirm-message');
        const badgeContainer = document.getElementById('aqua-confirm-badge-container');
        const badgeEl = document.getElementById('aqua-confirm-badge');
        const hintEl = document.getElementById('aqua-confirm-hint');
        const iconBox = document.getElementById('aqua-confirm-icon-box');
        const glowEl = document.getElementById('aqua-confirm-glow');
        const iconDanger = document.getElementById('aqua-icon-danger');
        const iconWarning = document.getElementById('aqua-icon-warning');
        const iconInfo = document.getElementById('aqua-icon-info');
        const btnCancel = document.getElementById('aqua-confirm-btn-cancel');
        const btnConfirm = document.getElementById('aqua-confirm-btn-confirm');

        window.openAquaConfirm = function (options) {
            options = options || {};
            const variant = options.variant || 'danger'; // 'danger', 'warning', 'info'

            titleEl.textContent = options.title || (variant === 'danger' ? 'Confirmer la suppression' : 'Confirmer la modification');
            messageEl.textContent = options.message || 'Êtes-vous certain de vouloir effectuer cette opération ?';

            // Badge cible
            if (options.badge) {
                badgeEl.textContent = options.badge;
                badgeContainer.classList.remove('hidden');
            } else {
                badgeContainer.classList.add('hidden');
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

            // Réinitialiser les icônes
            iconDanger.classList.add('hidden');
            iconWarning.classList.add('hidden');
            iconInfo.classList.add('hidden');

            // Style selon la variante
            if (variant === 'danger') {
                iconDanger.classList.remove('hidden');
                iconBox.className = 'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border border-rose-500/30 bg-rose-500/10 text-rose-400 ring-4 ring-rose-500/10';
                glowEl.className = 'absolute -top-24 -left-24 h-48 w-48 rounded-full blur-3xl pointer-events-none bg-rose-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-rose-500/30 bg-rose-500/10 p-3 text-xs text-rose-200 leading-relaxed';
                btnConfirm.className = 'rounded-xl bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-400 px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-rose-950/40 transition focus:outline-none focus:ring-2 focus:ring-rose-400/40';
            } else if (variant === 'warning') {
                iconWarning.classList.remove('hidden');
                iconBox.className = 'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border border-amber-500/30 bg-amber-500/10 text-amber-400 ring-4 ring-amber-500/10';
                glowEl.className = 'absolute -top-24 -left-24 h-48 w-48 rounded-full blur-3xl pointer-events-none bg-amber-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-200 leading-relaxed';
                btnConfirm.className = 'rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-amber-950/40 transition focus:outline-none focus:ring-2 focus:ring-amber-400/40';
            } else {
                iconInfo.classList.remove('hidden');
                iconBox.className = 'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border border-cyan-400/30 bg-cyan-500/10 text-cyan-300 ring-4 ring-cyan-500/10';
                glowEl.className = 'absolute -top-24 -left-24 h-48 w-48 rounded-full blur-3xl pointer-events-none bg-cyan-500/20';
                hintEl.className = 'mt-3.5 rounded-xl border border-cyan-400/30 bg-cyan-500/10 p-3 text-xs text-cyan-200 leading-relaxed';
                btnConfirm.className = 'rounded-xl bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-500 hover:to-cyan-400 px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-cyan-950/40 transition focus:outline-none focus:ring-2 focus:ring-cyan-400/40';
            }

            currentCallback = typeof options.onConfirm === 'function' ? options.onConfirm : null;
            currentCancelCallback = typeof options.onCancel === 'function' ? options.onCancel : null;

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                dialog.classList.remove('scale-95', 'opacity-0');
                dialog.classList.add('scale-100', 'opacity-100');
                btnCancel.focus();
            });
        };

        window.closeAquaConfirm = function () {
            dialog.classList.remove('scale-100', 'opacity-100');
            dialog.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                currentCallback = null;
                currentCancelCallback = null;
            }, 180);
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
