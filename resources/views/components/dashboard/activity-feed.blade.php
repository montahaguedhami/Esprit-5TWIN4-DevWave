@props([
    'activities' => [],
    'maxItems' => 5,
])

<x-ui.card>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-display font-bold text-white">Activité récente</h3>
        <i data-lucide="activity" class="w-5 h-5 text-cyan-400"></i>
    </div>
    
    @if(empty($activities))
        <x-ui.empty-state 
            icon="inbox"
            title="Aucune activité"
            description="Les activités récentes apparaîtront ici"
        />
    @else
        <div class="space-y-4">
            @foreach(array_slice($activities, 0, $maxItems) as $activity)
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-lg bg-{{ $activity['color'] ?? 'cyan' }}-500/10 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="{{ $activity['icon'] ?? 'circle' }}" class="w-4 h-4 text-{{ $activity['color'] ?? 'cyan' }}-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-white">{{ $activity['title'] ?? '' }}</p>
                    <p class="text-xs text-cyan-100/50 mt-1">{{ $activity['time'] ?? '' }}</p>
                </div>
                @if(isset($activity['badge']))
                <x-ui.status-badge :status="$activity['badge']" size="sm" />
                @endif
            </div>
            @endforeach
        </div>
        
        @if(count($activities) > $maxItems)
        <div class="mt-4 pt-4 border-t border-white/5">
            <a href="#" class="text-sm text-cyan-400 hover:text-cyan-300 transition-colors flex items-center justify-center gap-1">
                Voir tout
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        @endif
    @endif
</x-ui.card>
