<?php

namespace App\Http\Controllers;

use App\Models\TrackableItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TrackableItemController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $query = TrackableItem::query();

        if ($category && in_array($category, ['plant', 'chore', 'maintenance', 'pet', 'other'])) {
            $query->where('category', $category);
        }

        $items = $query->get();

        return view('items.index', compact('items', 'category'));
    }

    public function create(Request $request): View
    {
        $preselectedCategory = $request->query('category');
        return view('items.create', compact('preselectedCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'species'               => 'nullable|string|max:255',
            'location'              => 'required|string|max:255',
            'action_frequency_days' => 'required|integer|min:1|max:365',
            'category'              => 'required|in:plant,chore,maintenance,pet,other',
            'sunlight_needs'        => 'nullable|in:low,medium,high,direct',
            'notes'                 => 'nullable|string',
            'photo'                 => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,heic,heif|max:20480',
            'last_action_at'        => 'nullable|date|before_or_equal:today',
        ]);

        if ($request->hasFile('photo')) {
            $validated['image_path'] = $this->storePhoto($request->file('photo'));
        }

        // Maintenance items: default date to today if not provided, and set a far-future frequency
        if ($validated['category'] === 'maintenance') {
            $validated['last_action_at']        = $validated['last_action_at'] ?? now()->toDateString();
            $validated['action_frequency_days'] = 3650;
        }

        unset($validated['photo']);
        $item = TrackableItem::create($validated);

        // Redirect back to the same category list
        $category = $item->category instanceof \App\Enums\ItemCategory
            ? $item->category->value
            : $item->category;

        return redirect()
            ->route('items.index', ['category' => $category])
            ->with('success', $category === 'maintenance' ? 'Maintenance entry logged!' : 'Item added successfully!');
    }

    public function show(TrackableItem $item): View
    {
        $item->load('actionLogs');
        return view('items.show', compact('item'));
    }

    public function edit(TrackableItem $item): View
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, TrackableItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'species'               => 'nullable|string|max:255',
            'location'              => 'required|string|max:255',
            'action_frequency_days' => 'required|integer|min:1|max:365',
            'category'              => 'required|in:plant,chore,maintenance,pet,other',
            'sunlight_needs'        => 'nullable|in:low,medium,high,direct',
            'notes'                 => 'nullable|string',
            'photo'                 => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,heic,heif|max:20480',
        ]);

        if ($request->hasFile('photo')) {
            if ($item->image_path) {
                Storage::disk(config('filesystems.default'))->delete($item->image_path);
            }
            $validated['image_path'] = $this->storePhoto($request->file('photo'));
        }

        unset($validated['photo']);
        $item->update($validated);

        return redirect()->route('items.show', $item)->with('success', 'Item updated successfully!');
    }

    public function destroy(TrackableItem $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk(config('filesystems.default'))->delete($item->image_path);
        }
        $category = $item->category;
        $item->delete();
        return redirect()->route('items.index', ['category' => $category])->with('success', 'Item deleted successfully!');
    }

    private function storePhoto(UploadedFile $file): string
    {
        $isHeic = in_array(strtolower($file->getClientOriginalExtension()), ['heic', 'heif'])
               || in_array(strtolower($file->getMimeType()), ['image/heic', 'image/heif']);

        if ($isHeic) {
            $tmpPath = tempnam(sys_get_temp_dir(), 'casa_') . '.jpg';

            // ffmpeg is in the Docker image and handles all HEIC variants reliably.
            exec('ffmpeg -y -i ' . escapeshellarg($file->getRealPath()) . ' -q:v 2 ' . escapeshellarg($tmpPath) . ' 2>&1', $out, $exitCode);

            if ($exitCode === 0 && file_exists($tmpPath) && filesize($tmpPath) > 0) {
                $filename = 'plants/' . Str::uuid() . '.jpg';
                Storage::disk(config('filesystems.default'))->put($filename, file_get_contents($tmpPath));
                unlink($tmpPath);
                return $filename;
            }

            // Fall back to Imagick if ffmpeg fails for any reason.
            if (class_exists(\Imagick::class)) {
                try {
                    $imagick = new \Imagick();
                    $imagick->readImage($file->getRealPath() . '[0]');
                    $imagick->setImageFormat('jpeg');
                    $imagick->setImageCompressionQuality(85);
                    $imagick->stripImage();

                    $filename = 'plants/' . Str::uuid() . '.jpg';
                    Storage::disk(config('filesystems.default'))->put($filename, $imagick->getImageBlob());
                    $imagick->clear();

                    return $filename;
                } catch (\ImagickException) {
                    // Store original HEIC as last resort.
                }
            }
        }

        return $file->store('plants', config('filesystems.default'));
    }

    public function dashboard(): View
    {
        $items      = TrackableItem::all();
        $recentLogs = \App\Models\ActionLog::with('trackableItem')
                        ->latest()
                        ->limit(8)
                        ->get();

        return view('dashboard', compact('items', 'recentLogs'));
    }
}
