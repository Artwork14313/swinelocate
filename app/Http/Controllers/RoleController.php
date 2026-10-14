<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function show(Role $role): View
    {
        $role->load([
            'permissions',
            'users',
        ]);

        return view('roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        abort_if($role->slug === 'superadmin', 404);

        $role->load('permissions');

        $permissions = Permission::query()
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
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionIds = $validated['permissions'] ?? [];

        $role->permissions()->sync($permissionIds);

        return redirect()
            ->route('roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }
}