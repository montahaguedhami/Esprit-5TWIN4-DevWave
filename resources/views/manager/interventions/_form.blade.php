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

    <x-forms.input type="date" name="date" label="Date de l'intervention"
        :value="$intervention->date?->format('Y-m-d')" :error="$errors->first('date')" required />

    <x-forms.select name="statut" label="Statut" :placeholder="null" :error="$errors->first('statut')" required>
        @foreach(\App\Models\Intervention::STATUTS as $statut)
            <option value="{{ $statut }}" @selected(old('statut', $intervention->statut) === $statut)>{{ $statut }}</option>
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
