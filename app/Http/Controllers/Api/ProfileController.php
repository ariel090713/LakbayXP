<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlaceCategory;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Update the authenticated user's profile (avatar, bio).
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username,' . $request->user()->id],
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'cover_photo' => ['nullable', 'image', 'max:10240'],
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            try {
                $path = Storage::disk('s3')->putFile('avatars', $request->file('avatar'));
                if ($path) $user->avatar_path = $path;
            } catch (\Throwable $e) {
                \Log::error('Avatar upload failed', ['error' => $e->getMessage()]);
            }
        }

        if ($request->hasFile('cover_photo')) {
            try {
                $path = Storage::disk('s3')->putFile('covers', $request->file('cover_photo'));
                if ($path) $user->cover_photo_path = $path;
            } catch (\Throwable $e) {
                \Log::error('Cover photo upload failed', ['error' => $e->getMessage()]);
            }
        }

        if (isset($validated['name'])) $user->name = $validated['name'];
        if (isset($validated['username'])) $user->username = $validated['username'];
        if (array_key_exists('bio', $validated)) $user->bio = $validated['bio'];

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_path' => $user->avatar_path,
                'avatar_url' => $user->avatar_path ? Storage::disk('s3')->url($user->avatar_path) : null,
                'cover_photo_path' => $user->cover_photo_path,
                'cover_photo_url' => $user->cover_photo_path ? Storage::disk('s3')->url($user->cover_photo_path) : null,
            ],
        ]);
    }

    /**
     * Show a user's public travel profile.
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $me = $request->user();
        $user->loadCount(['unlockedPlaces', 'badges', 'followers', 'following']);

        $categoryCounts = [];
        foreach (PlaceCategory::cases() as $category) {
            $categoryCounts[$category->value] = $user->unlockedPlaces()
                ->where('category', $category->value)
                ->count();
        }

        $xpService = app(\App\Services\XpService::class);
        $xpProgress = $xpService->getProgress($user);

        // Ranking
        $ranking = User::where(function ($q) use ($user) {
                $q->where('level', '>', $user->level ?? 1)
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('level', $user->level ?? 1)
                         ->where('xp', '>', $user->xp ?? 0);
                  })
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('level', $user->level ?? 1)
                         ->where('xp', $user->xp ?? 0)
                         ->where('created_at', '<', $user->created_at);
                  });
            })->count() + 1;

        // Follow/buddy status relative to the authenticated user
        $isFollowing = $me->following()->where('following_id', $user->id)->exists();

        $buddyRecord = \App\Models\TravelBuddy::where(function ($q) use ($me, $user) {
            $q->where(fn ($q2) => $q2->where('requester_id', $me->id)->where('receiver_id', $user->id))
              ->orWhere(fn ($q2) => $q2->where('requester_id', $user->id)->where('receiver_id', $me->id));
        })->first();

        $isBuddy = $buddyRecord && $buddyRecord->status === 'accepted';
        $buddyRequestSent = $buddyRecord && $buddyRecord->status === 'pending' && $buddyRecord->requester_id === $me->id;
        $buddyRequestReceived = $buddyRecord && $buddyRecord->status === 'pending' && $buddyRecord->receiver_id === $me->id;

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'avatar_path' => $user->avatar_path,
                'avatar_url' => $user->avatar_url,
                'cover_photo_path' => $user->cover_photo_path ?? null,
                'cover_photo_url' => $user->cover_photo_url,
                'explorer_level' => $user->explorer_level,
                'level' => $user->level,
                'xp' => $xpProgress,
                'total_points' => $user->total_points,
                'available_points' => $user->available_points,
                'unlocked_places_count' => $user->unlocked_places_count,
                'badge_count' => $user->badges_count,
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
                'buddies_count' => \App\Models\TravelBuddy::where('status', 'accepted')
                    ->where(fn ($q) => $q->where('requester_id', $user->id)->orWhere('receiver_id', $user->id))
                    ->count(),
                'ranking' => $ranking,
                'is_following' => $isFollowing,
                'is_buddy' => $isBuddy,
                'buddy_request_sent' => $buddyRequestSent,
                'buddy_request_received' => $buddyRequestReceived,
                'buddy_request_id' => $buddyRecord?->id,
                'firebase_uid' => $user->firebase_uid,
                'badges' => $user->badges->map(fn ($badge) => [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'slug' => $badge->slug,
                    'icon_path' => $badge->icon_path,
                    'points' => $badge->points,
                    'xp_reward' => $badge->xp_reward,
                ]),
                'category_counts' => $categoryCounts,
            ],
        ]);
    }

    /**
     * List a user's unlocked places.
     */
    public function unlocks(Request $request, User $user): JsonResponse
    {
        $unlocks = $user->unlockedPlaces()
            ->withPivot('created_at', 'unlock_method')
            ->orderByPivot('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($unlocks);
    }

    /**
     * List a user's earned badges.
     */
    public function badges(Request $request, User $user): JsonResponse
    {
        $badges = $user->badges()
            ->withPivot('awarded_at')
            ->orderByPivot('awarded_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($badges);
    }
}
