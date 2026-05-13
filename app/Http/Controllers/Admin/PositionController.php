<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePositionRequest;
use App\Http\Requests\Admin\UpdatePositionRequest;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $positions = Position::query()->orderBy('name')->paginate(20);

        return view('admin.positions.index', compact('positions'));
    }

    public function create(): View
    {
        return view('admin.positions.create');
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        Position::query()->create($request->validated());

        return redirect()->route('admin.positions.index')->with('success', 'Cargo creado.');
    }

    public function edit(Position $position): View
    {
        return view('admin.positions.edit', compact('position'));
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $position->update($request->validated());

        return redirect()->route('admin.positions.index')->with('success', 'Cargo actualizado.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->employees()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar: hay empleados con este cargo.');
        }
        $position->delete();

        return redirect()->route('admin.positions.index')->with('success', 'Cargo eliminado.');
    }
}
