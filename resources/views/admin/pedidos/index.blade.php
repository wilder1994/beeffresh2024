@extends('layouts.app')

@section('titulo', 'Pedidos')
@section('cabecera', 'Pedidos en línea')

@section('contenido')
    <div class="py-6 max-w-7xl mx-auto px-4">
        <div class="overflow-x-auto bg-base-100 rounded-lg shadow">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Ítems</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr>
                            <td>{{ $pedido->id }}</td>
                            <td>{{ $pedido->user->name }}<br><span class="text-sm opacity-70">{{ $pedido->user->email }}</span></td>
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
                            <td colspan="6" class="text-center py-8 text-gray-500">No hay pedidos registrados.</td>
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
