<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * Public settings — no auth required.
     * Returns payment details, platform info, and deposit/withdrawal limits.
     * Grouped by setting group for easy frontend consumption.
     */
    public function public(): JsonResponse
    {
        return response()->json([
            'data' => $this->settings->public(),
        ]);
    }
}