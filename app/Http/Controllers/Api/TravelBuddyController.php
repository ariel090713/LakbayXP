<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TravelBuddy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelBuddyController extends Controller
{
    /**
     * List my travel buddies (accepted).
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $buddies = TravelBuddy::where('status', 'accepted')
            ->where(fn ($q) => $q->where('requester_id', $userId)->orWhere('receiver_id', $userId))
            ->with(['requester:id,name,username,avatar_path,level,xp', 'receiver:id,name,username,avatar_path,level,xp'])
            ->orderByDesc('accepted_at')
            ->paginate($request->input('per_page', 20));

        $buddies->getCollection()->transform(function ($tb) use ($userId) {
            $buddy = $tb->requester_id === $userId ? $tb->receiver : $tb->requester;
            return [
                'id' => $tb->id,
                'buddy' => [
                    'id' => $buddy->id,
                    'name' => $buddy->name,
                    'username' => $buddy->username,
                    'avatar_url' => $buddy->avatar_url,
                    'level' => $buddy->level ?? 1,
                    'xp' => $buddy->xp ?? 0,
                ],
                'accepted_at' => $tb->accepted_at,
                'created_at' => $tb->created_at,
            ];
        });

        return response()->json($buddies);
    }

    /**
     * List pending buddy requests I received.
     */
    public function pendingReceived(Request $request): JsonResponse
    {
        $requests = TravelBuddy::where('receiver_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('requester:id,name,username,avatar_path,level,xp')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        $requests->getCollection()->transform(fn ($tb) => [
            'id' => $tb->id,
            'requester' => [
                'id' => $tb->requester->id,
                'name' => $tb->requester->name,
                'username' => $tb->requester->username,
                'avatar_url' => $tb->requester->avatar_url,
                'level' => $tb->requester->level ?? 1,
                'xp' => $tb->requester->xp ?? 0,
            ],
            'created_at' => $tb->created_at,
        ]);

        return response()->json($requests);
    }

    /**
     * List pending buddy requests I sent.
     */
    public function pendingSent(Request $request): JsonResponse
    {
        $requests = TravelBuddy::where('requester_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('receiver:id,name,username,avatar_path,level,xp')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        $requests->getCollection()->transform(fn ($tb) => [
            'id' => $tb->id,
            'receiver' => [
                'id' => $tb->receiver->id,
                'name' => $tb->receiver->name,
                'username' => $tb->receiver->username,
                'avatar_url' => $tb->receiver->avatar_url,
                'level' => $tb->receiver->level ?? 1,
                'xp' => $tb->receiver->xp ?? 0,
            ],
            'created_at' => $tb->created_at,
        ]);

        return response()->json($requests);
    }

    /**
     * Send a travel buddy request.
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $me = $request->user();

        if ($me->id === $user->id) {
            return response()->json(['message' => 'Cannot send buddy request to yourself.'], 422);
        }

        // Check if already buddies or pending in either direction
        $existing = TravelBuddy::where(function ($q) use ($me, $user) {
            $q->where('requester_id', $me->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($me, $user) {
            $q->where('requester_id', $user->id)->where('receiver_id', $me->id);
        })->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return response()->json(['message' => 'You are already travel buddies.'], 422);
            }
            if ($existing->status === 'pending') {
                return response()->json(['message' => 'A buddy request already exists.'], 422);
            }
            // If declined, allow re-request by updating
            $existing->update([
                'requester_id' => $me->id,
                'receiver_id' => $user->id,
                'status' => 'pending',
                'accepted_at' => null,
            ]);

            return response()->json(['message' => 'Buddy request sent.', 'request' => $existing], 201);
        }

        $buddy = TravelBuddy::create([
            'requester_id' => $me->id,
            'receiver_id' => $user->id,
            'status' => 'pending',
        ]);

        // Notify
        try {
            app(\App\Services\NotificationService::class)->notify(
                $user, 'buddy_request',
                '🤝 Buddy Request',
                "{$me->name} sent you a travel buddy request.",
                ['requester_id' => $me->id]
            );
        } catch (\Throwable $e) {}

        return response()->json(['message' => 'Buddy request sent.', 'request' => $buddy], 201);
    }

    /**
     * Accept a buddy request.
     */
    public function accept(Request $request, TravelBuddy $travelBuddy): JsonResponse
    {
        if ($travelBuddy->receiver_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($travelBuddy->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending.'], 422);
        }

        $travelBuddy->update(['status' => 'accepted', 'accepted_at' => now()]);

        try {
            $me = $request->user();
            app(\App\Services\NotificationService::class)->notify(
                $travelBuddy->requester, 'buddy_accepted',
                '🤝 Buddy Accepted',
                "{$me->name} accepted your travel buddy request!",
                ['buddy_id' => $me->id]
            );
        } catch (\Throwable $e) {}

        return response()->json(['message' => 'Buddy request accepted.']);
    }

    /**
     * Decline a buddy request.
     */
    public function decline(Request $request, TravelBuddy $travelBuddy): JsonResponse
    {
        if ($travelBuddy->receiver_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($travelBuddy->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending.'], 422);
        }

        $travelBuddy->update(['status' => 'declined']);

        return response()->json(['message' => 'Buddy request declined.']);
    }

    /**
     * Cancel a buddy request I sent (requester only).
     */
    public function cancel(Request $request, TravelBuddy $travelBuddy): JsonResponse
    {
        if ($travelBuddy->requester_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($travelBuddy->status !== 'pending') {
            return response()->json(['message' => 'Request is not pending.'], 422);
        }

        $travelBuddy->delete();

        return response()->json(['message' => 'Buddy request cancelled.']);
    }

    /**
     * Remove a travel buddy (either side can remove).
     */
    public function remove(Request $request, TravelBuddy $travelBuddy): JsonResponse
    {
        $userId = $request->user()->id;

        if ($travelBuddy->requester_id !== $userId && $travelBuddy->receiver_id !== $userId) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($travelBuddy->status !== 'accepted') {
            return response()->json(['message' => 'Not a travel buddy.'], 422);
        }

        $travelBuddy->delete();

        return response()->json(['message' => 'Travel buddy removed.']);
    }
}
