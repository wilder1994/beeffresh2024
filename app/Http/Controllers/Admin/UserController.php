<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\UserRepositoryContract;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {}

    public function index(Request $request): View
    {
        $audience = $request->query('audience');
        $audience = is_string($audience) && $audience !== '' ? $audience : null;

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : null;
        if ($search === '') {
            $search = null;
        }

        $list = $this->users->paginateFiltered($audience, $search, 15);

        return view('admin.users.index', [
            'users' => $list,
            'filters' => ['audience' => $audience, 'search' => $search],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['avatar'], $data['password_confirmation']);

        $user = User::query()->create($data);

        if ($request->hasFile('avatar')) {
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
            $user->save();
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $admins = User::query()->where('role', UserRole::Admin)->count();
        $validated = $request->validated();
        $newRole = UserRole::from($validated['role']);

        if ($user->isAdmin() && $newRole !== UserRole::Admin && $admins <= 1) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['role' => 'Debe existir al menos un usuario administrador.']);
        }

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        unset($validated['avatar'], $validated['password_confirmation']);

        $user->fill($validated);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Usuario actualizado.');
    }
}
