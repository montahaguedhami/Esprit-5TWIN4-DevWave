@extends('layouts.auth')

@section('auth-content')
<div class="w-full max-w-md mx-auto my-auto">
    <div class="glass-strong p-8 rounded-2xl animate-fade-in-up">

        {{-- Icon --}}
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-cyan-500/15 border border-cyan-400/30 flex items-center justify-center">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#22d3ee" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
        </div>

        <h2 class="text-2xl font-display font-bold text-white text-center mb-2">Vérifiez votre email</h2>
        <p class="text-cyan-100/60 text-sm text-center mb-6">
            Merci pour votre inscription ! Avant de commencer, veuillez vérifier votre adresse email en cliquant sur le lien que nous venons de vous envoyer.
        </p>

        {{-- Status --}}
        @if (session('status') === 'verification-link-sent')
        <div class="mb-4 p-3 rounded-xl bg-teal-500/10 border border-teal-400/20">
            <p class="text-teal-300 text-sm text-center">
                ✅ Un nouveau lien de vérification a été envoyé à votre adresse email.
            </p>
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-400/20">
            @foreach ($errors->all() as $error)
            <p class="text-red-300 text-sm">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="space-y-3">
            {{-- Resend --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-semibold hover:shadow-lg hover:shadow-cyan-500/25 transition-all">
                    Renvoyer le lien de vérification
                </button>
            </form>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full py-3 rounded-xl bg-white/5 text-cyan-100/70 hover:text-white font-semibold transition-colors">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
