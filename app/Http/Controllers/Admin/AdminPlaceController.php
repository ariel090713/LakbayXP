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
        $query = Place::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('province', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by region
        if ($request->filled('region')) {
            $query->where('region', $request->input('region'));
        }

        // Filter by province
        if ($request->filled('province')) {
            $query->where('province', $request->input('province'));
        }

        // Filter by status — default to active only
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        } else {
            $query->where('is_active', true);
        }

        $places = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Get predefined regions and provinces for filter dropdowns
        $regions = \DB::table('regions')->orderBy('sort_order')->pluck('name');
        $provinces = Place::whereNotNull('province')->distinct()->pluck('province')->sort();

        return view('admin.places.index', compact('places', 'regions', 'provinces'));
    }

    /**
     * Show the form for creating a new place.
     */
    public function create(): View
    {
        $categories = PlaceCategory::cases();
        $allCategoryFields = config('place_fields', []);
        $regions = \DB::table('regions')->orderBy('sort_order')->get();
        $provinces = \DB::table('provinces')->orderBy('sort_order')->get()->groupBy('region_id');

        return view('admin.places.create', compact('categories', 'allCategoryFields', 'regions', 'provinces'));
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
            'xp_reward' => ['nullable', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
            'custom_meta_keys' => ['nullable', 'array'],
            'custom_meta_values' => ['nullable', 'array'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:10240'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('place-covers', $request->file('cover_image'));
        }

        unset($validated['cover_image'], $validated['meta'], $validated['custom_meta_keys'], $validated['custom_meta_values'], $validated['gallery']);
        $validated['created_by'] = $request->user()->id;

        $place = $this->placeService->create($validated);

        // Upload gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $i => $image) {
                $path = Storage::disk('s3')->putFile('place-gallery', $image);
                if ($path) {
                    $place->images()->create([
                        'image_path' => $path,
                        'image_source' => 'admin',
                        'sort_order' => $i,
                    ]);
                }
            }
        }

        // Save category-specific meta
        if ($request->has('meta')) {
            $place->syncMeta(array_filter($request->input('meta'), fn($v) => $v !== null && $v !== ''));
        }

        // Save custom meta
        if ($request->has('custom_meta_keys')) {
            $keys = $request->input('custom_meta_keys', []);
            $values = $request->input('custom_meta_values', []);
            foreach ($keys as $i => $key) {
                if ($key && isset($values[$i]) && $values[$i] !== '') {
                    $place->setMeta($key, $values[$i]);
                }
            }
        }

        return redirect()->route('admin.places.index')
            ->with('success', 'Place created successfully.');
    }

    /**
     * Show the form for editing the specified place.
     */
    public function edit(Place $place): View
    {
        $categories = PlaceCategory::cases();
        $place->load('meta');
        $categoryFields = config('place_fields.' . $place->category->value, []);
        $regions = \DB::table('regions')->orderBy('sort_order')->get();
        $provinces = \DB::table('provinces')->orderBy('sort_order')->get()->groupBy('region_id');

        return view('admin.places.edit', compact('place', 'categories', 'categoryFields', 'regions', 'provinces'));
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
            'xp_reward' => ['nullable', 'integer', 'min:0'],
            'meta' => ['nullable', 'array'],
            'custom_meta_keys' => ['nullable', 'array'],
            'custom_meta_values' => ['nullable', 'array'],
            'gallery' => ['nullable', 'array', 'max:10'],
            'gallery.*' => ['image', 'max:10240'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image_path'] = Storage::disk('s3')->putFile('place-covers', $request->file('cover_image'));
        }

        unset($validated['cover_image'], $validated['meta'], $validated['custom_meta_keys'], $validated['custom_meta_values'], $validated['gallery'], $validated['delete_images']);

        $this->placeService->update($place, $validated);

        // Delete selected gallery images
        if ($request->filled('delete_images')) {
            \App\Models\PlaceImage::whereIn('id', $request->input('delete_images'))
                ->where('place_id', $place->id)
                ->each(function ($img) {
                    Storage::disk('s3')->delete($img->image_path);
                    $img->delete();
                });
        }

        // Upload new gallery images
        if ($request->hasFile('gallery')) {
            $maxSort = $place->images()->max('sort_order') ?? 0;
            foreach ($request->file('gallery') as $i => $image) {
                $path = Storage::disk('s3')->putFile('place-gallery', $image);
                if ($path) {
                    $place->images()->create([
                        'image_path' => $path,
                        'image_source' => 'admin',
                        'sort_order' => $maxSort + $i + 1,
                    ]);
                }
            }
        }

        // Save category-specific meta
        if ($request->has('meta')) {
            $place->syncMeta(array_filter($request->input('meta'), fn($v) => $v !== null && $v !== ''));
        }

        // Save custom meta
        if ($request->has('custom_meta_keys')) {
            $keys = $request->input('custom_meta_keys', []);
            $values = $request->input('custom_meta_values', []);
            foreach ($keys as $i => $key) {
                if ($key && isset($values[$i]) && $values[$i] !== '') {
                    $place->setMeta($key, $values[$i]);
                }
            }
        }

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

    /**
     * Activate a place.
     */
    public function activate(Place $place): RedirectResponse
    {
        $this->placeService->activate($place);

        return redirect()->route('admin.places.index')
            ->with('success', 'Place activated successfully.');
    }
}
