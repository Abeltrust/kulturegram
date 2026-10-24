<?php

namespace App\Http\Controllers;

use App\Models\VaultItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VaultController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        
        $query = Auth::user()->vaultItems()->latest();

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $items = $query->paginate(12);
        
        $types = [
            'all' => 'All Items',
            'photos' => 'Photos',
            'documents' => 'Documents',
            'videos' => 'Videos',
            'audio' => 'Audio',
        ];

        return view('vault.index', compact('items', 'types', 'type'));
    }

    public function create()
    {
        return view('vault.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:photos,documents,videos,audio',
        'files.*' => 'file|max:51200', // allow multiple, 50MB each
        'cultural_significance' => 'nullable|string',
        'access_level' => 'required|in:private,family,research,public',
        'is_hidden' => 'boolean',
        'is_draft' => 'boolean',
    ]);

    if ($request->hasFile('files')) {
        foreach ($request->file('files') as $file) {
            $path = $file->store('vault/' . Auth::id(), 'private');

            VaultItem::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'file_path' => $path,
                'file_size' => $this->formatBytes($file->getSize()),
                'mime_type' => $file->getMimeType(),
                'cultural_significance' => $request->cultural_significance,
                'access_level' => $request->access_level,
                'is_hidden' => $request->boolean('is_hidden'),
                'metadata' => [
                    'draft' => $request->boolean('is_draft'),
                    'original_name' => $file->getClientOriginalName(),
                ],
            ]);
        }
    }

    return redirect()->route('vault.index')
        ->with('success', $request->boolean('is_draft') 
            ? 'Draft saved successfully!' 
            : 'Item(s) added to vault successfully!');
}

    public function show(VaultItem $vaultItem)
    {
        $this->authorize('view', $vaultItem);
        
        return view('vault.show', compact('vaultItem'));
    }

    public function download(VaultItem $vaultItem)
    {
        $this->authorize('view', $vaultItem);
        
        return Storage::disk('private')->download($vaultItem->file_path, $vaultItem->title);
    }

    public function destroy(VaultItem $vaultItem)
    {
        $this->authorize('delete', $vaultItem);
        
        Storage::disk('private')->delete($vaultItem->file_path);
        $vaultItem->delete();

        return redirect()->route('vault.index')
            ->with('success', 'Item deleted successfully!');
    }

    public function edit(VaultItem $vaultItem)
    {
        $this->authorize('update', $vaultItem);

        return view('vault.edit', compact('vaultItem'));
    }

    public function update(Request $request, VaultItem $vaultItem)
    {
        $this->authorize('update', $vaultItem);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cultural_significance' => 'nullable|string',
            'access_level' => 'required|in:private,family,research,public',
            'is_hidden' => 'boolean',
        ]);

        $vaultItem->update([
            'title' => $request->title,
            'description' => $request->description,
            'cultural_significance' => $request->cultural_significance,
            'access_level' => $request->access_level,
            'is_hidden' => $request->boolean('is_hidden'),
        ]);

        return redirect()->route('vault.show', $vaultItem)
            ->with('success', 'Vault item updated successfully!');
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}