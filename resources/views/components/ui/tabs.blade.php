@props([
    'id' => 'tabs-' . uniqid(),
    'defaultTab' => null,
])

<div {{ $attributes->merge(['class' => 'tabs-container']) }} data-tabs-id="{{ $id }}">
    {{ $slot }}
</div>

@once
@push('scripts')
<script>
function switchTab(tabId) {
    const container = document.querySelector(`[data-tabs-id]`);
    if (!container) return;
    
    // Update tab buttons
    const buttons = container.querySelectorAll('.tab-btn');
    buttons.forEach(btn => {
        const isActive = btn.dataset.tab === tabId;
        btn.classList.toggle('border-cyan-400', isActive);
        btn.classList.toggle('text-white', isActive);
        btn.classList.toggle('border-transparent', !isActive);
        btn.classList.toggle('text-cyan-100/50', !isActive);
    });
    
    // Update tab contents
    const contents = container.querySelectorAll('.tab-content');
    contents.forEach(content => {
        content.classList.toggle('hidden', content.id !== `tab-${tabId}`);
    });
}

// Initialize tabs on page load
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.tabs-container');
    containers.forEach(container => {
        const firstBtn = container.querySelector('.tab-btn');
        if (firstBtn && firstBtn.dataset.tab) {
            switchTab(firstBtn.dataset.tab);
        }
    });
});
</script>
@endpush
@endonce
