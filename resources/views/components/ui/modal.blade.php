@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md', // sm, md, lg, xl, full
    'closeButton' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-7xl',
        default => 'max-w-lg',
    };
@endphp

<div 
    id="{{ $id }}"
    class="modal-overlay fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden"
    onclick="if(event.target === this) closeModal('{{ $id }}')"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    aria-hidden="true"
>
    <div 
        class="modal-content glass-strong rounded-2xl shadow-2xl w-full {{ $sizeClasses }} max-h-[90vh] overflow-hidden flex flex-col animate-scale-in"
        onclick="event.stopPropagation()"
    >
        <!-- Header -->
        @if($title || $closeButton)
        <div class="flex items-center justify-between p-6 border-b border-white/5">
            <h3 id="{{ $id }}-title" class="text-xl font-display font-bold text-white">{{ $title }}</h3>
            @if($closeButton)
            <button 
                onclick="closeModal('{{ $id }}')"
                class="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-white/5 transition-colors"
                aria-label="Fermer le modal"
                type="button"
            >
                <i data-lucide="x" class="w-5 h-5 text-cyan-100/60"></i>
            </button>
            @endif
        </div>
        @endif

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </div>

        <!-- Footer (optional) -->
        @isset($footer)
        <div class="p-6 border-t border-white/5 flex items-center justify-end gap-3">
            {{ $footer }}
        </div>
        @endisset
    </div>
</div>

@once
@push('scripts')
<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        // Focus trap
        const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal-overlay:not(.hidden)');
        openModals.forEach(modal => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }
});
</script>
@endpush
@endonce
