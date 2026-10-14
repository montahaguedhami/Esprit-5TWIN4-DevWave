@props([
    'id' => 'dropdown-' . uniqid(),
    'align' => 'right', // left, right, center
    'width' => 'md', // sm, md, lg
])

@php
    $alignClasses = match($align) {
        'left' => 'left-0',
        'right' => 'right-0',
        'center' => 'left-1/2 -translate-x-1/2',
        default => 'right-0',
    };
    
    $widthClasses = match($width) {
        'sm' => 'w-48',
        'md' => 'w-56',
        'lg' => 'w-72',
        default => 'w-56',
    };
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <!-- Trigger -->
    <div @click="open = !open" class="cursor-pointer">
        {{ $trigger }}
    </div>
    
    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute {{ $alignClasses }} {{ $widthClasses }} mt-2 z-50 glass-strong rounded-xl shadow-2xl border border-cyan-400/10 py-2 hidden"
        :class="{ 'hidden': !open, 'block': open }"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>

@once
@push('scripts')
<script>
// Simple Alpine.js-like behavior without Alpine
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[x-data]').forEach(el => {
        const trigger = el.querySelector('[\\@click]');
        const dropdown = el.querySelector('[x-show]');
        
        if (trigger && dropdown) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isHidden = dropdown.style.display === 'none' || dropdown.style.display === '';
                
                // Close all other dropdowns
                document.querySelectorAll('[x-show]').forEach(d => {
                    if (d !== dropdown) d.style.display = 'none';
                });
                
                dropdown.style.display = isHidden ? 'block' : 'none';
            });
            
            // Click outside to close
            document.addEventListener('click', function(e) {
                if (!el.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
});
</script>
@endpush
@endonce
