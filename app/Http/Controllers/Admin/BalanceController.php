<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalanceController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->search, fn ($query) => $query
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.balances.index', ['users' => $users, 'balanceService' => $this->balanceService]);
    }

    public function show(User $user): View
    {
        $transactions = $user->transactions()->with(['asset', 'method'])->latest()->paginate(20);
        $balances = $this->balanceService->getAllBalances($user);

        return view('admin.balances.show', compact('user', 'transactions', 'balances'));
    }
}
