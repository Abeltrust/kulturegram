<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Culture;
use App\Models\User;
use App\Models\Community;
use App\Models\Event;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending_review');

        $query = Culture::with('submitter')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $cultures = $query->paginate(15);

        $submittedDrafts = \App\Models\CulturalRecordDraft::where('status', 'submitted_to_moderation')
            ->with('contributor:id,name,username')
            ->latest()
            ->get();

        $counts = [
            'pending' => Culture::where('status', 'pending_review')->count(),
            'approved' => Culture::where('status', 'approved')->count(),
            'featured' => Culture::where('status', 'featured')->count(),
            'pending_drafts' => $submittedDrafts->count(),
            'total_users' => User::count(),
            'total_events' => Event::count(),
            'total_communities' => Community::count(),
        ];

        return view('admin.cultures', compact('cultures', 'status', 'counts', 'submittedDrafts'));
    }

    public function approve(Culture $culture)
    {
        $culture->status = 'approved';
        $culture->save();

        if ($culture->submitter) {
            $culture->submitter->notifications()->create([
                'type' => 'collaboration',
                'title' => 'Cultural Story Approved!',
                'message' => "Your cultural story \"{$culture->name}\" has been approved and published to Kulturegram.",
                'data' => ['culture_id' => $culture->id],
            ]);
        }

        return back()->with('success', "Culture \"{$culture->name}\" approved and published successfully!");
    }

    public function feature(Culture $culture)
    {
        $culture->status = $culture->status === 'featured' ? 'approved' : 'featured';
        $culture->save();

        $action = $culture->status === 'featured' ? 'featured' : 'unfeatured';
        return back()->with('success', "Culture \"{$culture->name}\" is now {$action}.");
    }

    public function destroy(Culture $culture)
    {
        $title = $culture->name;
        $submitter = $culture->submitter;

        $culture->delete();

        if ($submitter) {
            $submitter->notifications()->create([
                'type' => 'collaboration',
                'title' => 'Cultural Submission Update',
                'message' => "Your submission \"{$title}\" could not be approved and has been removed.",
                'data' => [],
            ]);
        }

        return back()->with('success', "Submission \"{$title}\" was rejected and removed.");
    }
}
