@extends('layouts.app')

@section('content')
<div id="public-shell" style="min-height:100vh;">
    @yield('public-content')
    <x-ui.toast />
</div>
@endsection
