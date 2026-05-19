@props([
    'variant' => 'store',
])

@php
    use App\Support\AuthLoginAudience;
    $routes = AuthLoginAudience::loginRouteOptions();
@endphp

<details {{ $attributes->merge(['class' => 'relative']) }}>
    <summary @class([
        'list-none [&::-webkit-details-marker]:hidden cursor-pointer',
        'btn btn-sm bg-white/10 hover:bg-white/20 border border-white/40 text-white' => $variant === 'store',
        'btn btn-outline btn-sm bg-white/95 text-[var(--bf-rust)] border-0 hover:bg-[var(--bf-cream)]' => $variant === 'panel',
    ])>
        Ingresar
    </summary>
    <ul @class([
        'menu menu-sm absolute right-0 top-full z-[100] mt-2 w-52 rounded-box border p-2 shadow-lg',
        'border-black/10 bg-white text-gray-900' => true,
    ])>
        <li>
            <a href="{{ $routes[AuthLoginAudience::CLIENT] }}" class="rounded-lg font-medium">
                {{ AuthLoginAudience::label(AuthLoginAudience::CLIENT) }}
            </a>
        </li>
        <li>
            <a href="{{ $routes[AuthLoginAudience::EMPLOYEE] }}" class="rounded-lg font-medium">
                {{ AuthLoginAudience::label(AuthLoginAudience::EMPLOYEE) }}
            </a>
        </li>
        <li>
            <a href="{{ $routes[AuthLoginAudience::SUPPLIER] }}" class="rounded-lg font-medium">
                {{ AuthLoginAudience::label(AuthLoginAudience::SUPPLIER) }}
            </a>
        </li>
    </ul>
</details>
