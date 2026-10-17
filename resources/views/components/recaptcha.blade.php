@props([
    /** The reCAPTCHA action name. v3 only — v2 has no action. */
    'action' => 'default',
    /** CSS selector of the form this widget belongs to. */
    'form' => null,
])

@php
    /** @var \App\Services\RecaptchaVerifier $verifier */
    $verifier = app(\App\Services\RecaptchaVerifier::class);

    $isV2   = $verifier->isV2();
    $siteKey = $verifier->siteKey();

    // Unique per instance so several forms can coexist on one page.
    $widgetId = 'aqua-rc-' . substr(md5($action . '-' . uniqid('', true)), 0, 8);
@endphp

{{--
    reCAPTCHA widget + token attachment.

    v2 renders the visible "Je ne suis pas un robot" checkbox and must live
    INSIDE the <form> so the response field it creates is actually submitted.
    v3 stays invisible and fetches a token right before submission.

    Behaviour is identical from the server's point of view: both end up posting
    a `g-recaptcha-response` field, which RecaptchaVerifier::verify() checks.
--}}
@if ($verifier->enabled())
    @if ($isV2)
        {{-- Visible checkbox — v2 --}}
        <div class="aqua-rc-v2" id="{{ $widgetId }}-host">
            <div id="{{ $widgetId }}"></div>
        </div>

        <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>

        <script>
            (function () {
                const HOST_ID    = @json($widgetId . '-host');
                const WIDGET_ID  = @json($widgetId);
                const SITE_KEY   = @json($siteKey);
                const FORM       = @json($form);

                const showMessage = function (text) {
                    const host = document.getElementById(HOST_ID);
                    if (!host) return;
                    let box = host.querySelector('.aqua-rc-v2__error');
                    if (!box) {
                        box = document.createElement('p');
                        box.className = 'aqua-rc-v2__error';
                        box.setAttribute('role', 'alert');
                        host.appendChild(box);
                    }
                    box.textContent = text;
                };

                const clearMessage = function () {
                    const host = document.getElementById(HOST_ID);
                    if (!host) return;
                    const box = host.querySelector('.aqua-rc-v2__error');
                    if (box) box.remove();
                };

                // The api.js loader is async: poll until grecaptcha is usable.
                const renderWidget = function () {
                    if (typeof grecaptcha === 'undefined' || typeof grecaptcha.render !== 'function') {
                        window.setTimeout(renderWidget, 80);
                        return;
                    }
                    if (window.__aquaRcRendered) return;
                    window.__aquaRcRendered = true;

                    window.__aquaRcWidgetId = grecaptcha.render(WIDGET_ID, {
                        sitekey: SITE_KEY,
                        size: 'normal',
                        callback: clearMessage
                    });
                };

                window.aquaAttachRecaptcha = function (formSelector) {
                    const form = document.querySelector(formSelector || FORM);
                    if (!form) return;

                    form.addEventListener('submit', function (event) {
                        // An earlier handler already rejected this submission.
                        if (event.defaultPrevented) return;

                        if (typeof grecaptcha === 'undefined' || window.__aquaRcWidgetId === undefined) {
                            // Widget not ready — let the server answer clearly
                            // rather than silently sending an empty response.
                            return;
                        }

                        const token = grecaptcha.getResponse(window.__aquaRcWidgetId);

                        if (!token) {
                            event.preventDefault();
                            showMessage('Veuillez cocher « Je ne suis pas un robot ».');
                            return;
                        }

                        let input = form.querySelector('input[name="g-recaptcha-response"]');
                        if (!input) {
                            input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'g-recaptcha-response';
                            form.appendChild(input);
                        }
                        input.value = token;
                    }, true); // capture phase: run before the app's own submit handlers
                };

                if (document.readyState === 'complete') renderWidget();
                else window.addEventListener('load', renderWidget);
            })();
        </script>

        <style>
            /*
             * No vertical margin on purpose: the surrounding form lays its
             * children out with a flex `gap`, so adding margins here would
             * stack on top of it and break the rhythm shared by every field.
             */
            .aqua-rc-v2 {
                margin: 0;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.4rem;
            }

            /*
             * Google ships the widget with square corners on an opaque white
             * plate. Clipping it to a rounded frame makes it sit with the rest
             * of the form without touching the cross-origin contents.
             */
            .aqua-rc-v2 > div {
                border-radius: 0.55rem;
                overflow: hidden;
                line-height: 0;
                box-shadow: 0 1px 3px rgba(2, 12, 24, 0.18);
            }

            .aqua-rc-v2__error {
                margin: 0;
                font-size: 0.72rem;
                font-weight: 600;
                color: #fb7185;
            }
        </style>
    @else
        {{-- Invisible — v3 --}}
        <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}" async defer></script>

        <script>
            (function () {
                const SITE_KEY = @json($siteKey);
                const ACTION   = @json($action);

                /**
                 * Fetch a fresh token right before the form is submitted.
                 * The token is bound to the action and expires quickly, so it
                 * is never reused across submissions.
                 */
                window.aquaAttachRecaptcha = function (formSelector) {
                    const form = document.querySelector(formSelector);
                    if (!form) return;

                    form.addEventListener('submit', function (event) {
                        // Another handler (e.g. client-side validation) already
                        // rejected this submission — do not override it.
                        if (event.defaultPrevented) {
                            return;
                        }

                        // Do not block a re-submission triggered by a validation
                        // error when the token is still being refreshed.
                        if (form.dataset.captchaBusy === '1') {
                            event.preventDefault();
                            return;
                        }

                        // Skip the round trip if a token is already attached and fresh.
                        if (form.dataset.captchaToken && form.dataset.captchaAction === ACTION) {
                            return;
                        }

                        event.preventDefault();
                        form.dataset.captchaBusy = '1';

                        if (typeof grecaptcha === 'undefined') {
                            // Script still loading — let the server decide.
                            form.dataset.captchaBusy = '0';
                            form.submit();
                            return;
                        }

                        grecaptcha.ready(function () {
                            grecaptcha.execute(SITE_KEY, { action: ACTION }).then(function (token) {
                                let input = form.querySelector('input[name="g-recaptcha-response"]');

                                if (!input) {
                                    input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = 'g-recaptcha-response';
                                    form.appendChild(input);
                                }

                                input.value = token;
                                form.dataset.captchaToken = token;
                                form.dataset.captchaAction = ACTION;
                                form.dataset.captchaBusy = '0';
                                form.submit();
                            }).catch(function () {
                                // On failure let the request through so the server
                                // returns a clear, translatable error.
                                form.dataset.captchaBusy = '0';
                                form.submit();
                            });
                        });
                    });
                };
            })();
        </script>
    @endif
@endif
