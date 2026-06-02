<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success(mixed $data = null, string $message = 'Request successful.', int $status = 200, array $extra = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            ...$extra,
        ], $status);
    }

    protected function error(string $message, int $status = 400, array $extra = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            ...$extra,
        ], $status);
    }
}
