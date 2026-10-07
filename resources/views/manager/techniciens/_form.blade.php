{{-- Formulaire partagé entre create et edit --}}
@csrf

<div class="grid sm:grid-cols-2 gap-5">
    <x-forms.input name="nom" label="Nom complet" icon="user" placeholder="ex. Amira Ben Ali"
        :value="$technicien->nom" :error="$errors->first('nom')" required />

    <x-forms.input name="telephone" label="Téléphone" icon="phone" placeholder="+216 22 123 456"
        :value="$technicien->telephone" :error="$errors->first('telephone')" required />

    <x-forms.select name="specialite" label="Spécialité" :error="$errors->first('specialite')" required>
        @foreach(\App\Models\Technicien::SPECIALITES as $specialite)
            <option value="{{ $specialite }}" @selected(old('specialite', $technicien->specialite) === $specialite)>{{ $specialite }}</option>
        @endforeach
    </x-forms.select>

    <x-forms.select name="disponibilite" label="Disponibilité" :placeholder="null" :error="$errors->first('disponibilite')" required>
        @foreach(\App\Models\Technicien::DISPONIBILITES as $disponibilite)
            <option value="{{ $disponibilite }}" @selected(old('disponibilite', $technicien->disponibilite) === $disponibilite)>{{ $disponibilite }}</option>
        @endforeach
    </x-forms.select>
</div>

<div class="flex items-center justify-end gap-2 mt-8 pt-6 border-t border-white/10">
    <x-maintenance.link-button :href="$technicien->exists ? route('manager.techniciens.show', $technicien) : route('manager.techniciens.index')" variant="secondary">
        Annuler
    </x-maintenance.link-button>
    <x-ui.button type="submit" icon="save">{{ $submitLabel }}</x-ui.button>
</div>
