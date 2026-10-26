<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\UserOriginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Suggestions for user-added local areas in countries without official LGA data.
     */
    public function areas(Request $request, Country $country, UserOriginService $origin): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        return response()->json($origin->customAreas($country, $search !== '' ? mb_substr($search, 0, 60) : null));
    }
}
