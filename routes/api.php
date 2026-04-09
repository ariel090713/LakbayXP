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
use App\Http\Controllers\Api\ExplorerController;
use App\Http\Controllers\Api\TravelBuddyController;
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
            'event_count' => \App\Models\Event::where('category', $cat->value)->whereIn('status', ['published', 'full'])->count(),
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

// Regions with provinces
Route::get('/regions', function () {
    $regions = \DB::table('regions')
        ->orderBy('sort_order')
        ->get()
        ->map(function ($region) {
            $provinces = \DB::table('provinces')
                ->where('region_id', $region->id)
                ->orderBy('sort_order')
                ->pluck('name');

            return [
                'id' => $region->id,
                'name' => $region->name,
                'provinces' => $provinces,
            ];
        });

    return response()->json(['data' => $regions]);
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

        // Calculate ranking — no ties, tiebreaker: older account ranks higher
        $myRanking = \App\Models\User::where('role', 'user')
            ->where(function ($q) use ($user) {
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

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'bio' => $user->bio,
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_path ? \Storage::disk('s3')->url($user->avatar_path) : null,
            'cover_photo_path' => $user->cover_photo_path ?? null,
            'cover_photo_url' => ($user->cover_photo_path ?? null) ? \Storage::disk('s3')->url($user->cover_photo_path) : null,
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
            'buddies_count' => \App\Models\TravelBuddy::where('status', 'accepted')
                ->where(fn ($q) => $q->where('requester_id', $user->id)->orWhere('receiver_id', $user->id))
                ->count(),
            'my_ranking' => $myRanking,
            'created_at' => $user->created_at,
        ]);
    });

    // Events
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event:slug}', [EventController::class, 'show']);
    Route::post('/events/{event}/book', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::delete('/bookings/{booking}', [BookingController::class, 'cancel']);

    // Organizer API endpoints
    Route::middleware('role:organizer')->group(function () {
        Route::post('/events/{event}/complete', [EventController::class, 'complete']);
        Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
        Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
    });

    // Admin API endpoints
    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/grant-xp', function (Request $request) {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'amount' => ['required', 'integer', 'min:1', 'max:10000'],
                'description' => ['required', 'string', 'max:255'],
                'category' => ['nullable', 'string'],
            ]);

            $user = \App\Models\User::findOrFail($request->input('user_id'));
            $xpService = app(\App\Services\XpService::class);
            $result = $xpService->adminGrantXp(
                admin: $request->user(),
                user: $user,
                amount: $request->input('amount'),
                description: $request->input('description'),
                category: $request->input('category'),
            );

            return response()->json([
                'message' => "Granted {$request->input('amount')} XP to {$user->name}.",
                'result' => $result,
            ]);
        });

        // Admin grant points
        Route::post('/admin/grant-points', function (Request $request) {
            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'amount' => ['required', 'integer', 'min:1', 'max:100000'],
                'description' => ['required', 'string', 'max:255'],
            ]);

            $user = \App\Models\User::findOrFail($request->input('user_id'));
            $pointsService = app(\App\Services\PointsService::class);
            $history = $pointsService->adminGrantPoints(
                admin: $request->user(),
                user: $user,
                amount: $request->input('amount'),
                description: $request->input('description'),
            );

            return response()->json([
                'message' => "Granted {$request->input('amount')} points to {$user->name}.",
                'history' => $history,
            ]);
        });
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

    // Social — Follow
    Route::post('/users/{user}/follow', [FollowController::class, 'store']);
    Route::delete('/users/{user}/unfollow', [FollowController::class, 'destroy']);
    Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{user}/following', [FollowController::class, 'following']);

    // Travel Buddies
    Route::get('/travel-buddies', [TravelBuddyController::class, 'index']);
    Route::get('/travel-buddies/pending-received', [TravelBuddyController::class, 'pendingReceived']);
    Route::get('/travel-buddies/pending-sent', [TravelBuddyController::class, 'pendingSent']);
    Route::post('/users/{user}/buddy-request', [TravelBuddyController::class, 'store']);
    Route::post('/travel-buddies/{travelBuddy}/accept', [TravelBuddyController::class, 'accept']);
    Route::post('/travel-buddies/{travelBuddy}/decline', [TravelBuddyController::class, 'decline']);
    Route::post('/travel-buddies/{travelBuddy}/cancel', [TravelBuddyController::class, 'cancel']);
    Route::delete('/travel-buddies/{travelBuddy}', [TravelBuddyController::class, 'remove']);

    // Achievements & Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);
    Route::get('/badges', [BadgeController::class, 'index']);

    // My badges with viewed status
    Route::get('/my-badges', function (Request $request) {
        $badges = $request->user()->badges()
            ->orderByPivot('awarded_at', 'desc')
            ->get();

        $unviewedCount = $request->user()->badges()->wherePivot('is_viewed', false)->count();

        return response()->json([
            'data' => $badges,
            'unviewed_count' => $unviewedCount,
        ]);
    });

    // Mark badge as viewed
    Route::post('/my-badges/{badge}/view', function (Request $request, \App\Models\Badge $badge) {
        $request->user()->badges()->updateExistingPivot($badge->id, ['is_viewed' => true]);
        return response()->json(['message' => 'Badge marked as viewed.']);
    });

    // Mark all badges as viewed
    Route::post('/my-badges/view-all', function (Request $request) {
        \DB::table('user_badges')
            ->where('user_id', $request->user()->id)
            ->where('is_viewed', false)
            ->update(['is_viewed' => true]);
        return response()->json(['message' => 'All badges marked as viewed.']);
    });

    // XP History & Category Leaderboard
    Route::get('/xp-history', function (Request $request) {
        $xpService = app(\App\Services\XpService::class);
        return response()->json($xpService->getHistory($request->user(), $request->input('per_page', 15)));
    });

    Route::get('/xp-categories', function (Request $request) {
        $xpService = app(\App\Services\XpService::class);
        return response()->json(['data' => $xpService->getCategoryXp($request->user())]);
    });

    // Points History
    Route::get('/points-history', function (Request $request) {
        $pointsService = app(\App\Services\PointsService::class);
        return response()->json($pointsService->getHistory($request->user(), $request->input('per_page', 15)));
    });

    Route::get('/leaderboard/category/{category}', function (Request $request, string $category) {
        $users = \App\Models\XpHistory::where('category', $category)
            ->selectRaw('user_id, SUM(amount) as category_xp')
            ->groupBy('user_id')
            ->orderByDesc('category_xp')
            ->paginate($request->input('per_page', 20));

        $userIds = $users->pluck('user_id');
        $userMap = \App\Models\User::whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $users->getCollection()->transform(function ($row) use ($userMap) {
            $user = $userMap[$row->user_id] ?? null;
            return [
                'user_id' => $row->user_id,
                'category_xp' => (int) $row->category_xp,
                'name' => $user?->name,
                'username' => $user?->username,
                'avatar_url' => $user?->avatar_url,
                'level' => $user?->level ?? 1,
                'total_xp' => $user?->xp ?? 0,
            ];
        });

        return response()->json($users);
    });

    // Rewards & Redemptions
    Route::get('/rewards', [RewardController::class, 'index']);
    Route::post('/rewards/{reward}/redeem', [RewardController::class, 'redeem']);
    Route::get('/my-redemptions', [RewardController::class, 'myRedemptions']);

    // Community Feed
    Route::get('/feed', [CommunityController::class, 'feed']);
    Route::post('/posts', [CommunityController::class, 'createPost']);
    Route::get('/posts/{post}', [CommunityController::class, 'showPost']);
    Route::put('/posts/{post}', [CommunityController::class, 'updatePost']);
    Route::delete('/posts/{post}', [CommunityController::class, 'deletePost']);
    Route::delete('/posts/{post}/images/{postImage}', [CommunityController::class, 'deletePostImage']);
    Route::get('/posts/{post}/comments', [CommunityController::class, 'getComments']);
    Route::post('/posts/{post}/comments', [CommunityController::class, 'addComment']);
    Route::post('/posts/{post}/react', [CommunityController::class, 'toggleReaction']);
    Route::post('/comments/{comment}/react', [CommunityController::class, 'toggleCommentReaction']);
    Route::get('/users/{user}/posts', [CommunityController::class, 'userPosts']);
    Route::get('/my-posts', function (Request $request) {
        return app(CommunityController::class)->userPosts($request, $request->user());
    });

    // Notifications
    Route::get('/notifications', function (Request $request) {
        $notifications = \App\Models\AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($notifications);
    });

    Route::get('/notifications/unread-count', function (Request $request) {
        $count = \App\Models\AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    });

    Route::post('/notifications/{notification}/read', function (Request $request, \App\Models\AppNotification $notification) {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $notification->update(['is_read' => true]);
        return response()->json(['message' => 'Marked as read.']);
    });

    Route::post('/notifications/read-all', function (Request $request) {
        \App\Models\AppNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return response()->json(['message' => 'All marked as read.']);
    });

    Route::get('/suggested-explorers', [CommunityController::class, 'suggestedExplorers']);

    // Explorers list
    Route::get('/explorers', [ExplorerController::class, 'index']);
    Route::post('/location', [ExplorerController::class, 'updateLocation']);
});
