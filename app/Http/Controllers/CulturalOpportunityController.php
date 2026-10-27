<?php

namespace App\Http\Controllers;

use App\Services\MetaAI\CulturalOpportunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CulturalOpportunityController extends Controller
{
    protected CulturalOpportunityService $opportunityService;

    public function __construct(CulturalOpportunityService $opportunityService)
    {
        $this->opportunityService = $opportunityService;
    }

    /**
     * Get opportunity recommendations for authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        $data = $this->opportunityService->getOpportunities($user);

        return response()->json($data);
    }

    /**
     * Dismiss an opportunity recommendation.
     */
    public function dismiss(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:preservation_project,culture,community,event,user',
            'target_id' => 'required|integer',
        ]);

        $type = $request->input('type');
        if ($type === 'preservation_project') {
            $type = 'culture';
        }

        $this->opportunityService->dismissOpportunity(
            Auth::user(),
            $type,
            (int) $request->input('target_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation dismissed.',
        ]);
    }

    /**
     * Update cultural interests and collaboration preferences.
     */
    public function updatePreferences(Request $request)
    {
        $request->validate([
            'cultural_interests' => 'nullable|array',
            'cultural_interests.*' => 'string|max:80',
            'opt_in_collaboration' => 'required|boolean',
            'opt_in_recommendations' => 'nullable|boolean',
        ]);

        $success = $this->opportunityService->updatePreferences(
            Auth::user(),
            $request->input('cultural_interests', []),
            (bool) $request->input('opt_in_collaboration', false),
            $request->has('opt_in_recommendations') ? (bool) $request->input('opt_in_recommendations') : null
        );

        return response()->json([
            'success' => $success,
            'message' => 'Cultural preferences updated successfully.',
            'user' => Auth::user()->fresh(),
        ]);
    }
}
