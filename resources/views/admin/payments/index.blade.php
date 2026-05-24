@extends('layouts.app')

@section('titulo', 'Pagos')
@section('cabecera', 'Centro financiero')

@section('contenido')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['label' => 'Aprobados hoy', 'value' => $metrics['approved_today'], 'tone' => 'success'],
            ['label' => 'Pendientes', 'value' => $metrics['pending'], 'tone' => 'warn'],
            ['label' => 'Fallidos hoy', 'value' => $metrics['failed_today'], 'tone' => 'danger'],
            ['label' => 'Ingresos hoy', 'value' => '$'.number_format($metrics['volume_today'], 0, ',', '.'), 'tone' => 'info'],
        ] as $m)
            <div class="bf-ops-metric bf-ops-metric--{{ $m['tone'] }}">
                <span class="bf-ops-metric__value">{{ $m['value'] }}</span>
                <span class="bf-ops-metric__label">{{ $m['label'] }}</span>
            </div>
        @endforeach
    </div>

    <form method="GET" class="flex flex-wrap gap-2">
        <input type="search" name="search" value="{{ $search }}" placeholder="Referencia, transacción, email…" class="bf-input flex-1 min-w-[12rem]">
        <select name="status" class="bf-select">
            <option value="">Todos los estados</option>
            @foreach(\App\Enums\PaymentStatus::cases() as $st)
                <option value="{{ $st->value }}" @selected($status === $st->value)>{{ $st->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="bf-btn-primary">Filtrar</button>
    </form>

    <div class="bf-ops-panel overflow-x-auto">
        <table class="bf-table w-full text-sm">
            <thead>
                <tr>
                    <th>Referencia</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Gateway</th>
                    <th>Total</th>
                    <th>Pedido</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><a href="{{ route('admin.payments.show', $payment) }}" class="font-mono text-xs text-[var(--bf-brand)] hover:underline">{{ $payment->reference }}</a></td>
                        <td>{{ $payment->user?->name }}<br><span class="text-xs text-stone-500">{{ $payment->user?->email }}</span></td>
                        <td><x-payment.status-badge :status="$payment->status" /></td>
                        <td>{{ $payment->gateway->label() }}</td>
                        <td class="tabular-nums">${{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td>@if($payment->order_id)#{{ $payment->order_id }}@else—@endif</td>
                        <td class="text-xs text-stone-500">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-stone-500">Sin pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $payments->links() }}
</div>
@endsection
