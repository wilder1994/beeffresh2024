@extends('layouts.app')

@section('titulo', 'Pedidos')
@section('cabecera', 'Pedidos en línea')

@section('contenido')
    <div class="max-w-7xl mx-auto -mt-1">
        <div class="overflow-x-auto bg-base-100 rounded-lg shadow border border-base-200/80">
            <table class="table table-zebra table-sm md:table-md text-xs md:text-sm">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">#</th>
                        <th>Cliente</th>
                        <th>Entrega</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th class="whitespace-nowrap">Fecha</th>
                        <th>Ítems</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->id }}</td>
                            <td>{{ $pedido->user->name }}<br><span class="text-sm opacity-70">{{ $pedido->user->email }}</span></td>
                            <td class="text-sm max-w-[14rem]">
                                @if($pedido->shipping_phone)
                                    <span class="font-medium">{{ $pedido->shipping_phone }}</span><br>
                                @endif
                                @if($pedido->shipping_city)
                                    {{ $pedido->shipping_city }}@if($pedido->shipping_state), {{ $pedido->shipping_state }}@endif
                                @else
                                    <span class="opacity-60">Sin datos de envío</span>
                                @endif
                            </td>
                            <td>{{ $pedido->status->label() }}</td>
                            <td>${{ number_format((float) $pedido->total, 0, ',', '.') }}</td>
                            <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <ul class="text-sm list-disc list-inside">
                                    @foreach($pedido->items as $item)
                                        <li>{{ $item->producto->nombre }} × {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 md:py-8 text-gray-500 text-xs md:text-sm">No hay pedidos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pedidos->links() }}
        </div>
    </div>
@endsection
