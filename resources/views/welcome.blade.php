@extends('layouts.app')

@section('titulo', 'Página principal')

@section('contenido')
<div
class="hero min-h-screen"
style="background-image: url(https://img.daisyui.com/images/stock/photo-1507358522600-9f71e620c44e.webp);">
<div class="hero-overlay bg-opacity-60"></div>
<div class="hero-content text-neutral-content text-center">
  <div class="max-w-md">
    <h1 class="mb-5 text-5xl font-bold">Bienvenido a BEEF FRESH</h1>
    <p class="mb-5">
      Calidad hasta la puerta de tu casa.
    {{--</p>
    <button class="btn btn-primary">Get Started</button>--}}
  </div>
</div>
</div>
@endsection