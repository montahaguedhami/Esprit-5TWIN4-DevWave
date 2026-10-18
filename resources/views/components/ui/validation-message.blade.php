{{--
    Client-side validation message placeholder.
    Usage: <x-ui.validation-message for="field_name" />
    The JS validation engine targets the element by id: "{for}-client-error"
    and toggles its visibility + text content.
--}}
@props(['for'])

<p id="{{ $for }}-client-error"
   role="alert"
   aria-live="polite"
   style="
       display: none;
       margin-top: 4px;
       font-size: 12px;
       font-weight: 500;
       color: #f87171;
       align-items: center;
       gap: 5px;
   ">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex-shrink:0;color:#f87171">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <span class="{{ $for }}-error-text"></span>
</p>
