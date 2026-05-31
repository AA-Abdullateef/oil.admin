<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assets = Asset::where('status', Asset::STATUS_ACTIVE)
            ->when($request->type, fn ($query) => $query->where('type', $request->type))
            ->orderBy('name')
            ->get();

        return response()->json(['data' => AssetResource::collection($assets)]);
    }

    public function show(Asset $asset): JsonResponse
    {
        abort_if($asset->status !== Asset::STATUS_ACTIVE, 404);

        return response()->json(['data' => new AssetResource($asset)]);
    }
}
