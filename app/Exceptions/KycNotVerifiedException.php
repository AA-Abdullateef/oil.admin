<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class KycNotVerifiedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error'   => 'KYC_NOT_VERIFIED',
            'kyc_url' => '/api/v1/kyc/submit',
        ], 403);
    }
}