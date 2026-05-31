<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\AuditLogService;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid credentials.']);
        }

        $user = Auth::user();

        if (! $user->isAdmin()) {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'You do not have admin access.']);
        }

        if ($user->status !== 'active') {
            Auth::logout();
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account is suspended.']);
        }

        $request->session()->regenerate();

        app(AuditLogService::class)->log('admin_login', $user, metadata: [
            'email' => $user->email,
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            app(AuditLogService::class)->log('admin_logout', $user, metadata: [
                'email' => $user->email,
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
