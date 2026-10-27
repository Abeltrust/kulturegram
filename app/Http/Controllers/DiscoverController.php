<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Culture;
use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;

class DiscoverController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'stories');
        $search = $request->get('search');

        $data = [];

        switch ($tab) {
            case 'stories':
                $query = Post::with(['user', 'taps', 'resonances'])
                    ->public()
                    ->where('taps_count', '>', 10) // Popular stories
                    ->latest();

                if ($search) {
                    $query->where('content', 'like', "%{$search}%");
                }

                $data = $query->limit(12)->get();
                break;

            case 'cultures':
                $query = Culture::with(['submitter', 'lockIns', 'resonances'])
                    ->approved()
                    ->orderBy('locked_in_count', 'desc');

                if ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                }

                $data = $query->limit(12)->get();
                break;

            case 'people':
                $query = User::orderBy('taps_received', 'desc');

                if (auth()->check()) {
                    $query->where('id', '!=', auth()->id());
                }

                if ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%");
                }

                $data = $query->limit(12)->get();
                break;

            case 'places':
                $query = Event::with('organizer')
                    ->upcoming()
                    ->orderBy('attendees_count', 'desc');

                if ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                }

                $data = $query->limit(12)->get();
                break;
        }

        // Trending stats
        $trendingStats = [
            'taps_this_week' => Post::where('created_at', '>=', now()->subWeek())->sum('taps_count'),
            'new_connections' => auth()->check() ? auth()->user()->locked_in_count : 0,
            'cultures_featured' => Culture::where('status', 'featured')->count(),
        ];

        $opportunities = null;
        if (auth()->check()) {
            $opportunityService = app(\App\Services\MetaAI\CulturalOpportunityService::class);
            $opportunities = $opportunityService->getOpportunities(auth()->user());
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Discover-Tab')) {
            return response()->json([
                'success' => true,
                'tab' => $tab,
                'search' => $search,
                'html' => view('discover.partials.grid', compact('data', 'tab'))->render(),
            ]);
        }

        return view('discover.index', compact('data', 'tab', 'search', 'trendingStats', 'opportunities'));
    }
}