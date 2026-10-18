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
    <label for="{{ $name }}" class="text-xs font-semibold text-slate-700 mb-2 block">
        {{ $label }}
        @if($required)
        <span class="text-rose-400">*</span>
        @endif
    </label>
    @endif
    
    <div class="relative">
        @if($icon)
        <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <i data-lucide="{{ $icon }}" class="w-5 h-5 text-slate-400"></i>
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
            class="w-full bg-white border rounded-xl px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none transition-colors {{ $icon ? 'pl-11' : '' }} {{ $error ? 'border-rose-300 focus:border-rose-500' : 'border-slate-200 focus:border-cyan-600' }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"
        />
    </div>
    
    @if($error)
    <p id="{{ $name }}-error" class="text-xs text-rose-700 mt-1.5 flex items-center gap-1" role="alert">
        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
        {{ $error }}
    </p>
    @elseif($hint)
    <p id="{{ $name }}-hint" class="text-xs text-slate-500 mt-1.5">{{ $hint }}</p>
    @endif
</div>
