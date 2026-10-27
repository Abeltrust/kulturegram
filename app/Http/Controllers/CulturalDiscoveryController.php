<?php

namespace App\Http\Controllers;

use App\Services\MetaAI\CulturalConnectionRecommendationService;
use App\Services\MetaAI\EventDiscoveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CulturalDiscoveryController extends Controller
{
    /**
     * Get personalized cultural recommendations for current user
     */
    public function recommendations(
        Request $request,
        CulturalConnectionRecommendationService $connectionService,
        EventDiscoveryService $eventService
    ) {
        $user = Auth::user();

        if ($connectionService->isOptedOut($user)) {
            return response()->json([
                'success' => true,
                'opted_out' => true,
                'users' => [],
                'communities' => [],
                'events' => [],
                'message' => 'Personalized recommendations are currently turned off in your privacy settings.',
            ]);
        }

        $users = $connectionService->recommendUsers($user, 4);
        $communities = $connectionService->recommendCommunities($user, 4);
        $events = $eventService->recommendEvents($user, 3);

        $userRecs = $users['recommendations'] ?? [];
        $communityRecs = $communities['recommendations'] ?? [];
        $eventRecs = $events['recommendations'] ?? [];

        return response()->json([
            'success' => true,
            'opted_out' => false,
            'users' => $userRecs,
            'communities' => $communityRecs,
            'events' => $eventRecs,
            'raw' => [
                'users' => $users,
                'communities' => $communities,
                'events' => $events,
            ],
        ]);
    }

    /**
     * Dismiss a recommended user, community, or event
     */
    public function dismiss(Request $request, CulturalConnectionRecommendationService $connectionService)
    {
        $request->validate([
            'type' => 'required|in:user,community,event',
            'id' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $dismissal = $connectionService->dismiss(
            Auth::user(),
            $request->input('type'),
            $request->input('id'),
            $request->input('reason', 'dismissed_by_user')
        );

        return response()->json([
            'success' => true,
            'dismissed' => $dismissal,
        ]);
    }

    /**
     * Toggle AI recommendation opt-in / opt-out
     */
    public function toggleOptIn(Request $request, CulturalConnectionRecommendationService $connectionService)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $status = $connectionService->toggleOptIn(Auth::user(), $request->boolean('enabled'));

        return response()->json([
            'success' => true,
            'recommendations_enabled' => $status,
            'message' => $status 
                ? 'Cultural recommendations enabled based on your declared interests.' 
                : 'Cultural recommendations disabled. Your profile will not be matched.',
        ]);
    }
}
