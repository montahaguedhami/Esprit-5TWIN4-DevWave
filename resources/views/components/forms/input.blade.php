@props([
    'type' => 'text',
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($label)
    <label for="{{ $name }}" class="text-xs font-semibold text-cyan-100/70 mb-2 block">
        {{ $label }}
        @if($required)
        <span class="text-rose-400">*</span>
        @endif
    </label>
    @endif
    
    <div class="relative">
        @if($icon)
        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <i data-lucide="{{ $icon }}" class="w-5 h-5 text-cyan-100/40"></i>
        </div>
        @endif
        
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required aria-required="true" @endif
            @if($disabled) disabled @endif
            @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            @if($hint && !$error) aria-describedby="{{ $name }}-hint" @endif
            class="w-full bg-slate-950/35 border rounded-xl px-4 py-3 text-sm text-white placeholder:text-cyan-100/30 focus:outline-none transition-colors {{ $icon ? 'pl-11' : '' }} {{ $error ? 'border-rose-400/50 focus:border-rose-400' : 'border-cyan-400/15 focus:border-cyan-400/50' }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
        />
    </div>
    
    @if($error)
    <p id="{{ $name }}-error" class="text-xs text-rose-400 mt-1.5 flex items-center gap-1" role="alert">
        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
        {{ $error }}
    </p>
    @elseif($hint)
    <p id="{{ $name }}-hint" class="text-xs text-cyan-100/50 mt-1.5">{{ $hint }}</p>
    @endif
</div>
