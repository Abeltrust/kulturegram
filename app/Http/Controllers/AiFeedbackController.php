<?php

namespace App\Http\Controllers;

use App\Models\AiFeedbackLog;
use App\Services\MetaAI\AiFeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiFeedbackController extends Controller
{
    protected AiFeedbackService $feedbackService;

    public function __construct(AiFeedbackService $feedbackService)
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * Submit feedback from user or moderator.
     */
    public function store(Request $request)
    {
        $request->validate([
            'feedback_type' => 'required|string|in:moderation_dispute,recommendation_irrelevant,recommendation_inappropriate,event_audience_mismatch,summary_correction,preference_update',
            'target_type' => 'nullable|string|max:60',
            'target_id' => 'nullable|integer',
            'rating' => 'nullable|integer|between:-1,5',
            'reason' => 'nullable|string|max:100',
            'user_correction' => 'nullable|string|max:2000',
            'metadata' => 'nullable|array',
        ]);

        $log = $this->feedbackService->logFeedback(
            Auth::user(),
            $request->input('feedback_type'),
            $request->input('target_type'),
            $request->input('target_id') ? (int) $request->input('target_id') : null,
            $request->input('rating') !== null ? (int) $request->input('rating') : null,
            $request->input('reason'),
            $request->input('user_correction'),
            $request->input('metadata', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you. Your feedback helps Kulturegram improve community and cultural intelligence.',
            'log_id' => $log->id,
        ]);
    }

    /**
     * Admin aggregate metrics and feedback review page.
     */
    public function adminIndex()
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            abort(403);
        }

        $metrics = $this->feedbackService->getAdminMetrics();
        $feedbackLogs = AiFeedbackLog::with('user:id,name,username')
            ->latest()
            ->paginate(20);

        return view('admin.ai-feedback', compact('metrics', 'feedbackLogs'));
    }

    /**
     * Admin updates feedback log review status.
     */
    public function updateStatus(Request $request, AiFeedbackLog $log)
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string|in:logged,reviewed,applied',
        ]);

        $log->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Feedback status updated.');
    }
}
