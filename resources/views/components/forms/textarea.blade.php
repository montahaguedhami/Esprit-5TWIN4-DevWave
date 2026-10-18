@props([
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'rows' => 4,
    'maxlength' => null,
])

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label)
    <label for="{{ $name }}" class="text-xs font-semibold text-slate-700 mb-2 block">
        {{ $label }}
        @if($required)
        <span class="text-rose-400">*</span>
        @endif
    </label>
    @endif
    
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        class="w-full bg-white border rounded-xl px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none transition-colors resize-y {{ $error ? 'border-rose-300 focus:border-rose-500' : 'border-slate-200 focus:border-cyan-600' }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
    >{{ old($name, $value) }}</textarea>
    
    <div class="flex items-center justify-between mt-1.5">
        <div>
            @if($error)
            <p class="text-xs text-rose-700 flex items-center gap-1">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                {{ $error }}
            </p>
            @elseif($hint)
            <p class="text-xs text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        
        @if($maxlength)
        <p class="text-xs text-slate-400">
            <span id="{{ $name }}_counter">0</span>/{{ $maxlength }}
        </p>
        @endif
    </div>
</div>

@if($maxlength)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('{{ $name }}');
    const counter = document.getElementById('{{ $name }}_counter');
    
    if (textarea && counter) {
        const updateCounter = () => {
            counter.textContent = textarea.value.length;
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});
</script>
@endpush
@endif
