<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BadgeController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\PlaceUnlockController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CommunityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All API routes are consumed by the mobile app.
| Authentication: Firebase ID token → POST /api/auth/firebase → Sanctum token.
| All authenticated endpoints require 'auth:sanctum' middleware.
|
*/

// Public auth endpoint — mobile Firebase login
Route::post('/auth/firebase', [AuthController::class, 'firebaseLogin']);

// Public categories endpoint (no auth needed)
Route::get('/categories', function () {
    $icons = [
        'mountain'=>'⛰️','beach'=>'🏖️','island'=>'🏝️','falls'=>'💧',
        'river'=>'🌊','lake'=>'🏞️','campsite'=>'⛺','historical'=>'🏛️',
        'food_destination'=>'🍜','road_trip'=>'🚗','hidden_gem'=>'💎',
    ];

    $categories = collect(\App\Enums\PlaceCategory::cases())->map(function ($cat) use ($icons) {
        return [
            'value' => $cat->value,
            'label' => str_replace('_', ' ', ucfirst($cat->value)),
            'icon' => $icons[$cat->value] ?? '📍',
            'place_count' => \App\Models\Place::where('category', $cat->value)->where('is_active', true)->count(),
        ];
    });

    return response()->json($categories);
});

// All places for map (lightweight, no pagination)
Route::get('/places/all', function () {
    $places = \App\Models\Place::where('is_active', true)
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->withCount('unlockedByUsers')
        ->select(['id', 'name', 'slug', 'category', 'region', 'province', 'latitude', 'longitude', 'xp_reward'])
        ->orderBy('name')
        ->get();

    return response()->json(['data' => $places]);
});

Route::middleware('auth:sanctum')->group(function () {
    // FCM token registration
    Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Current user profile
    Route::get('/me', function (Request $request) {
        $user = $request->user();

        try {
            $user->loadCount(['unlockedPlaces', 'badges']);
        } catch (\Throwable $e) {
            // ignore if relationship fails
        }

        $followersCount = 0;
        $followingCount = 0;
        try {
            $followersCount = $user->followers()->count();
            $followingCount = $user->following()->count();
        } catch (\Throwable $e) {
            // follows table might not exist
        }

        $xpProgress = null;
        try {
            $xpService = app(\App\Services\XpService::class);
            $xpProgress = $xpService->getProgress($user);
        } catch (\Throwable $e) {
            $xpProgress = ['level' => $user->level ?? 1, 'total_xp' => $user->xp ?? 0, 'progress_percent' => 0];
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'avatar_path' => $user->avatar_path,
            'role' => $user->role instanceof \BackedEnum ? $user->role->value : $user->role,
            'level' => $user->level ?? 1,
            'xp' => $user->xp ?? 0,
            'xp_progress' => $xpProgress,
            'total_points' => $user->total_points ?? 0,
            'available_points' => $user->available_points ?? 0,
            'unlocked_places_count' => $user->unlocked_places_count ?? 0,
            'badges_count' => $user->badges_count ?? 0,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'created_at' => $user->created_at,
        ]);
    });

    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event:slug}', [EventController::class, 'show']);
    Route::post('/events/{event}/book', [BookingController::class, 'store']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'cancel']);

    // Organizer API endpoints
    Route::middleware('role:organizer')->group(function () {
        Route::post('/events/{event}/complete', [EventController::class, 'complete']);
        Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
        Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
    });

    // Places & Unlocks
    Route::get('/places', [PlaceController::class, 'index']);
    Route::get('/places/{place:slug}', [PlaceController::class, 'show']);
    Route::post('/places/{place}/unlock', [PlaceUnlockController::class, 'store']);

    // My unlocked place IDs (for map, no pagination)
    Route::get('/my-unlocks', function (Request $request) {
        $unlocks = $request->user()->unlockedPlaces()
            ->select(['places.id'])
            ->withPivot('created_at', 'unlock_method')
            ->get()
            ->map(function ($place) {
                return [
                    'place_id' => $place->id,
                    'unlocked_at' => $place->pivot->created_at,
                    'method' => $place->pivot->unlock_method,
                ];
            });

        return response()->json(['data' => $unlocks]);
    });

    // Profile
    Route::get('/profile/{user:username}', [ProfileController::class, 'show']);
    Route::get('/profile/{user:username}/unlocks', [ProfileController::class, 'unlocks']);
    Route::get('/profile/{user:username}/badges', [ProfileController::class, 'badges']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // Social
    Route::post('/users/{user}/follow', [FollowController::class, 'store']);
    Route::delete('/users/{user}/unfollow', [FollowController::class, 'destroy']);

    // Achievements & Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/badges', [BadgeController::class, 'index']);

    // Rewards & Redemptions
    Route::get('/rewards', [RewardController::class, 'index']);
    Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem']);
    Route::get('/my-redemptions', [RewardController::class, 'myRedemptions']);

    // Community Feed
    Route::get('/feed', [CommunityController::class, 'feed']);
    Route::post('/posts', [CommunityController::class, 'createPost']);
    Route::get('/posts/{post}', [CommunityController::class, 'showPost']);
    Route::delete('/posts/{post}', [CommunityController::class, 'deletePost']);
    Route::get('/posts/{post}/comments', [CommunityController::class, 'getComments']);
    Route::post('/posts/{post}/comments', [CommunityController::class, 'addComment']);
    Route::post('/posts/{post}/react', [CommunityController::class, 'toggleReaction']);
    Route::get('/users/{user}/posts', [CommunityController::class, 'userPosts']);
    Route::get('/my-posts', function (Request $request) {
        return app(CommunityController::class)->userPosts($request, $request->user());
    });
    Route::get('/suggested-explorers', [CommunityController::class, 'suggestedExplorers']);
});
