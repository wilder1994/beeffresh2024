@php
    use App\Support\AuthLoginAudience;
    $audience = $audience ?? AuthLoginAudience::CLIENT;
    $openRegisterConfirm = request('registro') === 'confirm';
    $openRegisterModal = $errors->hasAny(\App\Http\Requests\Auth\RegisterCustomerRequest::FIELD_KEYS);
@endphp

<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="text-xl font-semibold text-[var(--bf-ink)]">{{ AuthLoginAudience::heading($audience) }}</h1>
        <p class="text-sm text-[var(--bf-muted)] leading-relaxed">
            {{ AuthLoginAudience::description($audience) }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="tipo" value="{{ $audience }}">

        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[var(--bf-crimson)] shadow-sm focus:ring-[var(--bf-crimson)]" name="remember">
                <span class="ms-2 text-sm text-[var(--bf-muted)]">{{ __('Recordar') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-[var(--bf-muted)] hover:text-[var(--bf-rust)] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--bf-crimson)]/40" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Iniciar sesión') }}
            </x-primary-button>
        </div>
    </form>

    @if(AuthLoginAudience::allowsSelfRegistration($audience))
        <p class="mt-6 text-center text-sm text-[var(--bf-muted)]">
            ¿No tienes cuenta de cliente?
            <button
                type="button"
                class="font-medium text-[var(--bf-brand)] hover:underline"
                x-on:click.prevent="window.bfOpenRegisterConfirm && window.bfOpenRegisterConfirm()"
                onclick="event.preventDefault(); window.bfOpenRegisterConfirm && window.bfOpenRegisterConfirm();"
            >
                Regístrate aquí
            </button>
        </p>

        <x-auth.register-modals
            :open-confirm="$openRegisterConfirm"
            :open-register="$openRegisterModal"
        />
    @endif
</x-guest-layout>
