<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->when(
                !auth()->user()->isSuperadmin(),
                fn ($query) => $query->where('slug', '!=', 'superadmin')
            )
            ->withCount('users')
            ->withCount([
                'permissions' => fn ($query) => $query
                    ->where('permissions.slug', '!=', 'superadmin'),
            ])
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function show(Role $role): View
    {
        $role->load([
            'permissions' => fn ($query) => $query
                ->where('permissions.slug', '!=', 'superadmin'),
            'users',
        ]);

        return view('roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        abort_if($role->slug === 'superadmin', 404);

        $role->load([
            'permissions' => fn ($query) => $query
                ->where('permissions.slug', '!=', 'superadmin'),
        ]);

        $permissions = Permission::query()
            ->where('slug', '!=', 'superadmin')
            ->orderBy('name')
            ->get();

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->slug === 'superadmin') {
            return redirect()
                ->route('roles.show', $role)
                ->with('error', 'Superadmin permissions are protected and cannot be changed.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')
                    ->where(fn ($query) => $query->where('slug', '!=', 'superadmin')),
            ],
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        $role->permissions()->sync($permissionIds);

        return redirect()
            ->route('roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }
}