@extends('layouts.app')

@section('content')
<div class="min-h-screen relative overflow-hidden">
    @yield('public-content')
    
    <!-- Toast Notifications -->
    <x-ui.toast />
</div>
@endsection
