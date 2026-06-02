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
        $profile = $request->user()->profile()->with('user', 'country', 'state')->firstOrFail();

        return $this->success(new ProfileResource($profile), 'Profile retrieved.');
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $profile = $request->user()->profile ?? $request->user()->profile()->create();
        $validated = $request->validated();
        $userData = collect($validated)->only(['first_name', 'last_name', 'email', 'phone'])->all();
        $profileData = collect($validated)->except(['first_name', 'last_name', 'email', 'phone'])->all();

        if ($userData !== []) {
            $request->user()->update($userData);
        }

        $profile->update($profileData);

        return $this->success(
            new ProfileResource($profile->load('user', 'country', 'state')),
            'Profile updated.'
        );
    }
}
