<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminBadgeController extends Controller
{
    public function index(Request $request): View
    {
        $badges = Badge::query()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.badges.index', compact('badges'));
    }

    public function create(): View
    {
        return view('admin.badges.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:badges,slug'],
            'description' => ['nullable', 'string'],
            'criteria_type' => ['required', 'string', 'in:unlock_count,category_count,region_count,streak'],
            'criteria_value' => ['required', 'array'],
            'is_active' => ['boolean'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'category' => ['nullable', 'string'],
            'points' => ['nullable', 'integer', 'min:0'],
            'xp_reward' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon_path'] = Storage::disk('s3')->putFile('badge-icons', $request->file('icon'), 'public');
        }

        unset($validated['icon']);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['points'] = $validated['points'] ?? 0;
        $validated['xp_reward'] = $validated['xp_reward'] ?? 0;

        Badge::create($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge created successfully.');
    }

    public function edit(Badge $badge): View
    {
        return view('admin.badges.edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:badges,slug,' . $badge->id],
            'description' => ['nullable', 'string'],
            'criteria_type' => ['required', 'string', 'in:unlock_count,category_count,region_count,streak'],
            'criteria_value' => ['required', 'array'],
            'is_active' => ['boolean'],
            'icon' => ['nullable', 'image', 'max:2048'],
            'category' => ['nullable', 'string'],
            'points' => ['nullable', 'integer', 'min:0'],
            'xp_reward' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('icon')) {
            $validated['icon_path'] = Storage::disk('s3')->putFile('badge-icons', $request->file('icon'), 'public');
        }

        unset($validated['icon']);

        $validated['is_active'] = $request->boolean('is_active', true);

        $badge->update($validated);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge updated successfully.');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $badge->update(['is_active' => false]);

        return redirect()->route('admin.badges.index')
            ->with('success', 'Badge deactivated successfully.');
    }
}
