<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Services\PlaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class AdminPlaceController extends Controller
{
    public function __construct(
        protected PlaceService $placeService,
    ) {}

    /**
     * Display a listing of places.
     */
    public function index(Request $request): View
    {
        $places = Place::query()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.places.index', compact('places'));
    }

    /**
     * Show the form for creating a new place.
     */
    public function create(): View
    {
        $categories = PlaceCategory::cases();

        return view('admin.places.create', compact('categories'));
    }

    /**
     * Store a newly created place.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:places,slug'],
            'description' => ['nullable', 'string'],
            'category' => ['required', new Enum(PlaceCategory::class)],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'category_fields' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('place-covers', $request->file('cover_image'), 'public');
        }

        unset($validated['cover_image']);
        $validated['created_by'] = $request->user()->id;

        $this->placeService->create($validated);

        return redirect()->route('admin.places.index')
            ->with('success', 'Place created successfully.');
    }

    /**
     * Show the form for editing the specified place.
     */
    public function edit(Place $place): View
    {
        $categories = PlaceCategory::cases();

        return view('admin.places.edit', compact('place', 'categories'));
    }

    /**
     * Update the specified place.
     */
    public function update(Request $request, Place $place): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:places,slug,' . $place->id],
            'description' => ['nullable', 'string'],
            'category' => ['required', new Enum(PlaceCategory::class)],
            'region' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'category_fields' => ['nullable', 'array'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('place-covers', $request->file('cover_image'), 'public');
        }

        unset($validated['cover_image']);

        $this->placeService->update($place, $validated);

        return redirect()->route('admin.places.index')
            ->with('success', 'Place updated successfully.');
    }

    /**
     * Remove (deactivate) the specified place.
     */
    public function destroy(Place $place): RedirectResponse
    {
        $this->placeService->deactivate($place);

        return redirect()->route('admin.places.index')
            ->with('success', 'Place deactivated successfully.');
    }
}
