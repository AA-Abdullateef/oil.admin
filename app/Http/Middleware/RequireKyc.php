<?php

namespace App\Http\Middleware;

use App\Services\KycService;
use Closure;
use Illuminate\Http\Request;

class RequireKyc
{
    public function __construct(private readonly KycService $kycService) {}

    public function handle(Request $request, Closure $next)
    {
        $this->kycService->assertVerified($request->user());

        return $next($request);
    }
}