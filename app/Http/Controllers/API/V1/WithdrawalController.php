<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Withdrawal\StoreWithdrawalRequest;
use App\Http\Resources\WithdrawalResource;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(private readonly WithdrawalService $withdrawalService) {}

    public function index(Request $request): JsonResponse
    {
        $withdrawals = Withdrawal::with(['asset', 'method', 'subMethod', 'withdrawalProof'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->success(WithdrawalResource::collection($withdrawals), 'Withdrawals retrieved.');
    }

    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        $withdrawal = $this->withdrawalService->request($request->user(), $request->validated());

        return $this->success(new WithdrawalResource($withdrawal), 'Withdrawal request submitted. Pending approval.', 201);
    }

    public function show(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        abort_if($withdrawal->user_id !== $request->user()->id, 403);

        return $this->success(
            new WithdrawalResource($withdrawal->load(['asset', 'method', 'subMethod', 'withdrawalProof'])),
            'Withdrawal retrieved.'
        );
    }
}
