@if(session('success'))
    <div role="status" class="rounded-lg border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div role="alert" class="rounded-lg border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div role="alert" class="rounded-lg border border-rose-400/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-300">
        <p class="font-semibold">Vérifiez les informations saisies :</p>
        <ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
