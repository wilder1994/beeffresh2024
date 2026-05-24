@extends('layouts.store')

@section('titulo', 'Procesando pago | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--checkout max-w-lg mx-auto" data-wompi-checkout>
    <h1 class="font-brand text-2xl text-[var(--bf-ink)] text-center mb-2">Completa tu pago</h1>
    <p class="text-sm text-[var(--bf-muted)] text-center mb-6">Referencia <span class="font-mono text-xs">{{ $payment->reference }}</span> · ${{ number_format((float) $payment->amount, 0, ',', '.') }} COP</p>

    <div class="bf-store-panel p-6 text-center space-y-4">
        <div class="bf-payment-loader mx-auto" aria-hidden="true"></div>
        <p class="text-sm text-[var(--bf-muted)]">Abriendo pasarela segura…</p>
        <button type="button" id="wompi-open-btn" class="bf-btn-primary w-full justify-center">Pagar ahora</button>
        <p class="text-xs text-[var(--bf-muted)]">Si no se abre automáticamente, pulsa «Pagar ahora».</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ $widget->widgetScriptUrl }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const config = @json($widget->widgetConfig);
    const open = () => {
        if (typeof WidgetCheckout === 'undefined') return;
        const checkout = new WidgetCheckout(config);
        checkout.open(function () {});
    };
    document.getElementById('wompi-open-btn')?.addEventListener('click', open);
    setTimeout(open, 400);
});
</script>
@endpush
