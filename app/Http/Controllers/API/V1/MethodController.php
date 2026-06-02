<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MethodResource;
use App\Http\Resources\SubMethodResource;
use App\Models\Method;
use App\Models\SubMethod;
use Illuminate\Http\JsonResponse;

class MethodController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success(
            MethodResource::collection(Method::withCount('subMethods')->orderBy('name')->get()),
            'Payment methods retrieved.'
        );
    }

    public function subMethods(Method $method): JsonResponse
    {
        $subMethods = $method->subMethods()
            ->active()
            ->orderBy('name')
            ->get();

        return $this->success(SubMethodResource::collection($subMethods), 'Payment sub-methods retrieved.');
    }

    public function showSubMethod(SubMethod $subMethod): JsonResponse
    {
        abort_if(! $subMethod->is_active, 404);

        return $this->success(new SubMethodResource($subMethod->load('method')), 'Payment sub-method retrieved.');
    }
}
