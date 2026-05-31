<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HoldingController extends Controller
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

        return view('admin.holdings.index', ['users' => $users, 'balanceService' => $this->balanceService]);
    }
}
