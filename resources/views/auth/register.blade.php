<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="text-xl font-semibold text-[var(--bf-ink)]">Crear cuenta de cliente</h1>
        <p class="text-sm text-[var(--bf-muted)] leading-relaxed">
            Este formulario es solo para <strong>compradores</strong>. Personal interno y proveedores reciben acceso por el administrador.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nombre -->
        <div>
            <x-input-label for="name" :value="'Nombre completo'" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Correo electrónico -->
        <div class="mt-4">
            <x-input-label for="email" :value="'Correo electrónico'" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="'Contraseña'" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Contraseña -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="'Confirmar contraseña'" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Enlace + Botón -->
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-[var(--bf-muted)] hover:text-[var(--bf-rust)] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--bf-crimson)]/40" href="{{ route('login') }}">
                ¿Ya tienes una cuenta?
            </a>

            <x-primary-button class="ms-4">
                Registrarse
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
