{{-- Formulaire partagé entre create et edit --}}
@csrf

<div class="grid sm:grid-cols-2 gap-5">
    {{-- Relation : chaque intervention appartient à un technicien --}}
    <x-forms.select name="technicien_id" label="Technicien" placeholder="Choisir un technicien" :error="$errors->first('technicien_id')" required class="sm:col-span-2">
        @foreach($techniciens as $technicien)
            <option value="{{ $technicien->id }}" @selected((string) old('technicien_id', $intervention->technicien_id) === (string) $technicien->id)>
                {{ $technicien->nom }} — {{ $technicien->specialite }}
            </option>
        @endforeach
    </x-forms.select>

    {{-- Recommandation IA du technicien (Ollama, avec calcul de secours) --}}
    <div class="sm:col-span-2 -mt-2" id="ia-recommandation"
         data-url="{{ route('manager.interventions.recommandation') }}"
         data-intervention="{{ $intervention->exists ? $intervention->id : '' }}">
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" id="ia-recommander"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-cyan-600 hover:opacity-90 transition disabled:opacity-60 disabled:cursor-wait">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span>Suggérer avec l'IA</span>
            </button>
            <span class="text-xs text-cyan-100/60">Renseignez la date, l'infrastructure et la description pour une meilleure suggestion.</span>
        </div>
        <div id="ia-resultat" class="hidden mt-3 rounded-xl border border-violet-500/30 bg-violet-500/5 p-4 text-sm" aria-live="polite"></div>
    </div>

    {{-- Relation optionnelle : infrastructure concernée --}}
    <x-forms.select name="infrastructure_id" label="Infrastructure concernée (optionnel)" placeholder="Aucune (intervention non rattachée)" :error="$errors->first('infrastructure_id')" class="sm:col-span-2">
        <option value="">Aucune infrastructure spécifique</option>
        @if(isset($infrastructures))
            @foreach($infrastructures as $infra)
                <option value="{{ $infra->id }}" @selected((string) old('infrastructure_id', $intervention->infrastructure_id) === (string) $infra->id)>
                    {{ $infra->nom }} (Zone : {{ $infra->zone?->nom ?? 'Sans zone' }})
                </option>
            @endforeach
        @endif
    </x-forms.select>

    <x-forms.input type="date" name="date" label="Date de l'intervention"
        :value="$intervention->date?->format('Y-m-d')" :error="$errors->first('date')" required />

    <x-forms.select name="statut" label="Statut" :placeholder="null" :error="$errors->first('statut')" required>
        @foreach(\App\Models\Intervention::STATUTS as $statut)
            <option value="{{ $statut }}" @selected(old('statut', $intervention->statut) === $statut)>{{ $statut }}</option>
        @endforeach
    </x-forms.select>

    <x-forms.select name="type" label="Type" :placeholder="null" :error="$errors->first('type')" required>
        @foreach(\App\Models\Intervention::TYPES as $type)
            <option value="{{ $type }}" @selected(old('type', $intervention->type) === $type)>{{ $type }}</option>
        @endforeach
    </x-forms.select>

    <x-forms.input name="cout" label="Coût (DT)" icon="banknote" placeholder="ex. 1250.50"
        :value="$intervention->cout" :error="$errors->first('cout')" required />

    <x-forms.textarea name="description" label="Description" rows="4" maxlength="1000"
        placeholder="ex. Réparation d'une fuite sur la canalisation principale"
        :value="$intervention->description" :error="$errors->first('description')" required class="sm:col-span-2" />
</div>

<div class="flex items-center justify-end gap-2 mt-8 pt-6 border-t border-white/10">
    <x-maintenance.link-button :href="$intervention->exists ? route('manager.interventions.show', $intervention) : route('manager.interventions.index')" variant="secondary">
        Annuler
    </x-maintenance.link-button>
    <x-ui.button type="submit" icon="save">{{ $submitLabel }}</x-ui.button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var bloc = document.getElementById('ia-recommandation');
    if (! bloc) { return; }

    var bouton = document.getElementById('ia-recommander');
    var resultat = document.getElementById('ia-resultat');
    var libelle = bouton.querySelector('span');

    // Construction en DOM (textContent) : le texte du modèle n'est jamais interprété comme du HTML
    function el(tag, classes, texte) {
        var e = document.createElement(tag);
        if (classes) { e.className = classes; }
        if (texte !== undefined && texte !== null) { e.textContent = texte; }
        return e;
    }

    function choisir(id) {
        var select = document.getElementById('technicien_id');
        select.value = String(id);
        select.dispatchEvent(new Event('change'));
    }

    function afficher(data) {
        resultat.replaceChildren();
        resultat.classList.remove('hidden');

        if (data.source === 'aucun') {
            resultat.appendChild(el('p', 'text-rose-300', data.message));
            return;
        }

        var entete = el('div', 'flex flex-wrap items-center gap-2 mb-2');
        entete.appendChild(el('span', 'font-semibold text-white', 'Suggestion : ' + data.technicien.nom + ' (' + data.technicien.specialite + ')'));
        entete.appendChild(el('span',
            'text-[11px] font-semibold rounded-md px-1.5 py-0.5 ' + (data.source === 'ia'
                ? 'text-violet-200 bg-violet-500/20 border border-violet-500/40'
                : 'text-amber-200 bg-amber-500/15 border border-amber-500/30'),
            data.source === 'ia' ? 'IA (Ollama)' : 'Calcul de secours'));
        resultat.appendChild(entete);

        resultat.appendChild(el('p', 'text-cyan-100/85 mb-2', data.justification));
        if (data.vigilance) {
            resultat.appendChild(el('p', 'text-amber-300/90 text-xs mb-2', '⚠ ' + data.vigilance));
        }
        if (data.specialite_attendue) {
            resultat.appendChild(el('p', 'text-xs text-cyan-100/50 mb-3', 'Spécialité attendue : ' + data.specialite_attendue));
        }

        var liste = el('div', 'grid gap-2 sm:grid-cols-3');
        data.classement.forEach(function (c) {
            var carte = el('div', 'rounded-lg border border-white/10 bg-white/[.03] p-3 flex flex-col gap-1'
                + (c.id === data.technicien.id ? ' ring-1 ring-violet-400/60' : ''));
            carte.appendChild(el('span', 'font-semibold text-white', c.nom));
            carte.appendChild(el('span', 'text-xs text-cyan-100/60', c.specialite + ' · score ' + c.score));
            carte.appendChild(el('span', 'text-xs text-cyan-100/50', c.raisons.join(' · ')));
            var choix = el('button', 'mt-1 self-start text-xs font-semibold text-cyan-300 hover:text-white', 'Choisir');
            choix.type = 'button';
            choix.addEventListener('click', function () { choisir(c.id); });
            carte.appendChild(choix);
            liste.appendChild(carte);
        });
        resultat.appendChild(liste);

        choisir(data.technicien.id);
    }

    bouton.addEventListener('click', function () {
        var date = document.getElementById('date').value;
        if (! date) {
            resultat.classList.remove('hidden');
            resultat.replaceChildren(el('p', 'text-amber-300', 'Choisissez d\'abord la date de l\'intervention.'));
            return;
        }

        bouton.disabled = true;
        libelle.textContent = 'Analyse en cours…';

        fetch(bloc.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                date: date,
                description: document.getElementById('description').value,
                infrastructure_id: document.getElementById('infrastructure_id').value || null,
                intervention_id: bloc.dataset.intervention || null
            })
        })
        .then(function (response) {
            return response.json().then(function (data) { return { ok: response.ok, data: data }; });
        })
        .then(function (r) {
            if (r.ok) {
                afficher(r.data);
            } else {
                var message = r.data.message || 'La suggestion a échoué.';
                resultat.classList.remove('hidden');
                resultat.replaceChildren(el('p', 'text-rose-300', message));
            }
        })
        .catch(function () {
            resultat.classList.remove('hidden');
            resultat.replaceChildren(el('p', 'text-rose-300', 'Erreur réseau : réessayez.'));
        })
        .finally(function () {
            bouton.disabled = false;
            libelle.textContent = 'Suggérer avec l\'IA';
        });
    });
});
</script>
@endpush
