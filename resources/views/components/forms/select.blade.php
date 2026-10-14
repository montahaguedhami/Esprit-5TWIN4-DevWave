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
        <label for="{{ $name }}" class="text-xs font-semibold text-slate-700 mb-2 block">
            {{ $label }}
            @if($required)
                <span class="text-rose-400">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <select name="{{ $name }}" id="{{ $name }}" @if($required) required @endif @if($disabled) disabled @endif
            class="w-full bg-white border rounded-xl px-4 py-3 text-sm text-slate-800 focus:outline-none transition-colors appearance-none cursor-pointer {{ $error ? 'border-rose-300 focus:border-rose-500' : 'border-slate-200 focus:border-cyan-600' }} {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            {{ $slot }}
        </select>

        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
            <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400"></i>
        </div>
    </div>

    @if($error)
        <p class="text-xs text-rose-700 mt-1.5 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
            {{ $error }}
        </p>
    @elseif($hint)
        <p class="text-xs text-slate-500 mt-1.5">{{ $hint }}</p>
    @endif
</div>