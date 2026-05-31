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
        return response()->json([
            'data' => MethodResource::collection(Method::withCount('subMethods')->orderBy('name')->get()),
        ]);
    }

    public function subMethods(Method $method): JsonResponse
    {
        $subMethods = $method->subMethods()
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => SubMethodResource::collection($subMethods),
        ]);
    }

    public function showSubMethod(SubMethod $subMethod): JsonResponse
    {
        abort_if(! $subMethod->is_active, 404);

        return response()->json([
            'data' => new SubMethodResource($subMethod->load('method')),
        ]);
    }
}
