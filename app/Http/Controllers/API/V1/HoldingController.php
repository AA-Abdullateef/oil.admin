<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Http\Resources\TransactionResource;
use App\Models\Asset;
use App\Models\Transaction;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HoldingController extends Controller
{
    public function __construct(private readonly BalanceService $balanceService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->balanceService->getAllBalances($request->user())->map(fn (array $row) => [
                'asset' => new AssetResource($row['asset']),
                'quantity' => number_format((float) $row['quantity'], 8, '.', ''),
                'current_value' => number_format((float) $row['value'], 8, '.', ''),
            ])->values(),
        ]);
    }

    public function show(Request $request, Asset $asset): JsonResponse
    {
        $quantity = $this->balanceService->getBalance($request->user(), $asset);

        return response()->json([
            'data' => [
                'asset' => new AssetResource($asset),
                'quantity' => number_format((float) $quantity, 8, '.', ''),
                'current_value' => number_format((float) bcmul($quantity, (string) $asset->current_price, 8), 8, '.', ''),
            ],
        ]);
    }

    public function trades(Request $request): JsonResponse
    {
        $transactions = Transaction::with('asset')
            ->where('user_id', $request->user()->id)
            ->whereIn('type', [Transaction::TYPE_BUY, Transaction::TYPE_SELL])
            ->latest()
            ->paginate(20);

        return response()->json(['data' => TransactionResource::collection($transactions)]);
    }
}
