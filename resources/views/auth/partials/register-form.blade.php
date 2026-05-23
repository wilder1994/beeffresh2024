@props([
    'inModal' => false,
])

@php
    $pfx = $inModal ? 'modal-' : '';
    $fieldClass = $inModal ? 'bf-input' : 'block mt-1 w-full';
    $labelClass = $inModal ? 'bf-label' : '';
    $gridGap = $inModal ? 'gap-3' : 'gap-3 mt-4';
    $sectionClass = $inModal ? 'space-y-3' : 'space-y-4';
@endphp

<form method="POST" action="{{ route('register') }}" class="{{ $sectionClass }}">
    @csrf

    <section>
        <h3 @class(['bf-form-section-title' => $inModal, 'text-sm font-semibold text-stone-700 mb-3' => ! $inModal])>Datos personales</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $gridGap }}">
            <div>
                @if($inModal)
                    <label class="bf-label" for="{{ $pfx }}first_name">Nombre</label>
                    <input id="{{ $pfx }}first_name" name="first_name" type="text" class="bf-input" value="{{ old('first_name') }}" required autofocus autocomplete="given-name" />
                @else
                    <x-input-label for="{{ $pfx }}first_name" :value="'Nombre'" />
                    <x-text-input id="{{ $pfx }}first_name" class="{{ $fieldClass }}" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                @endif
                <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
            </div>
            <div>
                @if($inModal)
                    <label class="bf-label" for="{{ $pfx }}last_name">Apellidos</label>
                    <input id="{{ $pfx }}last_name" name="last_name" type="text" class="bf-input" value="{{ old('last_name') }}" required autocomplete="family-name" />
                @else
                    <x-input-label for="{{ $pfx }}last_name" :value="'Apellidos'" />
                    <x-text-input id="{{ $pfx }}last_name" class="{{ $fieldClass }}" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                @endif
                <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
            </div>
            <div>
                @if($inModal)
                    <label class="bf-label" for="{{ $pfx }}phone">Teléfono</label>
                    <input id="{{ $pfx }}phone" name="phone" type="tel" class="bf-input" value="{{ old('phone') }}" required autocomplete="tel" />
                @else
                    <x-input-label for="{{ $pfx }}phone" :value="'Teléfono'" />
                    <x-text-input id="{{ $pfx }}phone" class="{{ $fieldClass }}" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" />
                @endif
                <x-input-error :messages="$errors->get('phone')" class="mt-1" />
            </div>
            <div>
                @if($inModal)
                    <label class="bf-label" for="{{ $pfx }}document_type">Tipo documento</label>
                    <x-forms.document-type-select
                        id="{{ $pfx }}document_type"
                        name="document_type"
                        :legacy-value="old('document_type')"
                        required
                    />
                @else
                    <x-input-label for="{{ $pfx }}document_type" :value="'Tipo documento'" />
                    <x-forms.document-type-select
                        id="{{ $pfx }}document_type"
                        name="document_type"
                        :legacy-value="old('document_type')"
                        class="{{ $fieldClass }}"
                        required
                    />
                @endif
                <x-input-error :messages="$errors->get('document_type')" class="mt-1" />
            </div>
            <div>
                @if($inModal)
                    <label class="bf-label" for="{{ $pfx }}document_number">Número documento</label>
                    <input id="{{ $pfx }}document_number" name="document_number" type="text" class="bf-input" value="{{ old('document_number') }}" required autocomplete="off" />
                @else
                    <x-input-label for="{{ $pfx }}document_number" :value="'Número documento'" />
                    <x-text-input id="{{ $pfx }}document_number" class="{{ $fieldClass }}" type="text" name="document_number" :value="old('document_number')" required />
                @endif
                <x-input-error :messages="$errors->get('document_number')" class="mt-1" />
            </div>
        </div>
    </section>

    <section>
        <h3 @class(['bf-form-section-title' => $inModal, 'text-sm font-semibold text-stone-700 mb-3' => ! $inModal])>Cuenta de acceso</h3>
        <div class="grid grid-cols-1 {{ $gridGap }}">
            <div>
                @if($inModal)
                    <label class="bf-label" for="{{ $pfx }}email">Correo electrónico</label>
                    <input id="{{ $pfx }}email" name="email" type="email" class="bf-input" value="{{ old('email') }}" required autocomplete="username" />
                @else
                    <x-input-label for="{{ $pfx }}email" :value="'Correo electrónico'" />
                    <x-text-input id="{{ $pfx }}email" class="{{ $fieldClass }}" type="email" name="email" :value="old('email')" required autocomplete="username" />
                @endif
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    @if($inModal)
                        <label class="bf-label" for="{{ $pfx }}password">Contraseña</label>
                        <input id="{{ $pfx }}password" name="password" type="password" class="bf-input" required autocomplete="new-password" />
                    @else
                        <x-input-label for="{{ $pfx }}password" :value="'Contraseña'" />
                        <x-text-input id="{{ $pfx }}password" class="{{ $fieldClass }}" type="password" name="password" required autocomplete="new-password" />
                    @endif
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    @if($inModal)
                        <label class="bf-label" for="{{ $pfx }}password_confirmation">Confirmar contraseña</label>
                        <input id="{{ $pfx }}password_confirmation" name="password_confirmation" type="password" class="bf-input" required autocomplete="new-password" />
                    @else
                        <x-input-label for="{{ $pfx }}password_confirmation" :value="'Confirmar contraseña'" />
                        <x-text-input id="{{ $pfx }}password_confirmation" class="{{ $fieldClass }}" type="password" name="password_confirmation" required autocomplete="new-password" />
                    @endif
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
                </div>
            </div>
        </div>
    </section>

    <section>
        <h3 @class(['bf-form-section-title' => $inModal, 'text-sm font-semibold text-stone-700 mb-3' => ! $inModal])>Domicilio de entrega</h3>
        <x-forms.colombia-address
            prefix="customer"
            :id-prefix="$pfx"
            :address="old('customer_address')"
            :neighborhood="old('customer_neighborhood')"
            :city="old('customer_city')"
            :department="old('customer_state')"
            :latitude="old('customer_latitude')"
            :longitude="old('customer_longitude')"
            show-postal
            show-delivery-notes
            class="!grid !grid-cols-1 sm:!grid-cols-2 {{ $gridGap }}"
        >
            <x-slot:postal>
                <div>
                    @if($inModal)
                        <label class="bf-label" for="{{ $pfx }}customer_postal_code">Código postal</label>
                        <input id="{{ $pfx }}customer_postal_code" name="customer_postal_code" type="text" class="bf-input" value="{{ old('customer_postal_code') }}" autocomplete="postal-code" />
                    @else
                        <x-input-label for="{{ $pfx }}customer_postal_code" :value="'Código postal'" />
                        <x-text-input id="{{ $pfx }}customer_postal_code" class="{{ $fieldClass }}" type="text" name="customer_postal_code" :value="old('customer_postal_code')" autocomplete="postal-code" />
                    @endif
                    <x-input-error :messages="$errors->get('customer_postal_code')" class="mt-1" />
                </div>
            </x-slot:postal>
            <x-slot:deliveryNotes>
                <div class="sm:col-span-2">
                    @if($inModal)
                        <label class="bf-label" for="{{ $pfx }}customer_delivery_notes">Indicaciones de entrega</label>
                        <textarea id="{{ $pfx }}customer_delivery_notes" name="customer_delivery_notes" rows="2" class="bf-textarea min-h-[3rem]">{{ old('customer_delivery_notes') }}</textarea>
                    @else
                        <x-input-label for="{{ $pfx }}customer_delivery_notes" :value="'Indicaciones de entrega'" />
                        <textarea id="{{ $pfx }}customer_delivery_notes" name="customer_delivery_notes" rows="2" class="bf-textarea min-h-[3rem] mt-1 w-full">{{ old('customer_delivery_notes') }}</textarea>
                    @endif
                    <x-input-error :messages="$errors->get('customer_delivery_notes')" class="mt-1" />
                </div>
            </x-slot:deliveryNotes>
        </x-forms.colombia-address>
    </section>

    <label class="flex items-start gap-2 cursor-pointer text-sm text-stone-600">
        <input
            type="checkbox"
            name="accepts_promotions"
            value="1"
            class="mt-0.5 rounded border-[var(--bf-border-brand-subtle)] text-[var(--bf-brand)] focus:ring-[var(--bf-brand)]"
            @checked(old('accepts_promotions', true))
        />
        <span>Deseo recibir promociones y novedades por correo.</span>
    </label>
    <x-input-error :messages="$errors->get('accepts_promotions')" class="mt-1" />

    <div @class(['bf-form-actions pt-2 justify-end items-center', 'gap-2 flex-wrap' => $inModal, 'mt-4' => ! $inModal])>
        @if($inModal)
            <button type="button" class="bf-btn-ghost btn-sm" x-on:click="$dispatch('close-modal', 'register-client')">Cancelar</button>
        @else
            <a class="underline text-sm text-[var(--bf-muted)] hover:text-[var(--bf-rust)] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--bf-crimson)]/40" href="{{ route('login', ['tipo' => 'cliente']) }}">
                ¿Ya tienes una cuenta?
            </a>
        @endif

        <x-primary-button @class(['ms-4' => ! $inModal])>
            Registrarse
        </x-primary-button>
    </div>
</form>
