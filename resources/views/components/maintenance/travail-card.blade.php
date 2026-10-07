{{-- Carte d'une intervention telle que présentée aux citoyens (sans données personnelles du technicien) --}}
@props(['intervention'])

<a href="{{ route('citizen.travaux.show', $intervention) }}" {{ $attributes->merge(['class' => 'block glass rounded-2xl p-5 hover-lift transition-all group']) }}>
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-cyan-500/10 border border-cyan-400/20 flex flex-col items-center justify-center shrink-0">
                <span class="text-sm font-bold text-white leading-none">{{ $intervention->date->format('d') }}</span>
                <span class="text-[10px] uppercase text-cyan-300 leading-none mt-0.5">{{ $intervention->date->locale('fr')->translatedFormat('M') }}</span>
            </div>
            <div>
                <p class="text-xs text-cyan-100/50">Équipe</p>
                <p class="text-sm font-semibold text-white">{{ $intervention->technicien->specialite }}</p>
            </div>
        </div>
        <x-maintenance.badge :value="$intervention->statut" />
    </div>

    <p class="text-sm text-cyan-100/80 leading-relaxed">{{ Str::limit($intervention->description, 110) }}</p>

    <p class="text-xs text-cyan-300 mt-4 flex items-center gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
        Voir le détail <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
    </p>
</a>
