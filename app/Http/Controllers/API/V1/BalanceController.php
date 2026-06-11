<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EarningResource;
use App\Http\Resources\TransactionResource;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success([
            'balances' => $this->balanceService
                ->getAllBalances($request->user())
                ->map(fn (array $row) => [
                    'asset' => [
                        'id' => $row['asset']->id,
                        'symbol' => $row['asset']->symbol,
                        'name' => $row['asset']->name,
                        'type' => $row['asset']->type,
                    ],
                    'quantity' => number_format((float) $row['quantity'], 8, '.', ''),
                    'value' => number_format((float) $row['value'], 8, '.', ''),
                ])
                ->values(),
        ], 'Balances retrieved.');
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->with(['asset', 'method'])
            ->latest()
            ->paginate(20);

        return $this->success(
            TransactionResource::collection($transactions),
            'Balance transactions retrieved.',
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

    public function earnings(Request $request): JsonResponse
    {
        $earnings = $request->user()
            ->earnings()
            ->with(['asset'])
            ->latest()
            ->paginate(20);

        return $this->success(
            EarningResource::collection($earnings),
            'Earning history retrieved.',
            200,
            [
                'meta' => [
                    'current_page' => $earnings->currentPage(),
                    'last_page' => $earnings->lastPage(),
                    'total' => $earnings->total(),
                ],
            ]
        );
    }
}