<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Deposit\StoreDepositRequest;
use App\Http\Resources\DepositResource;
use App\Models\Deposit;
use App\Services\DepositService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function __construct(private readonly DepositService $depositService) {}

    public function index(Request $request): JsonResponse
    {
        $deposits = Deposit::with(['asset', 'method', 'subMethod', 'depositProof'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json(['data' => DepositResource::collection($deposits)]);
    }

    public function store(StoreDepositRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('proof')) {
            $data['proof'] = $request->file('proof')->store('proofs', 'public');
        }

        $deposit = $this->depositService->initiate($request->user(), $data);

        return response()->json([
            'message' => 'Deposit submitted. Awaiting completion.',
            'data' => new DepositResource($deposit),
        ], 201);
    }

    public function show(Request $request, Deposit $deposit): JsonResponse
    {
        abort_if($deposit->user_id !== $request->user()->id, 403);

        return response()->json(['data' => new DepositResource($deposit->load(['asset', 'method', 'subMethod', 'depositProof']))]);
    }
}
