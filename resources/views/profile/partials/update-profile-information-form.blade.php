<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
        @csrf
        @method('patch')

        <div class="flex flex-wrap items-center gap-6 pb-6 border-b border-base-200">
            <div class="relative shrink-0">
                <x-user-avatar :user="$user" size="h-24 w-24" class="ring-4 ring-[var(--bf-red)]/20" />
                <label for="profile-avatar-input" class="absolute bottom-0 right-0 btn btn-circle btn-sm bg-[var(--bf-red)] text-white border-0 shadow-lg cursor-pointer hover:brightness-110" title="Elegir foto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </label>
                <input type="file" name="avatar" id="profile-avatar-input" class="hidden" accept="image/*">
            </div>
            <div class="text-sm text-gray-600 max-w-md">
                <p class="font-medium text-gray-800">Foto de perfil</p>
                <p class="mt-1">Pulsa la cámara para elegir una imagen (máx. 2&nbsp;MB) y luego <strong>Guardar cambios</strong>.</p>
                @error('avatar')
                    <p class="text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" value="Nombre" />
                <x-text-input id="first_name" name="first_name" type="text" :value="old('first_name', $user->first_name)" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>
            <div>
                <x-input-label for="last_name" value="Apellidos" />
                <x-text-input id="last_name" name="last_name" type="text" :value="old('last_name', $user->last_name)" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--bf-crimson)]/40">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if($user->isCustomer() || $user->isSupplier())
            <div class="mt-8 pt-6 border-t border-base-200">
                @include('profile.partials.contact-address')
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
