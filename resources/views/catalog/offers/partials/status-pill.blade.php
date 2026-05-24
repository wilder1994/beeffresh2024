@props([
    'active' => true,
])

@if($active)
    <span class="bf-catalog-pill bf-catalog-pill--active">Activo</span>
@else
    <span class="bf-catalog-pill bf-catalog-pill--inactive">Inactivo</span>
@endif
