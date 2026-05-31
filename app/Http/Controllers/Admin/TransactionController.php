<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with(['user', 'asset', 'method', 'subMethod'])
            ->when($request->type, fn ($query) => $query->where('type', $request->type))
            ->when($request->direction, fn ($query) => $query->where('direction', $request->direction))
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->when($request->search, fn ($query) => $query
                ->where('reference', 'like', "%{$request->search}%")
                ->orWhereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.transactions.index', compact('transactions'));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['user', 'asset', 'method', 'subMethod', 'updatedBy', 'depositProof', 'withdrawalProof']);

        return view('admin.transactions.show', compact('transaction'));
    }
}
