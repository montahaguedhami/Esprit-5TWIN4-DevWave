@extends('layouts.manager')

@section('title', 'Rapport d\'intervention')

@php
    use App\Data\PlaceholderData;
    $interventions = PlaceholderData::technicianInterventions();
    $intervention = $interventions[0];
@endphp

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('technician.interventions.show', ['id' => $intervention['id']]) }}" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
        </a>
        <div>
            <h1 class="text-3xl font-display font-bold text-white">Rapport d'intervention</h1>
            <p class="text-cyan-100/60 text-sm mt-1">{{ $intervention['id'] }} - {{ $intervention['type'] }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('technician.interventions.report.store', ['id' => $intervention['id']]) }}" enctype="multipart/form-data">
            @csrf

            <!-- Intervention Summary -->
            <x-ui.card class="mb-6">
                <h3 class="text-lg font-display font-bold text-white mb-4">Récapitulatif</h3>
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Zone</span>
                        <span class="text-white">{{ $intervention['zone'] }}</span>
                    </div>
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Adresse</span>
                        <span class="text-white">{{ $intervention['address'] }}</span>
                    </div>
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Démarré</span>
                        <span class="text-white">{{ \Carbon\Carbon::parse($intervention['started_at'])->format('d/m/Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-cyan-100/50 block mb-1">Terminé</span>
                        <span class="text-white">{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Status -->
            <x-ui.card class="mb-6">
                <h3 class="text-lg font-display font-bold text-white mb-4">Résultat de l'intervention</h3>
                
                <div class="grid sm:grid-cols-2 gap-4 mb-6">
                    @foreach([
                        ['value' => 'resolved', 'icon' => 'check-circle', 'title' => 'Problème résolu', 'color' => 'emerald'],
                        ['value' => 'partial', 'icon' => 'alert-circle', 'title' => 'Résolution partielle', 'color' => 'amber'],
                        ['value' => 'failed', 'icon' => 'x-circle', 'title' => 'Non résolu', 'color' => 'rose'],
                        ['value' => 'followup', 'icon' => 'clock', 'title' => 'Nécessite suivi', 'color' => 'cyan'],
                    ] as $status)
                    <label class="status-card glass p-5 rounded-xl cursor-pointer hover:border-cyan-400/40 transition-all relative">
                        <input type="radio" name="status" value="{{ $status['value'] }}" class="peer hidden" required>
                        <div class="peer-checked:border-{{ $status['color'] }}-400 peer-checked:bg-{{ $status['color'] }}-500/5 absolute inset-0 border-2 border-transparent rounded-xl transition-all"></div>
                        <div class="relative flex items-center gap-3">
                            <i data-lucide="{{ $status['icon'] }}" class="w-6 h-6 text-{{ $status['color'] }}-400"></i>
                            <span class="font-semibold text-white">{{ $status['title'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </x-ui.card>

            <!-- Work Details -->
            <x-ui.card class="mb-6">
                <h3 class="text-lg font-display font-bold text-white mb-4">Détails de l'intervention</h3>
                
                <div class="space-y-4">
                    <x-forms.textarea 
                        name="cause" 
                        label="Cause du problème" 
                        rows="3"
                        placeholder="Décrivez la cause identifiée..."
                        required
                    />

                    <x-forms.textarea 
                        name="work_performed" 
                        label="Travaux effectués" 
                        rows="4"
                        placeholder="Décrivez les actions réalisées..."
                        required
                    />

                    <x-forms.textarea 
                        name="observations" 
                        label="Observations / Recommandations" 
                        rows="3"
                        placeholder="Ajoutez vos observations et recommandations..."
                    />
                </div>
            </x-ui.card>

            <!-- Parts Used -->
            <x-ui.card class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-display font-bold text-white">Pièces utilisées</h3>
                    <x-ui.button type="button" variant="outline" size="sm" icon="plus" onclick="addPart()">
                        Ajouter
                    </x-ui.button>
                </div>
                
                <div id="parts-list" class="space-y-3">
                    <div class="flex gap-3 part-row">
                        <x-forms.input 
                            name="parts[0][name]" 
                            placeholder="Nom de la pièce"
                            class="flex-1"
                        />
                        <x-forms.input 
                            name="parts[0][quantity]" 
                            type="number"
                            placeholder="Qté"
                            class="w-24"
                        />
                        <button type="button" onclick="removePart(this)" class="w-10 h-10 rounded-lg flex items-center justify-center hover:bg-rose-500/10 text-rose-400 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </x-ui.card>

            <!-- Duration -->
            <x-ui.card class="mb-6">
                <h3 class="text-lg font-display font-bold text-white mb-4">Durée</h3>
                
                <div class="grid sm:grid-cols-3 gap-4">
                    <x-forms.input 
                        name="duration_hours" 
                        type="number"
                        label="Heures"
                        placeholder="0"
                        min="0"
                    />
                    <x-forms.input 
                        name="duration_minutes" 
                        type="number"
                        label="Minutes"
                        placeholder="0"
                        min="0"
                        max="59"
                    />
                    <div class="flex items-end">
                        <div class="glass p-4 rounded-xl w-full text-center">
                            <div class="text-xs text-cyan-100/50 mb-1">Durée estimée</div>
                            <div class="text-lg font-display font-bold text-white">{{ $intervention['estimated_duration'] }}</div>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Photos -->
            <x-ui.card class="mb-6">
                <h3 class="text-lg font-display font-bold text-white mb-4">Photos</h3>
                
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Photo avant</label>
                        <div class="glass p-6 rounded-xl border-2 border-dashed border-cyan-400/20 hover:border-cyan-400/40 transition-colors">
                            <input type="file" name="photo_before" accept="image/*" class="hidden" id="photo_before">
                            <label for="photo_before" class="cursor-pointer flex flex-col items-center">
                                <i data-lucide="camera" class="w-8 h-8 text-cyan-400 mb-2"></i>
                                <span class="text-sm text-white font-medium">Avant intervention</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Photo après</label>
                        <div class="glass p-6 rounded-xl border-2 border-dashed border-cyan-400/20 hover:border-cyan-400/40 transition-colors">
                            <input type="file" name="photo_after" accept="image/*" class="hidden" id="photo_after">
                            <label for="photo_after" class="cursor-pointer flex flex-col items-center">
                                <i data-lucide="camera" class="w-8 h-8 text-cyan-400 mb-2"></i>
                                <span class="text-sm text-white font-medium">Après intervention</span>
                            </label>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <!-- Actions -->
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('technician.interventions.show', ['id' => $intervention['id']]) }}">
                    <x-ui.button type="button" variant="outline">
                        Annuler
                    </x-ui.button>
                </a>
                <x-ui.button type="submit" icon="check">
                    Valider le rapport
                </x-ui.button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let partIndex = 1;

function addPart() {
    const partsList = document.getElementById('parts-list');
    const newPart = document.createElement('div');
    newPart.className = 'flex gap-3 part-row';
    newPart.innerHTML = `
        <input 
            name="parts[${partIndex}][name]" 
            placeholder="Nom de la pièce"
            class="flex-1 bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
        />
        <input 
            name="parts[${partIndex}][quantity]" 
            type="number"
            placeholder="Qté"
            class="w-24 bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
        />
        <button type="button" onclick="removePart(this)" class="w-10 h-10 rounded-lg flex items-center justify-center hover:bg-rose-500/10 text-rose-400 transition-colors">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
    `;
    partsList.appendChild(newPart);
    partIndex++;
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function removePart(button) {
    const partRow = button.closest('.part-row');
    if (document.querySelectorAll('.part-row').length > 1) {
        partRow.remove();
    }
}
</script>
@endpush
@endsection
