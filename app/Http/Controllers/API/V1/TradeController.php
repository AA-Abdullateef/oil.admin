<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Trade\BuyTradeRequest;
use App\Http\Requests\API\Trade\SellTradeRequest;
use App\Http\Resources\TransactionResource;
use App\Services\TradeService;
use Illuminate\Http\JsonResponse;

class TradeController extends Controller
{
    public function __construct(private readonly TradeService $tradeService) {}

    public function buy(BuyTradeRequest $request): JsonResponse
    {
        $rows = $this->tradeService->buy($request->user(), $request->validated());

        return $this->success(
            [
                'debit' => new TransactionResource($rows['debit']),
                'credit' => new TransactionResource($rows['credit']),
            ],
            'Trade recorded successfully.',
            201
        );
    }

    public function sell(SellTradeRequest $request): JsonResponse
    {
        $rows = $this->tradeService->sell($request->user(), $request->validated());

        return $this->success(
            [
                'debit' => new TransactionResource($rows['debit']),
                'credit' => new TransactionResource($rows['credit']),
            ],
            'Trade recorded successfully.',
            201
        );
    }
}
