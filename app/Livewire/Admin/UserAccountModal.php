<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class UserAccountModal extends Component
{
    public bool $open = false;

    /** @var 'view'|'edit'|'create' */
    public string $mode = 'view';

    public ?int $userId = null;

    public function openAccount(string $mode, ?int $userId = null): void
    {
        $this->mode = $mode;
        $this->userId = $userId;
        $this->open = true;
    }

    #[On('open-user-account')]
    public function onOpenUserAccount(string $mode, ?int $userId = null): void
    {
        $this->openAccount($mode, $userId);
    }

    #[On('user-saved')]
    public function onUserSaved(int $userId): void
    {
        $this->userId = $userId;
        $this->mode = 'view';
    }

    #[On('user-form-cancelled')]
    public function onUserFormCancelled(): void
    {
        if ($this->mode === 'create') {
            $this->close();

            return;
        }

        if ($this->userId !== null) {
            $this->mode = 'view';
        }
    }

    public function close(): void
    {
        $this->open = false;
        $this->reset('userId');
        $this->mode = 'view';
    }

    #[On('close-user-account')]
    public function onCloseUserAccount(): void
    {
        $this->close();
    }

    #[On('user-account-show-view')]
    public function onUserAccountShowView(): void
    {
        if ($this->userId !== null) {
            $this->mode = 'view';
        }
    }

    public function switchToEdit(): void
    {
        if ($this->userId !== null) {
            $this->mode = 'edit';
        }
    }

    public function render(): View
    {
        $viewUser = null;
        if ($this->userId !== null && in_array($this->mode, ['view', 'edit'], true)) {
            $viewUser = User::query()
                ->with(['roles', 'employeeProfile.position', 'customerProfile', 'supplierProfile'])
                ->find($this->userId);
        }

        $tabs = [['id' => 'cuenta', 'label' => 'Cuenta']];
        if ($viewUser?->isEmployee()) {
            $tabs[] = ['id' => 'empleado', 'label' => 'Empleado'];
        }
        if ($viewUser?->isCustomer()) {
            $tabs[] = ['id' => 'cliente', 'label' => 'Cliente'];
        }
        if ($viewUser?->isSupplier()) {
            $tabs[] = ['id' => 'proveedor', 'label' => 'Proveedor'];
        }

        return view('livewire.admin.user-account-modal', [
            'viewUser' => $viewUser,
            'tabs' => $tabs,
            'roleSlug' => $viewUser?->primaryRoleSlug(),
        ]);
    }
}
