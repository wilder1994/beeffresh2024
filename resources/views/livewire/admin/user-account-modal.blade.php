<div x-data x-effect="document.body.classList.toggle('overflow-y-hidden', $wire.open)">
    @if($open)
        <div
            class="fixed inset-0 z-[55] overflow-y-auto px-3 py-6 sm:px-4"
            role="dialog"
            aria-modal="true"
            wire:key="user-account-modal-overlay"
        >
            <div class="fixed inset-0 bg-stone-900/50" wire:click="close" aria-hidden="true"></div>

            <div class="flex min-h-full items-start sm:items-center justify-center pointer-events-none">
                <div
                    class="pointer-events-auto relative w-full sm:max-w-4xl max-h-[min(90vh,52rem)] flex flex-col"
                    wire:click.stop
                >
                    <div class="bf-account-dialog__scroll overflow-y-auto overscroll-contain pr-1">
                        @if(session('success'))
                            <div class="mb-3 rounded-lg border border-green-300 bg-green-50 px-3 py-2 text-sm text-green-900">{{ session('success') }}</div>
                        @endif

                        @if($mode === 'view' && $viewUser)
                            <x-account.shell
                                :user="$viewUser"
                                mode="view"
                                context="admin"
                                :tabs="$tabs"
                                in-modal
                            >
                                <x-slot:headerActions>
                                    <button type="button" wire:click="switchToEdit" class="bf-btn-primary btn-sm">Editar</button>
                                    <button type="button" wire:click="close" class="bf-btn-ghost btn-sm">Cerrar</button>
                                </x-slot:headerActions>
                                @include('admin.users.partials.account-view', ['user' => $viewUser])
                            </x-account.shell>
                        @elseif($mode === 'edit' && $userId)
                            <x-account.shell
                                :user="$viewUser"
                                mode="edit"
                                context="admin"
                                in-modal
                            >
                                <x-slot:headerActions>
                                    @if($viewUser)
                                        <button type="button" wire:click="$set('mode', 'view')" class="bf-btn-ghost btn-sm">Ver ficha</button>
                                    @endif
                                    <button type="button" wire:click="close" class="bf-btn-ghost btn-sm">Cerrar</button>
                                </x-slot:headerActions>
                                <livewire:admin.user-form :user-id="$userId" :embedded="true" :key="'user-form-edit-'.$userId" />
                            </x-account.shell>
                        @elseif($mode === 'create')
                            <x-account.shell
                                mode="create"
                                context="admin"
                                in-modal
                            >
                                <x-slot:headerActions>
                                    <button type="button" wire:click="close" class="bf-btn-ghost btn-sm">Cerrar</button>
                                </x-slot:headerActions>
                                <livewire:admin.user-form :embedded="true" key="user-form-create" />
                            </x-account.shell>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
