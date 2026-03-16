<?php

namespace App\Http\Controllers;

use App\Models\Culture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CulturalHubController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');

        $query = Culture::with(['submitter', 'lockIns.user', 'resonances.user'])
            ->where(function ($q) {
                $q->where('status', 'approved')
                    ->orWhere('status', 'featured');
                if (Auth::check()) {
                    $q->orWhere('submitted_by', Auth::id());
                }
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category !== 'all') {
            $query->where('category', $category);
        }

        $cultures = $query->inRandomOrder()->paginate(18);

        $categories = [
            'all' => 'All Cultures',
            'festivals' => 'Festivals',
            'traditions' => 'Traditions',
            'music' => 'Music & Arts',
            'heritage' => 'Heritage Sites',
            'crafts' => 'Traditional Crafts',
            'language' => 'Language & Literature',
        ];

        return view('cultural-hub.index', compact('cultures', 'categories', 'category'));
    }

    public function edit(Culture $culture)
    {
        if ($culture->submitted_by !== Auth::id()) {
            abort(403, 'You are not the guardian of this story.');
        }

        $categories = [
            'Traditional Crafts',
            'Oral Traditions',
            'Music & Dance',
            'Culinary Heritage',
            'Religious Practices',
            'Festivals & Ceremonies',
            'Language & Literature',
            'Architecture & Design',
            'Agricultural Practices',
            'Healing Traditions',
            'Social Customs',
            'Art & Aesthetics',
            'Games & Sports',
            'Clothing & Textiles'
        ];

        $licenses = [
            'Public Domain',
            'CC0 (Creative Commons Zero)',
            'CC BY (Attribution)',
            'CC BY-SA (Attribution-ShareAlike)',
            'CC BY-NC (Attribution-NonCommercial)',
            'CC BY-ND (Attribution-NoDerivs)',
            'All Rights Reserved'
        ];

        return view('cultural-hub.edit', compact('culture', 'categories', 'licenses'));
    }

    public function show(Culture $culture)
    {
        if ($culture->status !== 'approved' && $culture->status !== 'featured') {
            if (!Auth::check() || $culture->submitted_by !== Auth::id()) {
                abort(404);
            }
        }

        $culture->load(['submitter', 'lockIns', 'resonances']);

        return view('cultural-hub.show', compact('culture'));
    }

    public function create()
    {
        $categories = [
            'Traditional Crafts',
            'Oral Traditions',
            'Music & Dance',
            'Culinary Heritage',
            'Religious Practices',
            'Festivals & Ceremonies',
            'Language & Literature',
            'Architecture & Design',
            'Agricultural Practices',
            'Healing Traditions',
            'Social Customs',
            'Art & Aesthetics',
            'Games & Sports',
            'Clothing & Textiles'
        ];

        $licenses = [
            'Public Domain',
            'CC0 (Creative Commons Zero)',
            'CC BY (Attribution)',
            'CC BY-SA (Attribution-ShareAlike)',
            'CC BY-NC (Attribution-NonCommercial)',
            'CC BY-ND (Attribution-NoDerivs)',
            'All Rights Reserved'
        ];

        return view('cultural-hub.create', compact('categories', 'licenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'image' => 'nullable|image|max:10240',
            'images.*' => 'nullable|image|max:10240',
            'video_url' => 'nullable|url',
            'video_file' => 'nullable|file|mimes:mp4,mov,ogg,qt|max:51200',
            'video_description' => 'nullable|string|max:1000',
            'audio_url' => 'nullable|url',
            'audio_file' => 'nullable|file|mimes:mp3,wav,aac|max:20480',
            'audio_description' => 'nullable|string|max:1000',
            'license_type' => 'nullable|string',
            'license_credit' => 'nullable|string|max:255',
        ]);

        // Custom word count validation for main description
        $wordCount = str_word_count(strip_tags($request->description));
        if ($wordCount < 20) {
            return back()->withInput()->withErrors(['description' => 'Description must be at least 20 words to ensure quality.']);
        }

        $culture = new Culture($request->except(['video_file', 'audio_file', 'images', 'image']));
        $culture->submitted_by = Auth::id();
        $culture->status = 'pending_review';

        // Process Video Embed URL
        if ($request->video_url) {
            $culture->video_url = $this->convertToEmbedUrl($request->video_url);
        }

        // Handle Primary Image with optimization
        if ($request->hasFile('image')) {
            $culture->image = $this->optimizeAndStore($request->file('image'), 'cultures');
        }

        // Handle Multiple Gallery Images (Max 5)
        if ($request->hasFile('images')) {
            $gallery = [];
            foreach (array_slice($request->file('images'), 0, 5) as $file) {
                $gallery[] = $this->optimizeAndStore($file, 'cultures/gallery');
            }
            $culture->media_files = $gallery;
        }

        // Handle Video File
        if ($request->hasFile('video_file')) {
            $culture->video_path = $request->file('video_file')->store('cultures/videos', 'public');
        }

        // Handle Audio File
        if ($request->hasFile('audio_file')) {
            $culture->audio_path = $request->file('audio_file')->store('cultures/audios', 'public');
        }

        $culture->save();

        // Update user's cultures contributed count
        Auth::user()->increment('cultures_contributed');

        return redirect()->route('cultural-hub.index')
            ->with('success', 'Culture submitted successfully! It will be reviewed before publication.');
    }

    public function update(Request $request, Culture $culture)
    {
        if ($culture->submitted_by !== Auth::id()) {
            abort(403, 'You are not the guardian of this story.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'image' => 'nullable|image|max:10240',
            'images.*' => 'nullable|image|max:10240',
            'video_url' => 'nullable|url',
            'video_description' => 'nullable|string|max:1000',
            'audio_url' => 'nullable|url',
            'audio_description' => 'nullable|string|max:1000',
            'license_type' => 'nullable|string',
            'license_credit' => 'nullable|string|max:255',
        ]);

        $culture->fill($request->except(['video_file', 'audio_file', 'images', 'image']));

        // Process Video Embed URL
        if ($request->video_url && $request->video_url !== $culture->getOriginal('video_url')) {
            $culture->video_url = $this->convertToEmbedUrl($request->video_url);
        }

        // Handle Primary Image with optimization
        if ($request->hasFile('image')) {
            $culture->image = $this->optimizeAndStore($request->file('image'), 'cultures');
        }

        // Handle Multiple Gallery Images (Max 5)
        if ($request->hasFile('images')) {
            $gallery = [];
            foreach (array_slice($request->file('images'), 0, 5) as $file) {
                $gallery[] = $this->optimizeAndStore($file, 'cultures/gallery');
            }
            $culture->media_files = $gallery;
        }

        $culture->save();

        return redirect()->route('cultural-hub.show', $culture->id)
            ->with('success', 'Legend updated successfully.');
    }

    public function destroy(Culture $culture)
    {
        if ($culture->submitted_by !== Auth::id()) {
            abort(403, 'You cannot dissolve a story you did not enshrine.');
        }

        $culture->delete();

        // Optional: decrement the count
        Auth::user()->decrement('cultures_contributed');

        return redirect()->route('cultural-hub.index')
            ->with('success', 'The story has been dissolved into the mists of time.');
    }

    private function convertToEmbedUrl($url)
    {
        if (str_contains($url, 'youtube.com/') || str_contains($url, 'youtu.be/')) {
            $id = '';
            if (str_contains($url, 'v=')) {
                parse_str(parse_url($url, PHP_URL_QUERY), $vars);
                $id = $vars['v'] ?? '';
            } elseif (str_contains($url, 'youtu.be/')) {
                $id = ltrim(parse_url($url, PHP_URL_PATH), '/');
            }
            return $id ? "https://www.youtube.com/embed/{$id}" : $url;
        }

        if (str_contains($url, 'vimeo.com/')) {
            $id = ltrim(parse_url($url, PHP_URL_PATH), '/');
            return $id ? "https://player.vimeo.com/video/{$id}" : $url;
        }

        return $url;
    }

    private function optimizeAndStore($file, $directory)
    {
        $path = $file->hashName($directory);
        $fullPath = storage_path('app/public/' . $path);

        // Ensure directory exists
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $image = null;
        $extension = strtolower($file->getClientOriginalExtension());

        // Check if GD functions are available
        $hasGD = function_exists('imagecreatefromjpeg');

        if ($hasGD) {
            if ($extension === 'jpg' || $extension === 'jpeg') {
                $image = @imagecreatefromjpeg($file->getRealPath());
            } elseif ($extension === 'png') {
                $image = @imagecreatefrompng($file->getRealPath());
            } elseif ($extension === 'webp') {
                $image = @imagecreatefromwebp($file->getRealPath());
            }
        }

        if ($image) {
            $width = imagesx($image);
            $height = imagesy($image);
            $maxDim = 1200;

            if ($width > $maxDim || $height > $maxDim) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDim;
                    $newHeight = $maxDim / $ratio;
                } else {
                    $newHeight = $maxDim;
                    $newWidth = $maxDim * $ratio;
                }
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $newImage;
            }

            // Save as WebP for best quality/size ratio
            $webpPath = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $path);
            $fullWebpPath = storage_path('app/public/' . $webpPath);
            imagewebp($image, $fullWebpPath, 80);
            imagedestroy($image);

            return $webpPath;
        }

        return $file->store($directory, 'public');
    }

    public function lockin(Culture $culture)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Culture::class)
            ->where('interactable_id', $culture->id)
            ->where('type', 'lockin')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $culture->decrement('locked_in_count');
            $lockedIn = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Culture::class,
                'interactable_id' => $culture->id,
                'type' => 'lockin',
            ]);
            $culture->increment('locked_in_count');
            $lockedIn = true;

            // Create notification
            if ($culture->submitted_by !== $user->id) {
                $culture->submitter->notifications()->create([
                    'from_user_id' => $user->id,
                    'type' => 'lockin',
                    'title' => 'New Lock-In',
                    'message' => $user->name . ' locked-in to ' . $culture->name,
                    'data' => ['culture_id' => $culture->id],
                ]);
            }
        }

        return response()->json([
            'lockedIn' => $lockedIn,
            'count' => $culture->fresh()->locked_in_count,
        ]);
    }

    public function resonance(Culture $culture)
    {
        $user = Auth::user();

        $interaction = $user->interactions()
            ->where('interactable_type', Culture::class)
            ->where('interactable_id', $culture->id)
            ->where('type', 'resonance')
            ->first();

        if ($interaction) {
            $interaction->delete();
            $culture->decrement('resonance_count');
            $resonated = false;
        } else {
            $user->interactions()->create([
                'interactable_type' => Culture::class,
                'interactable_id' => $culture->id,
                'type' => 'resonance',
            ]);
            $culture->increment('resonance_count');
            $resonated = true;

            // Create notification
            if ($culture->submitted_by !== $user->id) {
                $culture->submitter->notifications()->create([
                    'from_user_id' => $user->id,
                    'type' => 'resonance',
                    'title' => 'New Resonance',
                    'message' => $user->name . ' resonated with ' . $culture->name,
                    'data' => ['culture_id' => $culture->id],
                ]);
            }
        }

        return response()->json([
            'resonated' => $resonated,
            'count' => $culture->fresh()->resonance_count,
        ]);
    }
}