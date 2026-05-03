<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="text-xl font-semibold text-[var(--bf-ink)]">Iniciar sesión</h1>
        <p class="text-sm text-[var(--bf-muted)] leading-relaxed">
            Para <strong>clientes</strong> que compran en la tienda en línea. Si eres <strong>personal</strong> o <strong>proveedor</strong>, usa el correo y contraseña que te asignó la empresa.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
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

    <p class="mt-6 text-center text-sm text-[var(--bf-muted)]">
        ¿No tienes cuenta de cliente?
        <a href="{{ route('register') }}" class="font-medium text-[var(--bf-brand)] hover:underline">Regístrate aquí</a>
    </p>
</x-guest-layout>
