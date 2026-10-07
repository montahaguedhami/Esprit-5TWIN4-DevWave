@if(session('success'))
    <x-ui.alert type="success" class="mb-6">{{ session('success') }}</x-ui.alert>
@endif

@if(session('error'))
    <x-ui.alert type="error" class="mb-6">{{ session('error') }}</x-ui.alert>
@endif
