@extends('catalog.layout')

@section('catalogTitle', 'Combos y packs · Catálogo')

@section('catalog')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Combos y packs</h1>
            <p class="text-sm text-gray-600">Packs multi-producto y ofertas por cantidad mínima.</p>
        </div>
        <a href="{{ route('catalog.offers.create') }}" class="bf-btn-primary shrink-0">Nueva oferta</a>
    </div>

    <div class="bf-table-panel bf-table-panel--flush">
        <table class="bf-table">
            <thead>
                <tr>
                    <th>Oferta</th>
                    <th>Tipo</th>
                    <th>Valor real</th>
                    <th>Precio oferta</th>
                    <th>Disponibles</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($offers as $row)
                    @php $offer = $row['offer']; @endphp
                    <tr>
                        <td>{{ $offer->name }}</td>
                        <td>{{ $offer->type->label() }}</td>
                        <td class="tabular-nums">${{ number_format($row['reference'], 0, ',', '.') }}</td>
                        <td class="tabular-nums">${{ number_format($row['offer_total'], 0, ',', '.') }}</td>
                        <td class="tabular-nums">{{ $row['available'] }}</td>
                        <td>{{ $offer->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('catalog.offers.edit', $offer) }}" class="text-sm text-[var(--bf-brand)] hover:underline mr-2">Editar</a>
                            <x-bf.delete-action
                                :action="route('catalog.offers.destroy', $offer)"
                                confirm-title="¿Eliminar oferta?"
                                :confirm-message="'Se eliminará «'.$offer->name.'».'"
                            />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">Sin ofertas registradas. Crea la primera con «Nueva oferta».</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
