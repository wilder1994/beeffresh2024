<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="text-xl font-semibold text-[var(--bf-ink)]">Crear cuenta de cliente</h1>
        <p class="text-sm text-[var(--bf-muted)] leading-relaxed">
            Este formulario es solo para <strong>compradores</strong>. Incluye datos personales, acceso y domicilio de entrega. Personal interno y proveedores reciben acceso por el administrador.
        </p>
    </div>

    @include('auth.partials.register-form', ['inModal' => false])
</x-guest-layout>
