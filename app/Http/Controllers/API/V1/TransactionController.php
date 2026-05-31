<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->with(['asset', 'method', 'subMethod'])
            ->when($request->type,     fn ($q) => $q->where('type', $request->type))
            ->when($request->direction, fn ($q) => $q->where('direction', $request->direction))
            ->when($request->status,   fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }

    public function show(Request $request, \App\Models\Transaction $transaction): JsonResponse
    {
        abort_if($transaction->user_id !== $request->user()->id, 403);

        return response()->json([
            'data' => new TransactionResource($transaction->load(['asset', 'method', 'subMethod'])),
        ]);
    }
}
