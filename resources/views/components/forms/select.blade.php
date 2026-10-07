@props([
    'label' => null,
    'name' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'hint' => null,
    'placeholder' => 'Sélectionner...',
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
        <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif @if($disabled) disabled @endif
            class="w-full bg-slate-950/35 border rounded-xl px-4 py-3 text-sm text-white focus:outline-none transition-colors appearance-none cursor-pointer {{ $error ? 'border-rose-400/50 focus:border-rose-400' : 'border-cyan-400/15 focus:border-cyan-400/50' }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>

        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <i data-lucide="chevron-down" class="w-5 h-5 text-cyan-100/40"></i>
        </div>
    </div>

    @if($error)
        <p class="text-xs text-rose-400 mt-1.5 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="text-xs text-cyan-100/50 mt-1.5">{{ $hint }}</p>
    @endif
</div>