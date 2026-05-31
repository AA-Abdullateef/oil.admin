<?php

namespace App\Http\Middleware;

use App\Support\Roles\RoleManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login')
                ->with('error', 'Please sign in to access the admin panel.');
        }

        $user = Auth::user();

        if (! $user->isAdmin()) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'You do not have admin access.']);
        }

        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account has been suspended.']);
        }

        return $next($request);
    }
}