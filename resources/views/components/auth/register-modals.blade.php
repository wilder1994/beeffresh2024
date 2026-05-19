@props([
    'openConfirm' => false,
    'openRegister' => false,
])

<x-account.dialog name="register-client-confirm" maxWidth="md" zIndex="z-[200]" :show="$openConfirm">
    <div class="bg-white rounded-xl border border-stone-200 shadow-xl p-6 text-center">
        <h2 class="text-lg font-bold text-stone-900">Registro de cliente</h2>
        <p class="mt-3 text-sm text-stone-600 leading-relaxed">
            Te vas a registrar como <strong>cliente</strong> para comprar en la tienda en línea.
            Personal interno y proveedores reciben acceso por el administrador.
        </p>
        <div class="mt-6 flex flex-wrap gap-2 justify-center">
            <button
                type="button"
                class="bf-btn-ghost btn-sm"
                x-on:click="window.location.href = @js(route('home'))"
            >
                Cancelar
            </button>
            <button
                type="button"
                class="bf-btn-primary btn-sm"
                x-on:click="window.bfCloseRegisterConfirm && window.bfCloseRegisterConfirm(); window.bfOpenRegisterClient && window.bfOpenRegisterClient();"
                onclick="window.bfCloseRegisterConfirm && window.bfCloseRegisterConfirm(); window.bfOpenRegisterClient && window.bfOpenRegisterClient();"
            >
                Aceptar
            </button>
        </div>
    </div>
</x-account.dialog>

<x-account.dialog name="register-client" maxWidth="2xl" zIndex="z-[210]" :show="$openRegister">
    <div class="bg-white rounded-xl border border-stone-200 shadow-xl p-5 sm:p-6 overflow-y-auto max-h-[min(92vh,44rem)]">
        <header class="mb-4 pb-3 border-b border-stone-100">
            <h2 class="text-lg font-bold text-stone-900">Crear cuenta de cliente</h2>
            <p class="text-xs text-stone-500 mt-1">Completa tus datos personales, acceso y domicilio de entrega para comprar en la tienda.</p>
        </header>
        @include('auth.partials.register-form', ['inModal' => true])
    </div>
</x-account.dialog>
