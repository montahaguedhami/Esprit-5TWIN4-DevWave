@extends('layouts.manager')

@section('title', 'Rapport d\'Intervention #' . $intervention->id)

@section('manager-content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center gap-3.5">
        <a href="{{ route('technician.interventions.show', $intervention) }}"
           class="w-10 h-10 rounded-xl glass border border-white/15 flex items-center justify-center text-cyan-300 hover:text-white transition-colors shrink-0">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-cyan-500/10 border border-cyan-400/20 text-cyan-400 text-xs font-semibold mb-1">
                <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                Rapport Terrain Officiel
            </div>
            <h1 class="text-2xl font-bold font-display text-white">Clôture & Rapport d'Intervention</h1>
            <p class="text-cyan-100/60 text-xs">
                Intervention #{{ $intervention->id }} · {{ $intervention->infrastructure?->nom ?? 'Ouvrage' }}
            </p>
        </div>
    </div>

    {{-- Formulaire de rapport --}}
    <form method="POST" action="{{ route('technician.interventions.report.store', $intervention) }}" class="glass rounded-3xl p-6 border border-white/10 space-y-6 shadow-xl">
        @csrf

        {{-- Erreurs éventuelles --}}
        @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Veuillez corriger les points suivants :
            </div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Résultat de l'intervention --}}
        <div>
            <label class="block text-xs font-semibold text-cyan-100/80 mb-2.5">
                Résultat opérationnel de l'intervention <span class="text-rose-400">*</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach([
                    ['val' => 'resolved', 'label' => 'Problème résolu', 'desc' => 'Remise en service complète', 'icon' => 'check-circle', 'color' => 'emerald'],
                    ['val' => 'partial', 'label' => 'Résolution partielle', 'desc' => 'Fonctionnement dégradé', 'icon' => 'alert-circle', 'color' => 'amber'],
                    ['val' => 'failed', 'label' => 'Échec / Pièce manquante', 'desc' => 'Ouvrage à l\'arrêt', 'icon' => 'x-circle', 'color' => 'rose'],
                    ['val' => 'followup', 'label' => 'Suivi nécessaire', 'desc' => 'Nouvelle visite requise', 'icon' => 'clock', 'color' => 'cyan'],
                ] as $r)
                <label class="p-3.5 rounded-2xl bg-slate-900/60 border border-white/10 hover:border-cyan-400/40 cursor-pointer flex items-start gap-3 transition-all relative group">
                    <input type="radio" name="status" value="{{ $r['val'] }}" required @checked(old('status', 'resolved') === $r['val']) class="mt-1 text-cyan-500 focus:ring-cyan-400">
                    <div>
                        <div class="text-xs font-bold text-white flex items-center gap-1.5">
                            <span>{{ $r['label'] }}</span>
                        </div>
                        <span class="text-[11px] text-cyan-100/50 block mt-0.5">{{ $r['desc'] }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Nouvel état opérationnel de l'infrastructure --}}
        @if($intervention->infrastructure)
        <div>
            <label for="nouvel_etat" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                État final de l'infrastructure ({{ $intervention->infrastructure->nom }})
            </label>
            <select id="nouvel_etat" name="nouvel_etat" class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs focus:ring-2 focus:ring-cyan-400">
                <option value="">-- Automatique selon le résultat sélectionné --</option>
                @foreach($etats as $e)
                    <option value="{{ $e->value }}" @selected(old('nouvel_etat') === $e->value)>
                        {{ $e->label() }}
                    </option>
                @endforeach
            </select>
            <p class="text-[11px] text-cyan-100/50 mt-1">
                La date de dernière maintenance de l'ouvrage sera mise à jour à aujourd'hui et les alertes opérationnelles seront clôturées.
            </p>
        </div>
        @endif

        {{-- Travaux effectués (Obligatoire) --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="work_performed" class="block text-xs font-semibold text-cyan-100/80">
                    Travaux effectués sur le terrain <span class="text-rose-400">*</span>
                </label>
                <span id="work-counter" class="text-[11px] text-cyan-100/40 font-mono">0 / 3000</span>
            </div>
            <textarea id="work_performed"
                      name="work_performed"
                      rows="4"
                      required
                      minlength="5"
                      maxlength="3000"
                      placeholder="Décrivez les réparations effectuées, tests de pression, étanchéité vérifiée, mesures de tension..."
                      oninput="document.getElementById('work-counter').textContent = this.value.length + ' / 3000'"
                      class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">{{ old('work_performed') }}</textarea>
        </div>

        {{-- Cause identifiée & Observations --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="cause" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                    Origine / Cause de la panne (Optionnel)
                </label>
                <textarea id="cause"
                          name="cause"
                          rows="3"
                          placeholder="Ex : Usure du joint d'étanchéité, surtension secteur..."
                          class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">{{ old('cause') }}</textarea>
            </div>

            <div>
                <label for="observations" class="block text-xs font-semibold text-cyan-100/80 mb-1.5">
                    Recommandations pour les prochaines visites
                </label>
                <textarea id="observations"
                          name="observations"
                          rows="3"
                          placeholder="Ex : Prévoir remplacement du clapet anti-retour dans 6 mois..."
                          class="w-full px-4 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs placeholder-cyan-100/40 focus:ring-2 focus:ring-cyan-400">{{ old('observations') }}</textarea>
            </div>
        </div>

        {{-- Pièces détachées utilisées (Sans coût) --}}
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-semibold text-cyan-100/80">
                    Pièces détachées remplacées (Optionnel)
                </label>
                <button type="button" onclick="addPartRow()" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Ajouter une pièce
                </button>
            </div>

            <div id="parts-container" class="space-y-2">
                <div class="flex items-center gap-2">
                    <input type="text" name="parts[0][name]" placeholder="Désignation de la pièce (ex: Joint torique DN100)" class="flex-1 px-3 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs">
                    <input type="number" name="parts[0][qty]" value="1" min="1" class="w-20 px-3 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs text-center" placeholder="Qté">
                </div>
            </div>
        </div>

        {{-- Bouton de soumission --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
            <a href="{{ route('technician.interventions.show', $intervention) }}"
               class="px-4 py-2.5 rounded-xl text-xs font-semibold glass text-cyan-100/70 hover:text-white transition-all">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-950/40 transition-all flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Valider le rapport & Clôturer</span>
            </button>
        </div>
    </form>

</div>

<script>
let partIndex = 1;
function addPartRow() {
    const container = document.getElementById('parts-container');
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2';
    div.innerHTML = `
        <input type="text" name="parts[${partIndex}][name]" placeholder="Désignation de la pièce" class="flex-1 px-3 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs">
        <input type="number" name="parts[${partIndex}][qty]" value="1" min="1" class="w-20 px-3 py-2 rounded-xl bg-slate-900 border border-white/20 text-white text-xs text-center" placeholder="Qté">
        <button type="button" onclick="this.parentElement.remove()" class="p-2 text-rose-400 hover:text-rose-300">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
    `;
    container.appendChild(div);
    partIndex++;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>
@endsection
