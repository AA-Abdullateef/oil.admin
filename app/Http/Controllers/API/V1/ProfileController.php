<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile()->with('country', 'state')->firstOrFail();

        return response()->json(['data' => new ProfileResource($profile)]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->profile ?? $request->user()->profile()->create();

        $profile->update($request->validated());

        return response()->json([
            'message' => 'Profile updated.',
            'data'    => new ProfileResource($profile->load('country', 'state')),
        ]);
    }
}