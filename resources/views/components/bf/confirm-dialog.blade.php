<div
    x-cloak
    x-show="$store.bfConfirm.open"
    x-transition.opacity
    class="bf-confirm-layer"
    role="dialog"
    aria-modal="true"
    aria-labelledby="bf-confirm-title"
    @keydown.escape.window="$store.bfConfirm.cancel()"
>
    <div class="bf-confirm-layer__backdrop" @click="$store.bfConfirm.cancel()"></div>
    <div
        class="bf-confirm-dialog bf-surface"
        x-show="$store.bfConfirm.open"
        x-transition
        @click.stop
    >
        <h2 id="bf-confirm-title" class="text-lg font-semibold text-gray-900" x-text="$store.bfConfirm.title"></h2>
        <p
            class="mt-2 text-sm text-gray-600 leading-relaxed"
            x-show="$store.bfConfirm.message"
            x-text="$store.bfConfirm.message"
        ></p>
        <div class="bf-form-actions mt-5 justify-end gap-2">
            <button type="button" class="bf-btn-ghost" @click="$store.bfConfirm.cancel()">Cancelar</button>
            <button type="button" class="bf-btn-primary bg-red-700 hover:brightness-110 border-red-800/30" @click="$store.bfConfirm.confirm()" x-text="$store.bfConfirm.confirmLabel"></button>
        </div>
    </div>
</div>
