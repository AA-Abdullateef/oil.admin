<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->with(['asset', 'method', 'subMethod'])
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->direction, fn ($q) => $q->where('direction', $request->direction))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return $this->success(
            TransactionResource::collection($transactions),
            'Transactions retrieved.',
            200,
            [
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total(),
                ],
            ]
        );
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        return $this->success(
            new TransactionResource($transaction->load(['asset', 'method', 'subMethod'])),
            'Transaction retrieved.'
        );
    }
}
