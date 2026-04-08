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

    /**
     * List followers of a user.
     */
    public function followers(Request $request, User $user): JsonResponse
    {
        $me = $request->user();
        $myFollowingIds = $me->following()->pluck('following_id')->toArray();

        $followers = $user->followers()
            ->select(['users.id', 'name', 'username', 'avatar_path', 'level', 'xp'])
            ->paginate($request->input('per_page', 20));

        $followers->getCollection()->transform(function ($u) use ($myFollowingIds, $me) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar_url' => $u->avatar_url,
                'level' => $u->level ?? 1,
                'xp' => $u->xp ?? 0,
                'is_following' => in_array($u->id, $myFollowingIds),
                'is_me' => $u->id === $me->id,
            ];
        });

        return response()->json($followers);
    }

    /**
     * List users that a user is following.
     */
    public function following(Request $request, User $user): JsonResponse
    {
        $me = $request->user();
        $myFollowingIds = $me->following()->pluck('following_id')->toArray();

        $following = $user->following()
            ->select(['users.id', 'name', 'username', 'avatar_path', 'level', 'xp'])
            ->paginate($request->input('per_page', 20));

        $following->getCollection()->transform(function ($u) use ($myFollowingIds, $me) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar_url' => $u->avatar_url,
                'level' => $u->level ?? 1,
                'xp' => $u->xp ?? 0,
                'is_following' => in_array($u->id, $myFollowingIds),
                'is_me' => $u->id === $me->id,
            ];
        });

        return response()->json($following);
    }
}
