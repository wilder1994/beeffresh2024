@php
    $user = auth()->user();
    $user->loadMissing('employeeProfile.position');

    $accountLabel = match (true) {
        $user->isAdmin() => 'Administrador',
        $user->isDispatcher() => 'Despachador',
        $user->isCourier() => 'Domiciliario',
        default => 'Personal',
    };

    if ($user->employeeProfile?->position?->name) {
        $accountLabel = $user->employeeProfile->position->name;
    }
@endphp

<div class="bf-sidebar-account shrink-0">
    <div class="bf-sidebar-account__shell">
        <div class="bf-sidebar-account__main">
            <div class="bf-sidebar-account__avatar shrink-0">
                <x-user-avatar :user="$user" size="h-9 w-9 sm:h-10 sm:w-10" />
            </div>

            <div class="dropdown dropdown-top dropdown-end flex-1 min-w-0">
                <label tabindex="0" class="bf-sidebar-account__trigger min-w-0">
                    <span class="flex items-center gap-1 min-w-0">
                        <span class="bf-sidebar-account__name truncate">{{ $user->name }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0 opacity-60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                    </span>
                    <span class="bf-sidebar-account__meta truncate">{{ $accountLabel }}</span>
                </label>
                <ul tabindex="0" class="dropdown-content menu bg-[#fffaf5] text-gray-900 rounded-box shadow-lg w-52 z-[120] border border-amber-100 mb-2 text-sm">
                    <li><a href="{{ route('notifications.index') }}">Centro de notificaciones</a></li>
                    <li><x-profile.open-button tag="a" class="w-full text-left">Mi perfil</x-profile.open-button></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left">Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>

            <x-notifications.bell variant="dark" placement="aside" compact />
        </div>
    </div>
</div>
