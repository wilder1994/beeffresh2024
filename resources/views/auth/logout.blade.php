@extends('layouts.guest')

@section('contenido')
<div class="text-center space-y-4">
    <p class="text-sm text-stone-600">Cerrando sesión…</p>
    <form id="logout-form" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="bf-btn-primary w-full justify-center">Continuar</button>
    </form>
</div>
<script>
    document.getElementById('logout-form')?.requestSubmit();
</script>
@endsection
