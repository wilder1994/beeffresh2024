<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

@php
    $inModal = $inModal ?? false;
    $profileUser = $user ?? auth()->user();
@endphp
<form id="profile-update-form" method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('patch')
    @if($inModal)
        <input type="hidden" name="_profile_modal" value="1" />
    @endif

    <section class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <section>
            <label class="bf-label" for="first_name">Nombre</label>
            <input id="first_name" name="first_name" type="text" class="bf-input" value="{{ old('first_name', $profileUser->first_name) }}" required autofocus autocomplete="given-name" />
            @error('first_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </section>
        <section>
            <label class="bf-label" for="last_name">Apellidos</label>
            <input id="last_name" name="last_name" type="text" class="bf-input" value="{{ old('last_name', $profileUser->last_name) }}" required autocomplete="family-name" />
            @error('last_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </section>
    </section>

    <section>
        <label class="bf-label" for="email">Correo</label>
        <input id="email" name="email" type="email" class="bf-input" value="{{ old('email', $profileUser->email) }}" required autocomplete="username" />
        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror

        @if ($profileUser instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $profileUser->hasVerifiedEmail())
            <p class="text-xs mt-2 text-stone-600">
                Correo sin verificar.
                <button form="send-verification" type="submit" class="text-[var(--bf-brand)] font-medium hover:underline">Reenviar enlace</button>
            </p>
            @if (session('status') === 'verification-link-sent')
                <p class="mt-1 text-xs text-green-700">Enlace enviado.</p>
            @endif
        @endif
    </section>

    @if($profileUser->isCustomer() || $profileUser->isSupplier())
        <section class="pt-2 border-t border-[var(--bf-border-brand-subtle)]">
            @include('profile.partials.contact-address', ['user' => $profileUser])
        </section>
    @endif

    <section class="bf-form-actions pt-4 mt-2">
        <button type="submit" class="bf-btn-primary">Guardar cambios</button>
        @if (session('status') === 'profile-updated')
            <span class="text-xs text-green-700 font-medium">Guardado</span>
        @endif
    </section>
</form>
