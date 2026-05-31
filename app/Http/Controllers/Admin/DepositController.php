<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Services\DepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function __construct(private readonly DepositService $depositService) {}

    public function index(Request $request): View
    {
        $deposits = Deposit::with(['user', 'asset', 'method', 'subMethod', 'depositProof'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->search, fn ($query) => $query
                ->where('reference', 'like', "%{$request->search}%")
                ->orWhereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending' => Deposit::where('status', Transaction::STATUS_PENDING)->count(),
            'completed' => Deposit::where('status', Transaction::STATUS_COMPLETED)->count(),
            'cancelled' => Deposit::where('status', Transaction::STATUS_CANCELLED)->count(),
        ];

        return view('admin.deposits.index', compact('deposits', 'counts'));
    }

    public function show(Deposit $deposit): View
    {
        $deposit->load(['user', 'asset', 'method', 'subMethod', 'depositProof', 'updatedBy']);

        return view('admin.deposits.show', compact('deposit'));
    }

    public function complete(Deposit $deposit): RedirectResponse
    {
        if ($deposit->status !== Transaction::STATUS_PENDING) {
            return back()->with('error', 'Only pending deposits can be completed.');
        }

        $this->depositService->complete($deposit, auth()->user());

        return back()->with('success', 'Deposit completed.');
    }

    public function cancel(Request $request, Deposit $deposit): RedirectResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        if ($deposit->status !== Transaction::STATUS_PENDING) {
            return back()->with('error', 'Only pending deposits can be cancelled.');
        }

        $this->depositService->cancel($deposit, auth()->user(), $request->reason);

        return back()->with('success', 'Deposit cancelled.');
    }
}
