<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        $countries = Country::orderBy('name')
            ->select('id', 'name', 'slug')
            ->get();

        return response()->json(['data' => $countries]);
    }

    public function states(Request $request): JsonResponse
    {
        $request->validate([
            'country_id' => ['required', 'uuid', 'exists:countries,id'],
        ]);

        $states = State::where('country_id', $request->country_id)
            ->orderBy('name')
            ->select('id', 'name', 'slug', 'country_id')
            ->get();

        return response()->json(['data' => $states]);
    }

    public function statesBySlug(string $countrySlug): JsonResponse
    {
        $country = Country::where('slug', $countrySlug)->firstOrFail();

        $states = State::where('country_id', $country->id)
            ->orderBy('name')
            ->select('id', 'name', 'slug', 'country_id')
            ->get();

        return response()->json([
            'data' => $states,
            'country' => ['id' => $country->id, 'name' => $country->name],
        ]);
    }
}