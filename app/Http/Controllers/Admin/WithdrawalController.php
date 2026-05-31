<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawalService) {}

    public function index(Request $request): View
    {
        $withdrawals = Withdrawal::with(['user', 'asset', 'method', 'subMethod', 'withdrawalProof'])
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
            'pending' => Withdrawal::where('status', Transaction::STATUS_PENDING)->count(),
            'processing' => Withdrawal::where('status', Transaction::STATUS_PROCESSING)->count(),
            'cancelled' => Withdrawal::where('status', Transaction::STATUS_CANCELLED)->count(),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'counts'));
    }

    public function show(Withdrawal $withdrawal): View
    {
        $withdrawal->load(['user', 'asset', 'method', 'subMethod', 'withdrawalProof', 'updatedBy']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function process(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if ($withdrawal->status !== Transaction::STATUS_PENDING) {
            return back()->with('error', 'Only pending withdrawals can be processed.');
        }

        $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
            'payment_evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $evidencePath = $request->file('payment_evidence')
            ->store("withdrawal-evidence/{$withdrawal->id}", 'private');

        $this->withdrawalService->process($withdrawal, auth()->user(), [
            'admin_notes' => $request->admin_notes,
            'payment_evidence' => $evidencePath,
        ]);

        return back()->with('success', "Withdrawal {$withdrawal->reference} is now processing and evidence was uploaded.");
    }

    public function cancel(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']]);

        if ($withdrawal->status !== Transaction::STATUS_PENDING) {
            return back()->with('error', 'Only pending withdrawals can be cancelled.');
        }

        $this->withdrawalService->cancel($withdrawal, auth()->user(), $request->reason);

        return back()->with('success', 'Withdrawal cancelled.');
    }
}
