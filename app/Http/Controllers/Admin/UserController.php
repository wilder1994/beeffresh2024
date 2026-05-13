<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\UserRepositoryContract;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {}

    public function index(Request $request): View
    {
        return $this->renderUserIndex($request, null);
    }

    public function indexClients(Request $request): View
    {
        return $this->renderUserIndex($request, 'clients');
    }

    public function indexCompany(Request $request): View
    {
        return $this->renderUserIndex($request, 'company');
    }

    public function indexProveedores(Request $request): View
    {
        return $this->renderUserIndex($request, 'suppliers');
    }

    private function renderUserIndex(Request $request, ?string $audienceFixed): View
    {
        $audience = $audienceFixed;
        if ($audience === null) {
            $q = $request->query('audience');
            $audience = is_string($q) && $q !== '' ? $q : null;
        }

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : null;
        if ($search === '') {
            $search = null;
        }

        $list = $this->users->paginateFiltered($audience, $search, 15);

        $formAction = match ($audienceFixed) {
            'clients' => route('admin.users.clientes'),
            'company' => route('admin.users.empresa'),
            'suppliers' => route('admin.users.proveedores'),
            default => route('admin.users.index'),
        };

        $pageHeading = match ($audienceFixed) {
            'clients' => 'Usuarios · Clientes',
            'company' => 'Usuarios · Empresa',
            'suppliers' => 'Usuarios · Proveedores',
            default => 'Usuarios del sistema',
        };

        return view('admin.users.index', [
            'users' => $list,
            'filters' => ['audience' => $audience, 'search' => $search],
            'audienceFixed' => $audienceFixed,
            'formAction' => $formAction,
            'pageHeading' => $pageHeading,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'employeeProfile.position', 'customerProfile', 'supplierProfile']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }
}
