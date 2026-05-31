<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\BalanceService;
use App\Support\Roles\RoleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->search, fn ($query) => $query
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['profile.country', 'profile.state', 'roles']);
        $recentTransactions = $user->transactions()->with(['asset', 'method'])->latest()->limit(10)->get();
        $balances = $this->balanceService->allFor($user);

        return view('admin.users.show', compact('user', 'recentTransactions', 'balances'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended,banned'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->update($data);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User removed.');
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role_id' => ['required', 'uuid', 'exists:roles,id']]);

        $role = Role::findOrFail($request->role_id);

        if (RoleManager::isImmutable($role->slug) && ! auth()->user()->isSuperAdmin()) {
            return back()->with('error', "The '{$role->name}' role can only be assigned by a super admin.");
        }

        $alreadyAssigned = $user->roles()->whereKey($role->id)->exists();
        $user->roles()->syncWithoutDetaching($role->id);

        if (! $alreadyAssigned) {
            app(AuditLogService::class)->log('user_role_attached', $user, metadata: [
                'role_id' => $role->id,
                'role_slug' => $role->slug,
            ]);
        }

        return back()->with('success', "Role '{$role->name}' assigned to {$user->name}.");
    }

    public function removeRole(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role_id' => ['required', 'uuid', 'exists:roles,id']]);

        $role = Role::findOrFail($request->role_id);

        if (RoleManager::isImmutable($role->slug)) {
            return back()->with('error', "The '{$role->name}' role is permanent and cannot be removed.");
        }

        if ($user->isSuperAdmin() && ! auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'You cannot modify the roles of a super admin.');
        }

        $wasAssigned = $user->roles()->whereKey($role->id)->exists();
        $user->roles()->detach($role->id);

        if ($wasAssigned) {
            app(AuditLogService::class)->log('user_role_detached', $user, metadata: [
                'role_id' => $role->id,
                'role_slug' => $role->slug,
            ]);
        }

        return back()->with('success', "Role '{$role->name}' removed from {$user->name}.");
    }
}
