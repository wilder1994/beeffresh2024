@auth
    <x-account.dialog name="profile-account" maxWidth="4xl">
        <div class="bf-account-dialog__scroll overflow-y-auto overscroll-contain pr-1">
            @include('profile.partials.panel', ['user' => auth()->user(), 'inModal' => true])
        </div>
    </x-account.dialog>
@endauth
