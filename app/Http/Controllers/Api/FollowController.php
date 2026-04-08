<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Follow a user.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $follower = $request->user();

        if ($follower->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        if ($follower->following()->where('following_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are already following this user.'], 422);
        }

        $follower->following()->attach($user->id);

        // Notify the followed user
        app(\App\Services\NotificationService::class)->notifyNewFollower($user, $follower);

        return response()->json(['message' => 'Successfully followed user.'], 201);
    }

    /**
     * Unfollow a user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $follower = $request->user();

        if (!$follower->following()->where('following_id', $user->id)->exists()) {
            return response()->json(['message' => 'You are not following this user.'], 422);
        }

        $follower->following()->detach($user->id);

        return response()->json(['message' => 'Successfully unfollowed user.']);
    }
}
