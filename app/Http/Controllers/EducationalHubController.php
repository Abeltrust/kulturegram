<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\CulturalClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EducationalHubController extends Controller
{
    public function index(Request $request)
    {
        $educationalTypes = ['class', 'training', 'ritual', 'wedding', 'ceremony', 'workshop'];

        $query = Event::with('organizer', 'attendees')
            ->whereIn('type', $educationalTypes)
            ->upcoming()
            ->latest('event_date');

        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        $events = $query->paginate(8);

        // Featured cultural video classes (Language, Heritage, Masterclasses)
        $featuredClasses = CulturalClass::with('instructor')
            ->published()
            ->latest()
            ->take(6)
            ->get();

        // Featured experts/hosts (those with most events or attendees)
        // Cache this for 60 minutes as it doesn't change frequently
        $experts = Cache::remember('educational_hub_experts', 3600, function() use ($educationalTypes) {
            return \App\Models\User::whereHas('events', function($q) use ($educationalTypes) {
                $q->whereIn('type', $educationalTypes);
            })->withCount('events')->orderBy('events_count', 'desc')->take(5)->get();
        });

        return view('education.index', compact('events', 'experts', 'featuredClasses'));
    }
}
