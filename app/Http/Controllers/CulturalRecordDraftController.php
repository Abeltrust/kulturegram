<?php

namespace App\Http\Controllers;

use App\Models\Culture;
use App\Models\CulturalRecordDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CulturalRecordDraftController extends Controller
{
    /**
     * List user's cultural heritage drafts.
     */
    public function index()
    {
        $user = Auth::user();
        $drafts = CulturalRecordDraft::where('contributor_id', $user->id)
            ->with(['liveStream:id,title', 'approvedCulture:id,name'])
            ->latest()
            ->paginate(15);

        return view('cultural-hub.drafts.index', compact('drafts'));
    }

    /**
     * Show editor/review page for a draft record.
     */
    public function show(CulturalRecordDraft $draft)
    {
        $user = Auth::user();
        if ($draft->contributor_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized access to this cultural draft.');
        }

        return view('cultural-hub.drafts.show', compact('draft'));
    }

    /**
     * Update draft with custodian enrichments, corrections, and notes.
     */
    public function update(Request $request, CulturalRecordDraft $draft)
    {
        $user = Auth::user();
        if ($draft->contributor_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:80',
            'region' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:100',
            'extracted_story' => 'required|string',
            'ai_summary' => 'nullable|string',
            'custodian_notes' => 'nullable|string',
            'unanswered_questions' => 'nullable|array',
            'unanswered_questions.*' => 'string|max:255',
        ]);

        $draft->update([
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'region' => $request->input('region'),
            'language' => $request->input('language'),
            'extracted_story' => $request->input('extracted_story'),
            'ai_summary' => $request->input('ai_summary'),
            'custodian_notes' => $request->input('custodian_notes'),
            'unanswered_questions' => $request->input('unanswered_questions', []),
            'status' => 'reviewed',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cultural draft updated successfully.',
                'draft' => $draft->fresh(),
            ]);
        }

        return redirect()->route('cultural-hub.drafts.show', $draft->id)
            ->with('success', 'Cultural draft saved successfully.');
    }

    /**
     * Submit draft to community moderation queue.
     */
    public function submitToModeration(CulturalRecordDraft $draft)
    {
        $user = Auth::user();
        if ($draft->contributor_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        $draft->update([
            'status' => 'submitted_to_moderation',
        ]);

        return redirect()->route('cultural-hub.drafts.show', $draft->id)
            ->with('success', 'Draft submitted to Kulturegram archive curators for review and formal publication.');
    }

    /**
     * Delete/reject a draft.
     */
    public function destroy(CulturalRecordDraft $draft)
    {
        $user = Auth::user();
        if ($draft->contributor_id !== $user->id && !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        $draft->delete();

        return redirect()->route('cultural-hub.drafts.index')
            ->with('success', 'Cultural draft removed.');
    }

    /**
     * Admin/Curator approves draft into the permanent public Culture archive.
     */
    public function approve(Request $request, CulturalRecordDraft $draft)
    {
        $user = Auth::user();
        if (!$user->is_admin) {
            abort(403, 'Administrator access required.');
        }

        // Convert draft to approved public Culture record
        $culture = Culture::create([
            'name' => $draft->title,
            'region' => $draft->region ?? 'Community Archive',
            'category' => $draft->category ?? 'Living Heritage',
            'language' => $draft->language ?? 'Local Dialect',
            'description' => $draft->ai_summary ?? \Illuminate\Support\Str::limit($draft->extracted_story, 300),
            'raw_story' => $draft->extracted_story,
            'submitted_by' => $draft->contributor_id,
            'status' => 'approved',
            'is_ai_assisted' => true,
            'ai_metadata' => [
                'origin' => 'livestream_discussion_archive',
                'live_stream_id' => $draft->live_stream_id,
                'draft_id' => $draft->id,
                'custodian_notes' => $draft->custodian_notes,
                'unanswered_questions' => $draft->unanswered_questions,
                'verified_at' => now()->toIso8601String(),
            ],
        ]);

        $draft->update([
            'status' => 'approved',
            'approved_culture_id' => $culture->id,
        ]);

        return redirect()->back()->with('success', "Draft approved and published to public cultural archive as [{$culture->name}].");
    }
}
