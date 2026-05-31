<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogService;
use App\Support\Roles\RoleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->with('permissions')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        // Only super_admin can create roles
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only super admins can create roles.');

        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'slug'           => ['required', 'string', 'regex:/^[a-z_]+$/', 'unique:roles,slug'],
            'description'    => ['nullable', 'string'],
            'permissions'    => ['nullable', 'array'],
            'permissions.*'  => ['uuid', 'exists:permissions,id'],
        ]);

        $permissionIds = $data['permissions'] ?? [];

        $role = Role::create($data);
        $changes = $role->permissions()->sync($permissionIds);

        app(AuditLogService::class)->log('role_permissions_synced', $role, metadata: [
            'attached' => $changes['attached'],
            'detached' => $changes['detached'],
            'updated' => $changes['updated'],
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        // Immutable roles can be viewed but only super_admin can edit them
        if (RoleManager::isImmutable($role->slug)) {
            abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only super admins can edit immutable roles.');
        }

        $permissions         = Permission::orderBy('name')->get();
        $assignedPermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'assignedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if (RoleManager::isImmutable($role->slug)) {
            abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only super admins can modify immutable roles.');
        }

        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'description'    => ['nullable', 'string'],
            'permissions'    => ['nullable', 'array'],
            'permissions.*'  => ['uuid', 'exists:permissions,id'],
        ]);

        $permissionIds = $data['permissions'] ?? [];
        $oldPermissionIds = $role->permissions()->pluck('permissions.id')->all();

        $role->update($data);
        $changes = $role->permissions()->sync($permissionIds);

        if ($changes['attached'] !== [] || $changes['detached'] !== [] || $changes['updated'] !== []) {
            app(AuditLogService::class)->log(
                'role_permissions_synced',
                $role,
                oldValues: ['permission_ids' => $oldPermissionIds],
                newValues: ['permission_ids' => $permissionIds],
                metadata: [
                    'attached' => $changes['attached'],
                    'detached' => $changes['detached'],
                    'updated' => $changes['updated'],
                ]
            );
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        // Immutable roles can never be deleted, period
        if (RoleManager::isImmutable($role->slug)) {
            return back()->with('error', "The '{$role->name}' role is a core system role and cannot be deleted.");
        }

        // System roles (admin, user) can only be deleted by super_admin
        if (in_array($role->slug, RoleManager::all())) {
            abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only super admins can delete system roles.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}
