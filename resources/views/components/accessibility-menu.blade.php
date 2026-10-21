{{-- AquaSecure Accessibility Suite & Modal --}}
<div id="aq-accessibility-container">

    {{-- Reading Guide Bar (Hidden by default) --}}
    <div id="aq-reading-ruler" class="aq-reading-ruler" style="display:none;" aria-hidden="true">
        <div class="aq-reading-ruler-line"></div>
    </div>

    {{-- Accessibility Modal Backdrop & Dialog --}}
    <div id="aq-accessibility-modal"
         class="aq-modal-backdrop"
         aria-modal="true"
         role="dialog"
         aria-labelledby="aq-acc-title"
         style="display:none;">

        <div class="aq-acc-panel" role="document">
            {{-- Header --}}
            <div class="aq-acc-header">
                <div class="aq-acc-header-brand">
                    <div class="aq-acc-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="4.5" r="2.2"/>
                            <path d="M12 7v6.5"/>
                            <path d="m5 10 7 1.5 7-1.5"/>
                            <path d="m7.5 21 4.5-7.5 4.5 7.5"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="aq-acc-title" class="aq-acc-title">Confort & Accessibilité</h2>
                        <p class="aq-acc-subtitle">Adaptez l'affichage d'AquaSecure à vos besoins visuels et moteurs</p>
                    </div>
                </div>
                <button type="button"
                        onclick="closeAccessibilityModal()"
                        class="aq-acc-close-btn"
                        aria-label="Fermer les paramètres d'accessibilité">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body Options --}}
            <div class="aq-acc-body">

                {{-- Option 1: Taille de Police --}}
                <div class="aq-acc-card">
                    <div class="aq-acc-card-header">
                        <div class="aq-acc-card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19V7h16v12"/><path d="M10 7v12"/><path d="M14 7v12"/></svg>
                            <span>Taille du texte</span>
                        </div>
                        <span class="aq-acc-tag" id="aq-font-tag">Standard</span>
                    </div>
                    <div class="aq-acc-btn-group" role="group" aria-label="Taille du texte">
                        <button type="button" class="aq-choice-btn" data-font="normal" onclick="setAqFont('normal')">
                            <span style="font-size: 13px; font-weight: 600;">A</span>
                            <span>Standard (100%)</span>
                        </button>
                        <button type="button" class="aq-choice-btn" data-font="lg" onclick="setAqFont('lg')">
                            <span style="font-size: 16px; font-weight: 700;">A+</span>
                            <span>Grand (115%)</span>
                        </button>
                        <button type="button" class="aq-choice-btn" data-font="xl" onclick="setAqFont('xl')">
                            <span style="font-size: 19px; font-weight: 800;">A++</span>
                            <span>Très grand (130%)</span>
                        </button>
                    </div>
                </div>

                {{-- Option 2: Vision & Contrastes --}}
                <div class="aq-acc-card">
                    <div class="aq-acc-card-header">
                        <div class="aq-acc-card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20z"/></svg>
                            <span>Vision & Contrastes</span>
                        </div>
                    </div>
                    <div class="aq-acc-toggles-grid">
                        {{-- High Contrast --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-contrast" onclick="toggleAqSetting('contrast')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m16 8-8 8"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Contraste Élevé</strong>
                                    <p class="aq-toggle-desc">Bordures nettes et lisibilité renforcée</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>

                        {{-- Grayscale --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-grayscale" onclick="toggleAqSetting('grayscale')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m3 9 18 0"/><path d="m3 15 18 0"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Niveaux de gris</strong>
                                    <p class="aq-toggle-desc">Supprime les couleurs saturées</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>
                    </div>
                </div>

                {{-- Option 3: Lisibilité & Dyslexie --}}
                <div class="aq-acc-card">
                    <div class="aq-acc-card-header">
                        <div class="aq-acc-card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <span>Lisibilité du texte & Dyslexie</span>
                        </div>
                    </div>
                    <div class="aq-acc-toggles-grid">
                        {{-- Dyslexia friendly font --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-dyslexia" onclick="toggleAqSetting('dyslexia')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Police Lisible (Dyslexie)</strong>
                                    <p class="aq-toggle-desc">Espacement optimisé pour faciliter la lecture</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>

                        {{-- Line height / spacing --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-spacing" onclick="toggleAqSetting('spacing')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 3v18M18 3v18M10 6h4M10 12h4M10 18h4"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Interligne Élargi</strong>
                                    <p class="aq-toggle-desc">Augmente l'espace entre chaque ligne de texte</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>
                    </div>
                </div>

                {{-- Option 4: Aides Visuelles & Navigation --}}
                <div class="aq-acc-card">
                    <div class="aq-acc-card-header">
                        <div class="aq-acc-card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            <span>Aides visuelles & Repères</span>
                        </div>
                    </div>
                    <div class="aq-acc-toggles-grid">
                        {{-- Underline links --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-links" onclick="toggleAqSetting('links')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17H5a3 3 0 0 1 0-6h4"/><path d="M15 7h4a3 3 0 0 1 0 6h-4"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="4" y1="21" x2="20" y2="21"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Souligner tous les liens</strong>
                                    <p class="aq-toggle-desc">Met en relief les éléments cliquables</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>

                        {{-- Reading ruler --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-ruler" onclick="toggleAqSetting('ruler')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="8" x="2" y="8" rx="1"/><path d="M6 8v3M10 8v2M14 8v3M18 8v2"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Guide de lecture</strong>
                                    <p class="aq-toggle-desc">Bandeau repère suivant le curseur</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>

                        {{-- Big Cursor --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-cursor" onclick="toggleAqSetting('cursor')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m4 4 7.07 17 2.51-7.39L21 11.07z"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Curseur Agrandie</strong>
                                    <p class="aq-toggle-desc">Pointeur de souris grand et contrasté</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>

                        {{-- Reduce motion --}}
                        <button type="button" class="aq-toggle-card" id="btn-toggle-motion" onclick="toggleAqSetting('motion')">
                            <div class="aq-toggle-content">
                                <div class="aq-toggle-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                </div>
                                <div>
                                    <strong class="aq-toggle-label">Réduire les animations</strong>
                                    <p class="aq-toggle-desc">Arrête les mouvements et scintillements</p>
                                </div>
                            </div>
                            <span class="aq-switch-dot"></span>
                        </button>
                    </div>
                </div>

                {{-- Option 5: Synthèse Vocale Rapide --}}
                <div class="aq-acc-card">
                    <div class="aq-acc-card-header">
                        <div class="aq-acc-card-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                            <span>Lecture Vocale (Synthèse)</span>
                        </div>
                    </div>
                    <div class="aq-tts-container">
                        <p class="aq-tts-desc">Sélectionnez du texte sur n'importe quelle page puis cliquez ci-dessous pour l'écouter :</p>
                        <div class="aq-tts-actions">
                            <button type="button" class="aq-tts-btn" onclick="aqSpeakSelectedText()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                                <span>Lire la sélection actuelle</span>
                            </button>
                            <button type="button" class="aq-tts-btn aq-tts-stop" onclick="aqStopSpeaking()">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                                <span>Stop</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="aq-acc-footer">
                <button type="button" class="aq-btn-reset" onclick="resetAqSettings()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span>Réinitialiser par défaut</span>
                </button>
                <div class="aq-acc-footer-right">
                    <span class="aq-save-note">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                        Mémorisé automatiquement
                    </span>
                    <button type="button" class="aq-btn-apply" onclick="closeAccessibilityModal()">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Styles for Accessibility Suite --}}
<style>
/* ─── Topbar Accessibility Button Active State ─── */
.aq-topbar-acc-btn {
    position: relative;
}
.aq-topbar-acc-btn.has-active::after {
    content: '';
    position: absolute;
    top: 5px;
    right: 5px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #0284c7;
    box-shadow: 0 0 6px rgba(2, 132, 199, 0.85);
}

/* ─── Reading Ruler ─── */
.aq-reading-ruler {
    position: fixed;
    left: 0;
    right: 0;
    height: 42px;
    background: rgba(14, 165, 233, 0.12);
    border-top: 2px solid #0284c7;
    border-bottom: 2px solid #0284c7;
    pointer-events: none;
    z-index: 999999;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.28);
    transform: translateY(-50%);
    transition: top 0.04s linear;
}

/* ─── Modal Backdrop ─── */
.aq-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: aqFadeIn 0.2s ease-out forwards;
}
@keyframes aqFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* ─── Modal Dialog Panel ─── */
.aq-acc-panel {
    width: 100%;
    max-width: 680px;
    max-height: 90vh;
    background: #0f172a;
    color: #f8fafc;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.08);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: aqScaleUp 0.24s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
[data-theme="light"] .aq-acc-panel {
    background: #ffffff;
    color: #0f172a;
    border-color: #cbd5e1;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
}
@keyframes aqScaleUp {
    from { transform: scale(0.96) translateY(8px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

/* ─── Header ─── */
.aq-acc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}
[data-theme="light"] .aq-acc-header {
    border-bottom-color: #e2e8f0;
}
.aq-acc-header-brand {
    display: flex;
    align-items: center;
    gap: 14px;
}
.aq-acc-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, rgba(2, 132, 199, 0.25), rgba(14, 165, 233, 0.15));
    border: 1px solid rgba(56, 189, 248, 0.35);
    color: #38bdf8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.aq-acc-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    letter-spacing: -0.01em;
}
.aq-acc-subtitle {
    font-size: 12px;
    color: #94a3b8;
    margin: 2px 0 0;
}
[data-theme="light"] .aq-acc-subtitle {
    color: #64748b;
}
.aq-acc-close-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: transparent;
    border: 1px solid transparent;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s;
}
.aq-acc-close-btn:hover {
    background: rgba(148, 163, 184, 0.12);
    color: #f1f5f9;
}
[data-theme="light"] .aq-acc-close-btn:hover {
    color: #0f172a;
}

/* ─── Body ─── */
.aq-acc-body {
    padding: 20px 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.aq-acc-card {
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(148, 163, 184, 0.15);
    border-radius: 14px;
    padding: 16px;
}
[data-theme="light"] .aq-acc-card {
    background: #f8fafc;
    border-color: #e2e8f0;
}
.aq-acc-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.aq-acc-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #38bdf8;
}
[data-theme="light"] .aq-acc-card-title {
    color: #0284c7;
}
.aq-acc-tag {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    background: rgba(56, 189, 248, 0.15);
    color: #38bdf8;
}
[data-theme="light"] .aq-acc-tag {
    background: rgba(2, 132, 199, 0.1);
    color: #0284c7;
}

/* ─── Button group for fonts ─── */
.aq-acc-btn-group {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.aq-choice-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 10px 8px;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 10px;
    color: #e2e8f0;
    cursor: pointer;
    font-family: inherit;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.18s;
}
[data-theme="light"] .aq-choice-btn {
    background: #ffffff;
    color: #334155;
    border-color: #cbd5e1;
}
.aq-choice-btn:hover {
    border-color: #38bdf8;
    background: rgba(56, 189, 248, 0.08);
}
.aq-choice-btn.is-active {
    background: rgba(2, 132, 199, 0.2);
    border-color: #38bdf8;
    color: #38bdf8;
    font-weight: 700;
    box-shadow: 0 0 0 1px #38bdf8;
}

/* ─── Toggle cards grid ─── */
.aq-acc-toggles-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
@media (max-width: 600px) {
    .aq-acc-toggles-grid {
        grid-template-columns: 1fr;
    }
}
.aq-toggle-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px;
    background: rgba(15, 23, 42, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 10px;
    cursor: pointer;
    text-align: left;
    transition: all 0.18s;
    color: inherit;
}
[data-theme="light"] .aq-toggle-card {
    background: #ffffff;
    border-color: #cbd5e1;
}
.aq-toggle-card:hover {
    border-color: #38bdf8;
}
.aq-toggle-card.is-active {
    background: rgba(2, 132, 199, 0.16);
    border-color: #38bdf8;
}
.aq-toggle-content {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.aq-toggle-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(148, 163, 184, 0.1);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.15s;
}
.aq-toggle-card.is-active .aq-toggle-icon {
    background: rgba(56, 189, 248, 0.2);
    color: #38bdf8;
}
.aq-toggle-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #f1f5f9;
}
[data-theme="light"] .aq-toggle-label {
    color: #0f172a;
}
.aq-toggle-desc {
    margin: 2px 0 0;
    font-size: 11px;
    color: #94a3b8;
    line-height: 1.3;
}
[data-theme="light"] .aq-toggle-desc {
    color: #64748b;
}

/* Switch dot indicator */
.aq-switch-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #64748b;
    background: transparent;
    flex-shrink: 0;
    transition: all 0.2s;
}
.aq-toggle-card.is-active .aq-switch-dot {
    border-color: #38bdf8;
    background: #38bdf8;
    box-shadow: 0 0 8px rgba(56, 189, 248, 0.6);
}

/* ─── TTS Box ─── */
.aq-tts-container {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.aq-tts-desc {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}
[data-theme="light"] .aq-tts-desc {
    color: #64748b;
}
.aq-tts-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.aq-tts-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    background: rgba(2, 132, 199, 0.2);
    color: #38bdf8;
    border: 1px solid rgba(56, 189, 248, 0.35);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.aq-tts-btn:hover {
    background: rgba(2, 132, 199, 0.35);
}
.aq-tts-stop {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.35);
}
.aq-tts-stop:hover {
    background: rgba(239, 68, 68, 0.25);
}

/* ─── Footer ─── */
.aq-acc-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-top: 1px solid rgba(148, 163, 184, 0.15);
    background: rgba(15, 23, 42, 0.4);
}
[data-theme="light"] .aq-acc-footer {
    border-top-color: #e2e8f0;
    background: #f8fafc;
}
.aq-btn-reset {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 8px;
    background: transparent;
    border: 1px solid rgba(148, 163, 184, 0.25);
    color: #94a3b8;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}
.aq-btn-reset:hover {
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.4);
    color: #f87171;
}
.aq-acc-footer-right {
    display: flex;
    align-items: center;
    gap: 14px;
}
.aq-save-note {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #10b981;
    font-weight: 500;
}
.aq-btn-apply {
    padding: 8px 20px;
    border-radius: 8px;
    background: #0284c7;
    color: #ffffff;
    border: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.aq-btn-apply:hover {
    background: #0369a1;
}

/* ══════════════════════════════════════════════════════════════════════════════
   GLOBAL ACCESSIBILITY ACTIVE EFFECT RULES
   ══════════════════════════════════════════════════════════════════════════════ */

/* 1. Font Zoom */
html[data-aq-font="lg"] {
    font-size: 115% !important;
}
html[data-aq-font="xl"] {
    font-size: 130% !important;
}

/* 2. High Contrast */
html.aq-contrast {
    filter: contrast(125%) !important;
}
html.aq-contrast body {
    text-shadow: 0 0 1px rgba(0, 0, 0, 0.3);
}
html.aq-contrast a,
html.aq-contrast button,
html.aq-contrast input,
html.aq-contrast select {
    border-color: #38bdf8 !important;
}

/* 3. Grayscale */
html.aq-grayscale {
    filter: grayscale(100%) !important;
}

/* 4. Dyslexia-friendly Font & Letter Spacing */
html.aq-dyslexia * {
    font-family: 'OpenDyslexic', 'Trebuchet MS', 'Verdana', sans-serif !important;
    letter-spacing: 0.05em !important;
    word-spacing: 0.12em !important;
}

/* 5. Extra Spacing */
html.aq-spacing * {
    line-height: 1.85 !important;
}

/* 6. Underline all links */
html.aq-links a {
    text-decoration: underline !important;
    text-decoration-thickness: 2px !important;
    text-underline-offset: 3px !important;
    text-decoration-color: #38bdf8 !important;
    font-weight: 600 !important;
}

/* 7. Big Cursor */
html.aq-cursor,
html.aq-cursor * {
    cursor: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 32 32'%3E%3Cpath d='M4 4l9.5 24 3.5-10.5L28 14z' fill='%230284c7' stroke='%23ffffff' stroke-width='2'/%3E%3C/svg%3E"), auto !important;
}

/* 8. Reduced Motion */
html.aq-motion *,
html.aq-motion *::before,
html.aq-motion *::after {
    animation-duration: 0.001ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.001ms !important;
    scroll-behavior: auto !important;
}
</style>

{{-- JavaScript Engine for Accessibility --}}
<script>
(function() {
    var STORAGE_KEY = 'aq-accessibility-preferences';

    var defaults = {
        font: 'normal',
        contrast: false,
        grayscale: false,
        dyslexia: false,
        spacing: false,
        links: false,
        ruler: false,
        cursor: false,
        motion: false
    };

    function loadPrefs() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            return raw ? Object.assign({}, defaults, JSON.parse(raw)) : Object.assign({}, defaults);
        } catch(e) {
            return Object.assign({}, defaults);
        }
    }

    function savePrefs(prefs) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
        } catch(e) {}
    }

    window.aqPrefs = loadPrefs();

    function applyPreferences(prefs) {
        var root = document.documentElement;

        // Font
        if (prefs.font === 'lg' || prefs.font === 'xl') {
            root.setAttribute('data-aq-font', prefs.font);
        } else {
            root.removeAttribute('data-aq-font');
        }

        // Toggles
        var toggleMap = {
            contrast: 'aq-contrast',
            grayscale: 'aq-grayscale',
            dyslexia: 'aq-dyslexia',
            spacing: 'aq-spacing',
            links: 'aq-links',
            cursor: 'aq-cursor',
            motion: 'aq-motion'
        };

        var activeCount = 0;
        if (prefs.font !== 'normal') activeCount++;

        Object.keys(toggleMap).forEach(function(key) {
            var className = toggleMap[key];
            if (prefs[key]) {
                root.classList.add(className);
                activeCount++;
            } else {
                root.classList.remove(className);
            }
        });

        // Reading Ruler
        var rulerEl = document.getElementById('aq-reading-ruler');
        if (rulerEl) {
            if (prefs.ruler) {
                rulerEl.style.display = 'block';
                activeCount++;
            } else {
                rulerEl.style.display = 'none';
            }
        }

        // Update active state on topbar buttons
        document.querySelectorAll('.aq-topbar-acc-btn').forEach(function(btn) {
            if (activeCount > 0) {
                btn.classList.add('has-active');
            } else {
                btn.classList.remove('has-active');
            }
        });

        // Update modal UI state
        updateModalControls(prefs);
    }

    function updateModalControls(prefs) {
        // Font buttons
        document.querySelectorAll('.aq-choice-btn').forEach(function(btn) {
            var f = btn.getAttribute('data-font');
            if (f === prefs.font) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        });

        var tag = document.getElementById('aq-font-tag');
        if (tag) {
            var labelMap = { normal: 'Standard', lg: 'Grand (115%)', xl: 'Très grand (130%)' };
            tag.textContent = labelMap[prefs.font] || 'Standard';
        }

        // Toggle cards
        var toggleCards = {
            contrast: 'btn-toggle-contrast',
            grayscale: 'btn-toggle-grayscale',
            dyslexia: 'btn-toggle-dyslexia',
            spacing: 'btn-toggle-spacing',
            links: 'btn-toggle-links',
            ruler: 'btn-toggle-ruler',
            cursor: 'btn-toggle-cursor',
            motion: 'btn-toggle-motion'
        };

        Object.keys(toggleCards).forEach(function(key) {
            var el = document.getElementById(toggleCards[key]);
            if (el) {
                if (prefs[key]) {
                    el.classList.add('is-active');
                } else {
                    el.classList.remove('is-active');
                }
            }
        });
    }

    // Modal Control Functions
    window.openAccessibilityModal = function() {
        var modal = document.getElementById('aq-accessibility-modal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            updateModalControls(window.aqPrefs);
        }
    };

    window.closeAccessibilityModal = function() {
        var modal = document.getElementById('aq-accessibility-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    };

    // Fermeture au clic sur le fond (hors de la boîte de dialogue)
    var aqModalFond = document.getElementById('aq-accessibility-modal');
    if (aqModalFond) {
        aqModalFond.addEventListener('click', function (e) {
            if (e.target === aqModalFond) window.closeAccessibilityModal();
        });
    }

    window.toggleAccessibilityModal = function() {
        var modal = document.getElementById('aq-accessibility-modal');
        if (modal && modal.style.display === 'flex') {
            window.closeAccessibilityModal();
        } else {
            window.openAccessibilityModal();
        }
    };

    window.setAqFont = function(size) {
        window.aqPrefs.font = size;
        savePrefs(window.aqPrefs);
        applyPreferences(window.aqPrefs);
    };

    window.toggleAqSetting = function(key) {
        if (typeof window.aqPrefs[key] !== 'undefined') {
            window.aqPrefs[key] = !window.aqPrefs[key];
            savePrefs(window.aqPrefs);
            applyPreferences(window.aqPrefs);
        }
    };

    window.resetAqSettings = function() {
        window.aqPrefs = Object.assign({}, defaults);
        savePrefs(window.aqPrefs);
        applyPreferences(window.aqPrefs);
        if (typeof showToast === 'function') {
            showToast('Paramètres d\'accessibilité réinitialisés', 'info');
        }
    };

    // Text-to-speech
    window.aqSpeakSelectedText = function() {
        var selection = window.getSelection().toString().trim();
        if (!selection) {
            if (typeof showToast === 'function') {
                showToast('Veuillez d\'abord sélectionner du texte à lire.', 'info');
            } else {
                alert('Veuillez d\'abord sélectionner du texte sur la page.');
            }
            return;
        }

        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            var utterance = new SpeechSynthesisUtterance(selection);
            utterance.lang = 'fr-FR';
            utterance.rate = 0.95;
            window.speechSynthesis.speak(utterance);
            if (typeof showToast === 'function') {
                showToast('Lecture vocale en cours...', 'success');
            }
        } else {
            alert('La synthèse vocale n\'est pas supportée par votre navigateur.');
        }
    };

    window.aqStopSpeaking = function() {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            if (typeof showToast === 'function') {
                showToast('Lecture vocale arrêtée', 'info');
            }
        }
    };

    // Keyboard Shortcuts (Alt+A / Escape)
    document.addEventListener('keydown', function(e) {
        if (e.altKey && (e.key === 'a' || e.key === 'A')) {
            e.preventDefault();
            window.toggleAccessibilityModal();
        }
        if (e.key === 'Escape') {
            window.closeAccessibilityModal();
        }
    });

    // Reading Ruler Movement Listener
    document.addEventListener('mousemove', function(e) {
        if (window.aqPrefs && window.aqPrefs.ruler) {
            var ruler = document.getElementById('aq-reading-ruler');
            if (ruler) {
                ruler.style.top = e.clientY + 'px';
            }
        }
    });

    // Initial apply on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        applyPreferences(window.aqPrefs);
    });

    // Run immediate apply in case DOM is already ready
    applyPreferences(window.aqPrefs);
})();
</script>
