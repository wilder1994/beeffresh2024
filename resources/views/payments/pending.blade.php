@extends('layouts.store')

@section('titulo', 'Pago pendiente | BEEF FRESH')

@section('content')
<div class="bf-store-page bf-store-page--checkout max-w-lg mx-auto text-center" data-payment-status data-status-url="{{ route('payments.status', $payment->uuid) }}">
    <div class="bf-payment-result bf-store-panel p-8">
        <div class="bf-payment-loader mx-auto mb-4"></div>
        <h1 class="font-brand text-2xl text-[var(--bf-ink)] mb-2">Confirmando pago…</h1>
        <p class="text-sm text-[var(--bf-muted)] mb-4">Estamos esperando la confirmación de la entidad financiera. Esto puede tardar unos segundos.</p>
        <x-payment.status-badge :status="$payment->status" class="mb-4" />
        <p class="text-xs text-[var(--bf-muted)]">Referencia: {{ $payment->reference }}</p>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/paymentStatus.js')
@endpush
