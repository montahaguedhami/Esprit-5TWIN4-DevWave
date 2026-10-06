@extends('layouts.frontoffice')

@section('title', 'Signaler un problème')

@php
    use App\Data\PlaceholderData;
    $zones = PlaceholderData::zones();
@endphp

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('citizen.dashboard') }}" class="w-10 h-10 rounded-xl glass flex items-center justify-center hover:border-cyan-400/40 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 text-cyan-300"></i>
        </a>
        <div>
            <h1 class="text-3xl font-display font-bold text-white">Signaler un problème</h1>
            <p class="text-cyan-100/60 text-sm mt-1">Aidez-nous à maintenir la qualité du service</p>
        </div>
    </div>

    <!-- Multi-step Form -->
    <div class="max-w-5xl mx-auto">
        <!-- Progress Steps -->
        <div class="glass-strong p-6 rounded-2xl mb-6">
            <div class="flex items-center justify-between">
                @foreach([
                    ['num' => 1, 'label' => 'Type'],
                    ['num' => 2, 'label' => 'Localisation'],
                    ['num' => 3, 'label' => 'Détails'],
                    ['num' => 4, 'label' => 'Confirmation'],
                ] as $index => $step)
                <div class="flex items-center {{ $index < 3 ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all {{ $index === 0 ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white' : 'bg-slate-800/50 text-cyan-100/40' }}" data-step="{{ $step['num'] }}">
                            {{ $step['num'] }}
                        </div>
                        <span class="text-xs text-cyan-100/60 mt-2 hidden sm:block">{{ $step['label'] }}</span>
                    </div>
                    @if($index < 3)
                    <div class="step-line flex-1 h-0.5 mx-3 {{ $index === 0 ? 'bg-slate-800/50' : 'bg-slate-800/50' }}"></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Form Steps -->
        <form id="reportForm" method="POST" action="{{ route('citizen.reports.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Problem Type -->
            <div class="form-step" data-step="1">
                <x-ui.card>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Quel type de problème signalez-vous ?</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach([
                            ['type' => 'leak', 'icon' => 'droplet', 'title' => 'Fuite d\'eau', 'desc' => 'Fuite visible sur la voie publique'],
                            ['type' => 'quality', 'icon' => 'flask', 'title' => 'Qualité de l\'eau', 'desc' => 'Eau trouble, odeur ou goût inhabituel'],
                            ['type' => 'pressure', 'icon' => 'gauge', 'title' => 'Pression insuffisante', 'desc' => 'Débit faible ou irrégulier'],
                            ['type' => 'outage', 'icon' => 'power-off', 'title' => 'Coupure d\'eau', 'desc' => 'Absence totale d\'eau'],
                            ['type' => 'noise', 'icon' => 'volume-2', 'title' => 'Bruit anormal', 'desc' => 'Sifflement ou vibration dans les tuyaux'],
                            ['type' => 'other', 'icon' => 'help-circle', 'title' => 'Autre', 'desc' => 'Un problème non listé ci-dessus'],
                        ] as $problem)
                        <label class="problem-type-card glass p-5 rounded-xl cursor-pointer hover:border-cyan-400/40 transition-all relative">
                            <input type="radio" name="type" value="{{ $problem['type'] }}" class="peer hidden" required>
                            <div class="peer-checked:border-cyan-400 peer-checked:bg-cyan-500/5 absolute inset-0 border-2 border-transparent rounded-xl transition-all"></div>
                            <div class="relative">
                                <i data-lucide="{{ $problem['icon'] }}" class="w-8 h-8 text-cyan-400 mb-3"></i>
                                <h4 class="font-semibold text-white mb-1">{{ $problem['title'] }}</h4>
                                <p class="text-xs text-cyan-100/60">{{ $problem['desc'] }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <div class="flex justify-end mt-6">
                        <x-ui.button onclick="nextStep()" type="button" icon-right="arrow-right">
                            Continuer
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>

            <!-- Step 2: Location -->
            <div class="form-step hidden" data-step="2">
                <x-ui.card>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Où se situe le problème ?</h3>
                    
                    <div class="space-y-4">
                        <x-forms.select name="zone" label="Zone" required>
                            @foreach($zones as $zone)
                            <option value="{{ $zone['id'] }}">{{ $zone['emoji'] }} {{ $zone['name'] }}</option>
                            @endforeach
                        </x-forms.select>

                        <x-forms.input 
                            name="address" 
                            label="Adresse complète" 
                            icon="map-pin"
                            placeholder="Ex: 42 Rue Habib Bourguiba"
                            required
                        />

                        <div>
                            <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">Point de repère (optionnel)</label>
                            <input 
                                type="text" 
                                name="landmark" 
                                placeholder="Ex: À côté de la pharmacie centrale"
                                class="w-full bg-slate-950/35 border border-cyan-400/15 rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none focus:border-cyan-400/50 transition-colors"
                            />
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <x-ui.button onclick="previousStep()" type="button" variant="outline" icon="arrow-left">
                            Retour
                        </x-ui.button>
                        <x-ui.button onclick="nextStep()" type="button" icon-right="arrow-right">
                            Continuer
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>

            <!-- Step 3: Details -->
            <div class="form-step hidden" data-step="3">
                <x-ui.card>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Décrivez le problème en détail</h3>
                    
                    <div class="space-y-4">
                        <x-forms.textarea 
                            name="description" 
                            label="Description" 
                            rows="5"
                            maxlength="500"
                            placeholder="Décrivez le problème observé, quand l'avez-vous remarqué, quelle est sa gravité..."
                            required
                        />

                        <div>
                            <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                                Niveau d'urgence
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach([
                                    ['value' => 'low', 'label' => 'Faible', 'color' => 'emerald'],
                                    ['value' => 'medium', 'label' => 'Moyen', 'color' => 'amber'],
                                    ['value' => 'high', 'label' => 'Urgent', 'color' => 'rose'],
                                ] as $priority)
                                <label class="priority-card glass p-4 rounded-xl cursor-pointer hover:border-cyan-400/40 transition-all relative text-center">
                                    <input type="radio" name="priority" value="{{ $priority['value'] }}" class="peer hidden" required>
                                    <div class="peer-checked:border-{{ $priority['color'] }}-400 peer-checked:bg-{{ $priority['color'] }}-500/5 absolute inset-0 border-2 border-transparent rounded-xl transition-all"></div>
                                    <div class="relative">
                                        <span class="block font-semibold text-white">{{ $priority['label'] }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-cyan-100/70 mb-2 block">
                                Photos (optionnel)
                            </label>
                            <div class="glass p-6 rounded-xl border-2 border-dashed border-cyan-400/20 hover:border-cyan-400/40 transition-colors">
                                <input type="file" name="photos[]" id="photos" multiple accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                                <label for="photos" class="cursor-pointer flex flex-col items-center">
                                    <i data-lucide="upload" class="w-10 h-10 text-cyan-400 mb-3"></i>
                                    <span class="text-sm text-white font-medium mb-1">Cliquez pour ajouter des photos</span>
                                    <span class="text-xs text-cyan-100/50">PNG, JPG jusqu'à 5MB chacune</span>
                                </label>
                            </div>
                            <div id="photo-preview" class="grid grid-cols-3 gap-3 mt-3"></div>
                        </div>
                    </div>

                    <div class="flex justify-between mt-6">
                        <x-ui.button onclick="previousStep()" type="button" variant="outline" icon="arrow-left">
                            Retour
                        </x-ui.button>
                        <x-ui.button onclick="nextStep()" type="button" icon-right="arrow-right">
                            Continuer
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>

            <!-- Step 4: Confirmation -->
            <div class="form-step hidden" data-step="4">
                <x-ui.card>
                    <h3 class="text-xl font-display font-bold text-white mb-4">Vérifiez votre déclaration</h3>
                    
                    <div class="space-y-4" id="summary">
                        <!-- Will be filled by JavaScript -->
                    </div>

                    <div class="flex items-start gap-3 p-4 rounded-xl bg-cyan-500/5 border border-cyan-400/10 mt-6">
                        <input 
                            type="checkbox" 
                            name="confirm" 
                            id="confirm" 
                            required
                            class="mt-0.5 w-4 h-4 rounded border-cyan-400/30 bg-slate-950/50 text-cyan-500 focus:ring-2 focus:ring-cyan-400/50"
                        />
                        <label for="confirm" class="text-xs text-cyan-100/70 leading-relaxed">
                            Je certifie que les informations fournies sont exactes et que ce signalement concerne un problème réel.
                        </label>
                    </div>

                    <div class="flex justify-between mt-6">
                        <x-ui.button onclick="previousStep()" type="button" variant="outline" icon="arrow-left">
                            Retour
                        </x-ui.button>
                        <x-ui.button type="submit" icon="send">
                            Envoyer la déclaration
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentStep = 1;
const formData = {};

function nextStep() {
    const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
    const inputs = currentStepEl.querySelectorAll('input[required], select[required], textarea[required]');
    
    // Validate current step
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value || (input.type === 'radio' && !document.querySelector(`input[name="${input.name}"]:checked`))) {
            isValid = false;
            input.reportValidity();
        }
    });
    
    if (!isValid) return;
    
    // Save current step data
    inputs.forEach(input => {
        if (input.type === 'radio' && input.checked) {
            formData[input.name] = input.value;
        } else if (input.type !== 'radio') {
            formData[input.name] = input.value;
        }
    });
    
    // Update step 4 summary
    if (currentStep === 3) {
        updateSummary();
    }
    
    // Move to next step
    if (currentStep < 4) {
        currentStepEl.classList.add('hidden');
        currentStep++;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('hidden');
        updateProgressUI();
    }
}

function previousStep() {
    if (currentStep > 1) {
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('hidden');
        currentStep--;
        document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('hidden');
        updateProgressUI();
    }
}

function updateProgressUI() {
    document.querySelectorAll('.step-circle').forEach((circle, index) => {
        if (index + 1 < currentStep) {
            circle.className = 'step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all bg-emerald-500 text-white';
            circle.innerHTML = '<i data-lucide="check" class="w-5 h-5"></i>';
        } else if (index + 1 === currentStep) {
            circle.className = 'step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all bg-gradient-to-r from-cyan-500 to-blue-600 text-white';
            circle.textContent = index + 1;
        } else {
            circle.className = 'step-circle w-10 h-10 rounded-full flex items-center justify-center font-semibold transition-all bg-slate-800/50 text-cyan-100/40';
            circle.textContent = index + 1;
        }
    });
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updateSummary() {
    const typeLabels = {
        'leak': 'Fuite d\'eau',
        'quality': 'Qualité de l\'eau',
        'pressure': 'Pression insuffisante',
        'outage': 'Coupure d\'eau',
        'noise': 'Bruit anormal',
        'other': 'Autre'
    };
    
    const priorityLabels = {
        'low': 'Faible',
        'medium': 'Moyen',
        'high': 'Urgent'
    };
    
    const zoneName = document.querySelector('select[name="zone"] option:checked').textContent;
    
    const summary = `
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="alert-circle" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Type de problème</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${typeLabels[formData.type] || formData.type}</p>
        </div>
        
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="map-pin" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Localisation</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${formData.address}</p>
            <p class="text-xs text-cyan-100/50 mt-1">${zoneName}</p>
        </div>
        
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="file-text" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Description</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${formData.description}</p>
        </div>
        
        <div class="glass p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-cyan-400"></i>
                <h4 class="font-semibold text-white">Urgence</h4>
            </div>
            <p class="text-sm text-cyan-100/70">${priorityLabels[formData.priority] || formData.priority}</p>
        </div>
    `;
    
    document.getElementById('summary').innerHTML = summary;
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function handleFileSelect(event) {
    const files = event.target.files;
    const preview = document.getElementById('photo-preview');
    preview.innerHTML = '';
    
    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative glass rounded-lg overflow-hidden aspect-square';
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                <button type="button" onclick="removePhoto(${index})" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-rose-500 flex items-center justify-center hover:bg-rose-600 transition-colors">
                    <i data-lucide="x" class="w-4 h-4 text-white"></i>
                </button>
            `;
            preview.appendChild(div);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };
        reader.readAsDataURL(file);
    });
}

// Form submission
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Simulate success
    showToast('Déclaration envoyée avec succès ! Vous recevrez une confirmation par email.', 'success');
    
    setTimeout(() => {
        window.location.href = '{{ route('citizen.dashboard') }}';
    }, 2000);
});
</script>
@endpush
@endsection
