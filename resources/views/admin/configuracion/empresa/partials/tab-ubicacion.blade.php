<form
    method="post"
    action="{{ route('admin.configuracion.empresa.ubicacion') }}"
    class="bf-form-panel bf-form-panel-tight space-y-4"
    x-data
    x-on:submit="
        const lat = document.getElementById('company-store-company_store_latitude');
        const lng = document.getElementById('company-store-company_store_longitude');
        if (!lat?.value?.trim() || !lng?.value?.trim()) {
            $event.preventDefault();
            alert('Abre el mapa (icono junto a la dirección), marca el punto y pulsa «Usar esta ubicación» antes de guardar.');
        }
    "
>
    @csrf
    @method('PUT')

    <p class="text-sm text-[var(--bf-muted)] leading-snug">
        Esta sede se usa en el <strong>mapa operativo</strong>, el centro del mapa y la lógica de asignación por cercanía.
        Abre el mapa con el icono junto a la dirección, confirma con <strong>Usar esta ubicación</strong> y luego guarda.
    </p>

    <x-forms.colombia-address
        prefix="company_store"
        id-prefix="company-store"
        :required="true"
        :address="$profile->store_address"
        :neighborhood="$profile->store_neighborhood"
        :city="$profile->store_city"
        :department="$profile->store_state"
        :latitude="$profile->store_latitude"
        :longitude="$profile->store_longitude"
    />

    @if($errors->has('company_store_latitude') || $errors->has('company_store_longitude'))
        <p class="text-sm text-red-600">
            Debes fijar el punto en el mapa (icono de ubicación → «Usar esta ubicación») para guardar las coordenadas GPS.
        </p>
    @endif
    @error('company_store_latitude')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    @error('company_store_longitude')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

    <div class="bf-form-actions justify-end">
        <button type="submit" class="bf-btn-primary">Guardar ubicación</button>
    </div>
</form>
