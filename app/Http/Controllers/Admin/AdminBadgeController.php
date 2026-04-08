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
            $validated['icon_path'] = Storage::disk()->putFile('badge-icons', $request->file('icon'));
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
            $validated['icon_path'] = Storage::disk()->putFile('badge-icons', $request->file('icon'));
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

    /**
     * Show award badge form.
     */
    public function showAward(Badge $badge): View
    {
        $users = \App\Models\User::where('role', 'user')
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email']);

        $alreadyAwarded = $badge->users()->pluck('users.id')->toArray();

        return view('admin.badges.award', compact('badge', 'users', 'alreadyAwarded'));
    }

    /**
     * Award badge to selected users.
     */
    public function award(Request $request, Badge $badge): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $notificationService = app(\App\Services\NotificationService::class);
        $xpService = app(\App\Services\XpService::class);
        $awarded = 0;

        foreach ($validated['user_ids'] as $userId) {
            // Skip if already has badge
            if ($badge->users()->where('users.id', $userId)->exists()) continue;

            $badge->users()->attach($userId, ['awarded_at' => now()]);
            $user = \App\Models\User::find($userId);

            // Award points
            if ($badge->points > 0) {
                $user->increment('total_points', $badge->points);
                $user->increment('available_points', $badge->points);
            }

            // Award XP
            if (($badge->xp_reward ?? 0) > 0) {
                $xpService->awardBadgeXp($user, $badge);
            }

            // Notify
            $notificationService->notifyBadgeAwarded($user, $badge);
            $awarded++;
        }

        return redirect()->route('admin.badges.index')
            ->with('success', "Badge \"{$badge->name}\" awarded to {$awarded} user(s).");
    }

    /**
     * Award badge to ALL users who don't have it yet.
     */
    public function awardAll(Badge $badge): RedirectResponse
    {
        $existingUserIds = $badge->users()->pluck('users.id')->toArray();
        $users = \App\Models\User::where('role', 'user')
            ->whereNotIn('id', $existingUserIds)
            ->get();

        $notificationService = app(\App\Services\NotificationService::class);
        $xpService = app(\App\Services\XpService::class);

        foreach ($users as $user) {
            $badge->users()->attach($user->id, ['awarded_at' => now()]);

            if ($badge->points > 0) {
                $user->increment('total_points', $badge->points);
                $user->increment('available_points', $badge->points);
            }

            if (($badge->xp_reward ?? 0) > 0) {
                $xpService->awardBadgeXp($user, $badge);
            }

            $notificationService->notifyBadgeAwarded($user, $badge);
        }

        return redirect()->route('admin.badges.index')
            ->with('success', "Badge \"{$badge->name}\" awarded to {$users->count()} user(s).");
    }
}
