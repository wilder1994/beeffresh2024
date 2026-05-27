@props([
    'prefix' => 'customer',
    'idPrefix' => '',
    'livewire' => false,
    'required' => true,
    'showReference' => false,
    'showPostal' => false,
    'showDeliveryNotes' => false,
    'address' => null,
    'neighborhood' => null,
    'city' => null,
    'department' => null,
    'latitude' => null,
    'longitude' => null,
])

@php
    use App\Domain\Geo\Colombia;
    use App\Domain\Geo\ColombianDepartments;
    use App\Domain\Geo\ColombianLocations;

    $fields = match ($prefix) {
        'company_store' => [
            'address' => 'company_store_address',
            'neighborhood' => 'company_store_neighborhood',
            'city' => 'company_store_city',
            'department' => 'company_store_state',
            'country' => 'company_store_country',
            'latitude' => 'company_store_latitude',
            'longitude' => 'company_store_longitude',
        ],
        'employee_home' => [
            'address' => 'employee_home_address',
            'neighborhood' => 'employee_home_neighborhood',
            'city' => 'employee_home_city',
            'department' => 'employee_home_state',
            'country' => 'employee_home_country',
            'latitude' => 'employee_home_latitude',
            'longitude' => 'employee_home_longitude',
        ],
        'supplier' => [
            'address' => 'supplier_business_address',
            'neighborhood' => 'supplier_neighborhood',
            'city' => 'supplier_city',
            'department' => 'supplier_state',
            'country' => 'supplier_country',
            'latitude' => 'supplier_latitude',
            'longitude' => 'supplier_longitude',
        ],
        default => [
            'address' => 'customer_address',
            'neighborhood' => 'customer_neighborhood',
            'city' => 'customer_city',
            'department' => 'customer_state',
            'country' => 'customer_country',
            'latitude' => 'customer_latitude',
            'longitude' => 'customer_longitude',
        ],
    };

    $pfx = $idPrefix !== '' ? rtrim($idPrefix, '-') . '-' : '';
    $addrVal = old($fields['address'], $address);
    $barrioVal = old($fields['neighborhood'], $neighborhood);
    $cityVal = old($fields['city'], $city);
    $deptVal = old($fields['department'], $department);
    $latVal = old($fields['latitude'], $latitude);
    $lngVal = old($fields['longitude'], $longitude);

    $fieldIds = [
        'address' => $pfx . $fields['address'],
        'neighborhood' => $pfx . $fields['neighborhood'],
        'city' => $pfx . $fields['city'],
        'department' => $pfx . $fields['department'],
        'latitude' => $pfx . $fields['latitude'],
        'longitude' => $pfx . $fields['longitude'],
    ];

    $pickerConfig = [
        'apiKey' => config('services.google.maps_api_key'),
        'fieldIds' => $fieldIds,
        'useLivewire' => $livewire,
        'models' => $livewire ? $fields : null,
        'initial' => [
            'address' => $addrVal,
            'neighborhood' => $barrioVal,
            'city' => $cityVal,
            'department' => $deptVal,
            'latitude' => $latVal,
            'longitude' => $lngVal,
        ],
    ];
@endphp

@once
    <script>
        window.__bfColombiaLocations = @json(ColombianLocations::all());
        window.__bfColombiaDepartments = @json(ColombianDepartments::names());
    </script>
@endonce

<div
    data-bf-address-picker
    x-data="colombiaAddressPicker(@js($pickerConfig))"
    class="col-span-full grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-3"
    {{ $attributes }}
>
    <div class="sm:col-span-2">
        <label class="bf-label" for="{{ $fieldIds['address'] }}">Dirección</label>
        <div class="flex gap-2">
            <input
                id="{{ $fieldIds['address'] }}"
                type="text"
                @if (! $livewire)
                    name="{{ $fields['address'] }}"
                @endif
                x-model="address"
                @input="onAddressInput()"
                class="bf-input flex-1 @error($fields['address']) ring-1 ring-red-400 @enderror"
                placeholder="Calle, número, edificio…"
                autocomplete="street-address"
                @if ($required) required @endif
            />
            <button
                type="button"
                class="btn btn-sm btn-ghost shrink-0 border border-[var(--bf-border-brand-subtle)] px-3"
                title="Elegir en el mapa"
                aria-label="Abrir mapa"
                @click="openMap()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[var(--bf-brand)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
            </button>
        </div>
        @error($fields['address'])<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <input type="hidden" id="{{ $fieldIds['latitude'] }}" name="{{ $livewire ? '' : $fields['latitude'] }}" x-model="latitude" />
    <input type="hidden" id="{{ $fieldIds['longitude'] }}" name="{{ $livewire ? '' : $fields['longitude'] }}" x-model="longitude" />

    <div>
        <label class="bf-label" for="{{ $fieldIds['department'] }}">Departamento</label>
        <select
            id="{{ $fieldIds['department'] }}"
            @if (! $livewire) name="{{ $fields['department'] }}" @endif
            x-model="department"
            @change="onDepartmentChange()"
            class="bf-select @error($fields['department']) ring-1 ring-red-400 @enderror"
            @if ($required) required @endif
        >
            <option value="">— Seleccionar departamento —</option>
            @foreach (ColombianDepartments::names() as $deptName)
                <option value="{{ $deptName }}">{{ $deptName }}</option>
            @endforeach
            @if ($deptVal && ! ColombianDepartments::isKnown($deptVal))
                <option value="{{ $deptVal }}" selected>{{ $deptVal }} (anterior)</option>
            @endif
        </select>
        @error($fields['department'])<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="bf-label" for="{{ $fieldIds['city'] }}">Ciudad</label>
        <select
            id="{{ $fieldIds['city'] }}"
            @if (! $livewire) name="{{ $fields['city'] }}" @endif
            x-model="city"
            @change="onCityChange()"
            class="bf-select @error($fields['city']) ring-1 ring-red-400 @enderror"
            :disabled="!department"
            @if ($required) required @endif
        >
            <option value="">— Seleccionar ciudad —</option>
            <template x-for="c in cityOptions" :key="c">
                <option :value="c" x-text="c"></option>
            </template>
        </select>
        @error($fields['city'])<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="bf-label" for="{{ $fieldIds['neighborhood'] }}">Barrio</label>
        <select
            x-show="neighborhoodIsSelect"
            x-cloak
            id="{{ $fieldIds['neighborhood'] }}-select"
            @if (! $livewire) :name="neighborhoodIsSelect ? '{{ $fields['neighborhood'] }}' : null" @endif
            x-model="neighborhood"
            @change="onNeighborhoodChange()"
            class="bf-select w-full @error($fields['neighborhood']) ring-1 ring-red-400 @enderror"
            :disabled="!city"
            @if ($required) :required="neighborhoodIsSelect" @endif
        >
            <option value="">— Seleccionar barrio —</option>
            <template x-for="b in neighborhoodOptions" :key="b">
                <option :value="b" x-text="b"></option>
            </template>
        </select>
        <input
            x-show="!neighborhoodIsSelect"
            x-cloak
            id="{{ $fieldIds['neighborhood'] }}"
            type="text"
            @if (! $livewire) :name="!neighborhoodIsSelect ? '{{ $fields['neighborhood'] }}' : null" @endif
            x-model="neighborhood"
            @input="onNeighborhoodChange()"
            class="bf-input w-full @error($fields['neighborhood']) ring-1 ring-red-400 @enderror"
            placeholder="Escribe el barrio"
            :disabled="!city"
            @if ($required) :required="!neighborhoodIsSelect" @endif
        />
        @error($fields['neighborhood'])<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="bf-label">País</label>
        <x-forms.colombia-country :name="$livewire ? '' : $fields['country']" :id="$pfx . $fields['country']" />
        @if ($livewire)
            <input type="hidden" wire:model="{{ $fields['country'] }}" value="CO" />
        @endif
    </div>

    @if ($showReference)
        {{ $reference ?? '' }}
    @endif

    @if ($showPostal)
        {{ $postal ?? '' }}
    @endif

    @if ($showDeliveryNotes)
        {{ $deliveryNotes ?? '' }}
    @endif

    <div
        x-show="mapOpen"
        x-cloak
        class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-stone-900/50"
        @keydown.escape.window="mapOpen && closeMap()"
    >
        <div
            class="bf-surface w-full max-w-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]"
            @click.outside="closeMap()"
        >
            <header class="px-4 py-3 border-b border-[var(--bf-border-brand-subtle)] flex items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-stone-800">Ubicar dirección en el mapa</h3>
                <button type="button" class="btn btn-xs btn-ghost" @click="closeMap()">✕</button>
            </header>
            <div class="p-4 space-y-3 flex-1 overflow-y-auto">
                <p class="text-xs text-stone-600">Usa las sugerencias de <strong>Google</strong> (no las del navegador), pulsa <strong>Buscar</strong> o <strong>Enter</strong>, o haz clic / arrastra el pin.</p>
                <template x-if="mapError">
                    <p class="text-sm text-red-700" x-text="mapError"></p>
                </template>
                <template x-if="mapSearchError">
                    <p class="text-sm text-red-700" x-text="mapSearchError"></p>
                </template>
                {{-- Sin <form> anidado: rompe el formulario padre (p. ej. Guardar ubicación en configuración empresa). --}}
                <div class="flex gap-2" role="search" autocomplete="off">
                    <input
                        x-ref="mapSearch"
                        type="search"
                        name="bf_map_search"
                        class="bf-input flex-1"
                        placeholder="Buscar dirección en Colombia…"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                        :disabled="mapBusy"
                        @keydown.enter.prevent="searchMapAddress()"
                    />
                    <button type="button" class="bf-btn-primary btn-sm shrink-0" :disabled="mapBusy" @click="searchMapAddress()">
                        <span x-show="!mapBusy">Buscar</span>
                        <span x-show="mapBusy">…</span>
                    </button>
                </div>
                <div x-show="mapLoading" class="text-sm text-stone-500 py-8 text-center">Cargando mapa…</div>
                <div x-ref="mapCanvas" class="w-full h-72 sm:h-80 rounded-lg border border-[var(--bf-border-brand-subtle)]" x-show="!mapLoading && !mapError"></div>
            </div>
            <footer class="px-4 py-3 border-t border-[var(--bf-border-brand-subtle)] flex justify-end gap-2">
                <button type="button" class="bf-btn-ghost btn-sm" @click="closeMap()">Cancelar</button>
                <button type="button" class="bf-btn-primary btn-sm" @click="confirmMap()">Usar esta ubicación</button>
            </footer>
        </div>
    </div>
</div>
