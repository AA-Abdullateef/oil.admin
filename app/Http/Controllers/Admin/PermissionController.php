<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\AuditLogService;
use App\Support\Roles\RoleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::withCount('roles')->orderBy('slug')->paginate(30);

        return view('admin.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'unique:permissions,slug'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        Permission::create($data);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created.');
    }

    public function edit(Permission $permission): View
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $permission->update($data);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        // Guard: refuse if this slug is referenced in RoleManager
        $allManaged = collect(RoleManager::permissions())->flatten()->unique();

        if ($allManaged->contains($permission->slug)) {
            return back()->with('error',
                "'{$permission->slug}' is a core system permission and cannot be deleted."
            );
        }

        $roleIds = $permission->roles()->pluck('roles.id')->all();

        $permission->roles()->detach();
        app(AuditLogService::class)->log('permission_roles_detached', $permission, metadata: [
            'role_ids' => $roleIds,
        ]);

        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted.');
    }
}
