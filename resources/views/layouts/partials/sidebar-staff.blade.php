@php
    use App\Domain\Users\PermissionKey;
    use App\Domain\Users\RoleSlug;
    use App\Models\Logo;
    use App\Models\User;
    $logoPrincipal = Logo::where('tipo', 'principal')->first();
    $logoPrincipalSrc = ($logoPrincipal && $logoPrincipal->imagen)
        ? asset('storage/logos/'.$logoPrincipal->imagen)
        : asset('logos/logo.jpeg');
    $isAdmin = auth()->user()->isAdmin();
    $canOrders = auth()->user()->can(PermissionKey::MODULE_ORDERS);
    $canCourier = auth()->user()->canAccessCourierModule();
    $canCatalog = auth()->user()->can(PermissionKey::MODULE_CATALOG);
    $canUsers = auth()->user()->can(PermissionKey::MODULE_USERS);
    $canSettings = auth()->user()->can(PermissionKey::MODULE_SETTINGS);

    $navActive = 'flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition border-l-4';
    $navIdle = 'border-transparent text-white/90 hover:bg-white/10';
    $navOn = 'border-[var(--bf-gold)] bg-white/15 text-white';
    $operacionesHijoActivo = ($canOrders && request()->routeIs('admin.pedidos.*'))
        || ($canCatalog && request()->routeIs('catalog.*'))
        || ($canCourier && request()->routeIs('courier.*'));
    $usuariosNavUser = request()->route('user');
    $usuariosNavUser = $usuariosNavUser instanceof User ? $usuariosNavUser : null;
    $audienceQuery = request()->query('audience');
    $audienceQuery = is_string($audienceQuery) && $audienceQuery !== '' ? $audienceQuery : null;
    $usuariosTodosActivo = request()->routeIs('admin.users.index') && $audienceQuery === null
        || request()->routeIs('admin.users.create');
    $usuariosClientesActivo = request()->routeIs('admin.users.clientes')
        || ($audienceQuery === 'clients' && request()->routeIs('admin.users.index'))
        || ($usuariosNavUser && RoleSlug::audienceId($usuariosNavUser->primaryRoleSlug() ?? '') === 'clients'
            && (request()->routeIs('admin.users.show') || request()->routeIs('admin.users.edit')));
    $usuariosEmpresaActivo = request()->routeIs('admin.users.empresa')
        || ($audienceQuery === 'company' && request()->routeIs('admin.users.index'))
        || ($usuariosNavUser && RoleSlug::audienceId($usuariosNavUser->primaryRoleSlug() ?? '') === 'company'
            && (request()->routeIs('admin.users.show') || request()->routeIs('admin.users.edit')));
    $usuariosProveedoresActivo = request()->routeIs('admin.users.proveedores')
        || ($audienceQuery === 'suppliers' && request()->routeIs('admin.users.index'))
        || ($usuariosNavUser && RoleSlug::audienceId($usuariosNavUser->primaryRoleSlug() ?? '') === 'suppliers'
            && (request()->routeIs('admin.users.show') || request()->routeIs('admin.users.edit')));
    $usuariosHijoActivo = $canUsers && request()->routeIs('admin.users.*');
    $ajustesHijoActivo = ($canCatalog && (request()->routeIs('videos.*') || request()->routeIs('recetas.*')))
        || ($isAdmin && request()->routeIs('admin.configuracion.*'))
        || ($canUsers && request()->routeIs('admin.positions.*'));
    $sectionHeading = 'flex items-center gap-2 sm:gap-2.5 px-2 sm:px-3 pt-3 sm:pt-4 pb-1 text-[9px] sm:text-[10px] uppercase tracking-wider text-white/45 font-semibold';
@endphp

<div class="flex flex-col h-full min-h-0 flex-1 w-full min-w-0">
    <div class="p-3 sm:p-4 border-b border-white/10 shrink-0">
        <div class="flex items-start gap-2 sm:gap-3">
            <div class="relative shrink-0" @if($isAdmin) x-data="logoCropUpload({ submitUrl: @js(route('admin.logo.update')), csrfToken: @js(csrf_token()) })" @endif>
                <img src="{{ $logoPrincipalSrc }}" alt="BEEF FRESH" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover ring-2 ring-white/30">
                @if($isAdmin)
                    <input type="file" id="sidebar-company-logo-input" class="hidden" accept="image/jpeg,image/png,image/webp" x-on:change="pickFile($event)">
                    <label for="sidebar-company-logo-input" class="absolute -bottom-1 -right-1 btn btn-circle btn-xs bg-white text-[var(--bf-rust-deep)] border-0 shadow-md hover:bg-[var(--bf-cream)] cursor-pointer" title="Cambiar logo de la empresa">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </label>
                    <x-bf.image-crop-dialog />
                @endif
            </div>
            <a href="{{ route('dashboard') }}" class="leading-tight flex-1 min-w-0 group pt-0.5">
                <span class="block font-brand tracking-tight text-sm sm:text-base text-white group-hover:text-[var(--bf-sun)]/95 transition">BEEF FRESH</span>
                <span class="text-[10px] sm:text-[11px] uppercase tracking-widest text-white/60">Panel interno</span>
            </a>
            <button type="button" x-on:click="toggleDesktopSidebar()" class="hidden lg:flex btn btn-ghost btn-xs btn-square shrink-0 text-white/90 hover:bg-white/10 border border-white/20" title="Ocultar menú lateral" aria-label="Ocultar menú lateral">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
            </button>
            <button type="button" x-on:click="closeMobileMenu()" class="lg:hidden btn btn-ghost btn-xs btn-square shrink-0 text-white/90 hover:bg-white/10" title="Cerrar menú" aria-label="Cerrar menú">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto min-h-0 py-3 sm:py-4 px-1.5 sm:px-2 space-y-0.5" x-on:click.capture="if ($event.target.closest('a')) closeMobileMenu()">
        <a href="{{ route('dashboard') }}" @class([$navActive, request()->routeIs('dashboard') ? $navOn : $navIdle])>
            <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
            Inicio
        </a>

        @if($isAdmin)
            <a href="{{ route('admin.payments.index') }}" @class([$navActive, request()->routeIs('admin.payments.*') ? $navOn : $navIdle])>
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Pagos
            </a>
        @endif

        @if($canOrders || $canCatalog || $canCourier)
            <div class="space-y-0.5 pt-1" x-data="{ operacionesOpen: {{ $operacionesHijoActivo ? 'true' : 'false' }} }" role="group" aria-label="Operaciones">
                <button
                    type="button"
                    x-on:click="operacionesOpen = !operacionesOpen"
                    x-bind:aria-expanded="operacionesOpen ? 'true' : 'false'"
                    @class([
                        'w-full flex items-center justify-between gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-lg text-left text-[9px] sm:text-[10px] uppercase tracking-wider font-semibold transition border border-transparent',
                        $operacionesHijoActivo ? 'text-white/85 bg-white/10' : 'text-white/45 hover:bg-white/10 hover:text-white/70',
                    ])
                >
                    <span class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        <span class="truncate">Operaciones</span>
                    </span>
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-white/45 transition-transform duration-200" x-bind:class="{ 'rotate-180': operacionesOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="operacionesOpen" x-transition class="ml-1 sm:ml-2 pl-2 sm:pl-3 border-l border-white/15 space-y-0.5 pb-0.5">
                    @if($canOrders)
                    <a href="{{ route('admin.pedidos.index') }}" @class([$navActive, request()->routeIs('admin.pedidos.index', 'admin.pedidos.show', 'admin.pedidos.ticket.*') ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        Pedidos
                    </a>
                    <a href="{{ route('admin.pedidos.map') }}" @class([$navActive, request()->routeIs('admin.pedidos.map') ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        Mapa operativo
                    </a>
                    @endif
                    @if($canCourier)
                    <a href="{{ route('courier.orders.index') }}" @class([$navActive, request()->routeIs('courier.*') ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                        Mis entregas
                    </a>
                    @endif
                    @if($canCatalog)
                    <a href="{{ route('catalog.products.index') }}" @class([$navActive, request()->routeIs('catalog.*') ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        Catálogo
                    </a>
                    @endif
                </div>
            </div>
        @endif

        @if($canUsers)
            <div class="space-y-0.5 pt-1" x-data="{ usuariosOpen: {{ $usuariosHijoActivo ? 'true' : 'false' }} }" role="group" aria-label="Usuarios">
                <button
                    type="button"
                    x-on:click="usuariosOpen = !usuariosOpen"
                    x-bind:aria-expanded="usuariosOpen ? 'true' : 'false'"
                    @class([
                        'w-full flex items-center justify-between gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-lg text-left text-[9px] sm:text-[10px] uppercase tracking-wider font-semibold transition border border-transparent',
                        $usuariosHijoActivo ? 'text-white/85 bg-white/10' : 'text-white/45 hover:bg-white/10 hover:text-white/70',
                    ])
                >
                    <span class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span class="truncate">Usuarios</span>
                    </span>
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-white/45 transition-transform duration-200" x-bind:class="{ 'rotate-180': usuariosOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="usuariosOpen" x-transition class="ml-1 sm:ml-2 pl-2 sm:pl-3 border-l border-white/15 space-y-0.5 pb-0.5">
                    <a href="{{ route('admin.users.index') }}" @class([$navActive, $usuariosTodosActivo ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        Todos
                    </a>
                    <a href="{{ route('admin.users.clientes') }}" @class([$navActive, $usuariosClientesActivo ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Clientes
                    </a>
                    <a href="{{ route('admin.users.empresa') }}" @class([$navActive, $usuariosEmpresaActivo ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Empresa
                    </a>
                    <a href="{{ route('admin.users.proveedores') }}" @class([$navActive, $usuariosProveedoresActivo ? $navOn : $navIdle])>
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                        Proveedores
                    </a>
                </div>
            </div>
        @endif

        @if($canCatalog || $canSettings || $canUsers)
            <div class="space-y-0.5 pt-1" x-data="{ ajustesOpen: {{ $ajustesHijoActivo ? 'true' : 'false' }} }" role="group" aria-label="Ajustes de tienda y contenido">
                <button
                    type="button"
                    x-on:click="ajustesOpen = !ajustesOpen"
                    x-bind:aria-expanded="ajustesOpen ? 'true' : 'false'"
                    @class([
                        'w-full flex items-center justify-between gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-lg text-left text-[9px] sm:text-[10px] uppercase tracking-wider font-semibold transition border border-transparent',
                        $ajustesHijoActivo ? 'text-white/85 bg-white/10' : 'text-white/45 hover:bg-white/10 hover:text-white/70',
                    ])
                >
                    <span class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="truncate">Ajustes</span>
                    </span>
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0 text-white/45 transition-transform duration-200" x-bind:class="{ 'rotate-180': ajustesOpen }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
                <div x-show="ajustesOpen" x-transition class="ml-1 sm:ml-2 pl-2 sm:pl-3 border-l border-white/15 space-y-0.5 pb-0.5">
                @if($canCatalog)
                <a href="{{ route('videos.index') }}" @class([$navActive, request()->routeIs('videos.*') ? $navOn : $navIdle])>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    Videos
                </a>
                <a href="{{ route('recetas.index') }}" @class([$navActive, request()->routeIs('recetas.*') ? $navOn : $navIdle])>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                    Recetas
                </a>
                @endif
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex items-center gap-2 sm:gap-3 px-2 sm:px-3 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium border-l-4 border-transparent text-white/90 hover:bg-white/10">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                    Ver en tienda
                </a>
                @if($isAdmin)
                <a href="{{ route('admin.configuracion.empresa') }}" @class([$navActive, request()->routeIs('admin.configuracion.*') ? $navOn : $navIdle])>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    Empresa y marca
                </a>
                @endif
                @if($canUsers)
                <a href="{{ route('admin.positions.index') }}" @class([$navActive, request()->routeIs('admin.positions.*') ? $navOn : $navIdle])>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                    Cargos
                </a>
                @endif
                </div>
            </div>
        @endif
    </nav>

    <div class="p-2 sm:p-3 border-t border-white/10 bg-black/20 shrink-0 overflow-visible">
        <x-staff.sidebar-account />
    </div>
</div>
