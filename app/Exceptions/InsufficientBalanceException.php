<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientBalanceException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error'   => 'INSUFFICIENT_BALANCE',
        ], 422);
    }
}