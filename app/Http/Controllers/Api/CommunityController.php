<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostImage;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    /**
     * Smart feed: all active posts, ordered by a weighted score.
     * Posts from people you follow get boosted. Trending posts get boosted.
     * Fully paginated — returns ALL posts across pages.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->input('per_page', 15);
        $followingIds = $user->following()->pluck('users.id')->toArray();

        $posts = Post::active()
            ->with([
                'user:id,name,username,avatar_path,level',
                'images',
                'place:id,name,slug,category',
                'event:id,title,slug',
                'badge:id,name,slug,icon_path',
            ])
            ->withCount(['reactions', 'comments'])
            ->orderByRaw(
                // Boost: following +2, own posts +1, system posts +1, new (< 1hr) +1
                '(CASE WHEN user_id IN (' . (empty($followingIds) ? '0' : implode(',', $followingIds)) . ') THEN 2 ELSE 0 END)
                + (CASE WHEN user_id = ' . $user->id . ' THEN 1 ELSE 0 END)
                + (CASE WHEN type IN ("place_unlock","badge_earned","event_completed") THEN 1 ELSE 0 END)
                + (CASE WHEN created_at >= "' . now()->subHour()->toDateTimeString() . '" THEN 1 ELSE 0 END)
                DESC'
            )
            ->orderByDesc('created_at')
            ->paginate($perPage);

        // Attach user's reaction type to each post
        $userReactions = Reaction::where('user_id', $user->id)
            ->whereIn('post_id', $posts->pluck('id'))
            ->pluck('type', 'post_id');

        $posts->getCollection()->transform(function ($post) use ($userReactions) {
            $post->user_reaction = $userReactions[$post->id] ?? null;
            $post->reaction_counts = $post->reaction_counts;
            return $post;
        });

        return response()->json($posts);
    }

    /**
     * Create a post (text or photo).
     */
    public function createPost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'in:text,photo'],
            'place_id' => ['nullable', 'exists:places,id'],
            'event_id' => ['nullable', 'exists:events,id'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'max:10240'],
        ]);

        $post = Post::create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'type' => $request->hasFile('images') ? 'photo' : ($validated['type'] ?? 'text'),
            'place_id' => $validated['place_id'] ?? null,
            'event_id' => $validated['event_id'] ?? null,
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                try {
                    $path = Storage::disk('s3')->putFile('posts', $image);
                    if ($path) {
                        PostImage::create([
                            'post_id' => $post->id,
                            'image_path' => $path,
                            'sort_order' => $i,
                        ]);
                    } else {
                        \Log::error('Post image upload returned false', ['post_id' => $post->id, 'index' => $i]);
                    }
                } catch (\Throwable $e) {
                    \Log::error('Post image upload failed', ['post_id' => $post->id, 'error' => $e->getMessage()]);
                }
            }
        }

        $post->load(['user:id,name,username,avatar_path,level', 'images']);
        $post->loadCount(['reactions', 'comments']);
        $post->reaction_counts = $post->reaction_counts;

        return response()->json(['data' => $post], 201);
    }

    /**
     * Get a single post with comments.
     */
    public function showPost(Post $post): JsonResponse
    {
        $post->load([
            'user:id,name,username,avatar_path,level',
            'images',
            'place:id,name,slug,category',
            'event:id,title,slug',
            'badge:id,name,slug,icon_path',
            'comments' => function ($q) {
                $q->whereNull('parent_id')
                    ->with(['user:id,name,username,avatar_path', 'replies.user:id,name,username,avatar_path'])
                    ->orderByDesc('created_at')
                    ->take(20);
            },
        ]);
        $post->loadCount(['reactions', 'comments']);

        return response()->json(['data' => $post]);
    }

    /**
     * Delete own post.
     */
    public function deletePost(Request $request, Post $post): JsonResponse
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted.']);
    }

    /**
     * Add a comment to a post.
     */
    public function addComment(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $comment->load('user:id,name,username,avatar_path');

        // Notify post owner
        $post->loadMissing('user');
        app(\App\Services\NotificationService::class)->notifyComment($post->user, $request->user(), $post);

        return response()->json(['data' => $comment], 201);
    }

    /**
     * Get comments for a post (paginated).
     */
    public function getComments(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['user:id,name,username,avatar_path', 'replies.user:id,name,username,avatar_path'])
            ->withCount('reactions')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        // Attach per-type reaction counts + user's reaction
        $commentIds = $comments->pluck('id')->merge($comments->pluck('replies.*.id')->flatten())->filter();
        $userCommentReactions = \App\Models\CommentReaction::where('user_id', $user->id)
            ->whereIn('comment_id', $commentIds)
            ->pluck('type', 'comment_id');

        $comments->getCollection()->transform(function ($comment) use ($userCommentReactions) {
            $comment->reaction_counts = $comment->reaction_counts;
            $comment->user_reaction = $userCommentReactions[$comment->id] ?? null;
            $comment->replies->transform(function ($reply) use ($userCommentReactions) {
                $reply->reaction_counts = $reply->reaction_counts;
                $reply->reactions_count = $reply->reactions()->count();
                $reply->user_reaction = $userCommentReactions[$reply->id] ?? null;
                return $reply;
            });
            return $comment;
        });

        return response()->json($comments);
    }

    /**
     * Toggle reaction on a post (like/unlike or change type).
     */
    public function toggleReaction(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:like,love,fire,wow,congrats'],
        ]);

        $type = $validated['type'] ?? 'like';
        $userId = $request->user()->id;

        $existing = Reaction::where('post_id', $post->id)->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Same type = remove reaction
                $existing->delete();
                return response()->json([
                    'message' => 'Reaction removed.',
                    'reacted' => false,
                    'reactions_count' => $post->reactions()->count(),
                ]);
            }
            // Different type = update
            $existing->update(['type' => $type]);
        } else {
            Reaction::create([
                'post_id' => $post->id,
                'user_id' => $userId,
                'type' => $type,
            ]);

            // Notify post owner
            $post->loadMissing('user');
            app(\App\Services\NotificationService::class)->notifyReaction($post->user, $request->user(), $post, $type);
        }

        return response()->json([
            'message' => 'Reacted.',
            'reacted' => true,
            'type' => $type,
            'reactions_count' => $post->reactions()->count(),
        ]);
    }

    /**
     * Toggle reaction on a comment.
     */
    public function toggleCommentReaction(Request $request, Comment $comment): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:like,love,fire,wow,congrats'],
        ]);

        $type = $validated['type'] ?? 'like';
        $userId = $request->user()->id;

        $existing = \App\Models\CommentReaction::where('comment_id', $comment->id)->where('user_id', $userId)->first();

        if ($existing) {
            if ($existing->type === $type) {
                $existing->delete();
                return response()->json([
                    'message' => 'Reaction removed.',
                    'reacted' => false,
                    'reactions_count' => $comment->reactions()->count(),
                ]);
            }
            $existing->update(['type' => $type]);
        } else {
            \App\Models\CommentReaction::create([
                'comment_id' => $comment->id,
                'user_id' => $userId,
                'type' => $type,
            ]);
        }

        return response()->json([
            'message' => 'Reacted.',
            'reacted' => true,
            'type' => $type,
            'reactions_count' => $comment->reactions()->count(),
        ]);
    }

    /**
     * Get a user's posts (for profile).
     */
    public function userPosts(Request $request, \App\Models\User $user): JsonResponse
    {
        $posts = Post::active()
            ->where('user_id', $user->id)
            ->with(['user:id,name,username,avatar_path,level', 'images', 'place:id,name,slug,category'])
            ->withCount(['reactions', 'comments'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json($posts);
    }

    /**
     * Suggested explorers algorithm:
     * 1. Mutual follows (people your friends follow but you don't)
     * 2. Same events (people who attended the same events as you)
     * 3. Similar unlocks (people who unlocked similar places)
     * 4. Top explorers (high level, active posters)
     * 5. Random discovery (fill remaining slots)
     * All exclude: yourself + people you already follow
     */
    public function suggestedExplorers(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->input('limit', 10);
        $followingIds = $user->following()->pluck('users.id')->toArray();
        $excludeIds = array_merge($followingIds, [$user->id]);

        $suggestions = collect();

        // 1. Mutual follows — people your friends follow (strongest signal)
        if (!empty($followingIds)) {
            $mutuals = DB::table('follows')
                ->whereIn('follower_id', $followingIds)
                ->whereNotIn('following_id', $excludeIds)
                ->select('following_id', DB::raw('COUNT(*) as mutual_count'))
                ->groupBy('following_id')
                ->orderByDesc('mutual_count')
                ->take(4)
                ->pluck('following_id')
                ->toArray();

            if (!empty($mutuals)) {
                $suggestions = $suggestions->merge(
                    \App\Models\User::whereIn('id', $mutuals)
                        ->where('role', 'user')
                        ->withCount(['unlockedPlaces', 'badges', 'followers'])
                        ->get()
                        ->map(function ($u) { $u->suggestion_reason = 'mutual_friends'; return $u; })
                );
            }
        }

        // 2. Same events — people who booked the same events
        $myEventIds = DB::table('bookings')
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->pluck('event_id');

        if ($myEventIds->isNotEmpty()) {
            $eventBuddies = DB::table('bookings')
                ->whereIn('event_id', $myEventIds)
                ->where('status', 'approved')
                ->whereNotIn('user_id', $excludeIds)
                ->whereNotIn('user_id', $suggestions->pluck('id'))
                ->select('user_id', DB::raw('COUNT(*) as shared_events'))
                ->groupBy('user_id')
                ->orderByDesc('shared_events')
                ->take(3)
                ->pluck('user_id')
                ->toArray();

            if (!empty($eventBuddies)) {
                $suggestions = $suggestions->merge(
                    \App\Models\User::whereIn('id', $eventBuddies)
                        ->where('role', 'user')
                        ->withCount(['unlockedPlaces', 'badges', 'followers'])
                        ->get()
                        ->map(function ($u) { $u->suggestion_reason = 'same_events'; return $u; })
                );
            }
        }

        // 3. Similar unlocks — people who unlocked the same places
        $myPlaceIds = $user->unlockedPlaces()->pluck('places.id');
        if ($myPlaceIds->isNotEmpty()) {
            $placemates = DB::table('place_unlocks')
                ->whereIn('place_id', $myPlaceIds)
                ->whereNotIn('user_id', $excludeIds)
                ->whereNotIn('user_id', $suggestions->pluck('id'))
                ->select('user_id', DB::raw('COUNT(*) as shared_places'))
                ->groupBy('user_id')
                ->orderByDesc('shared_places')
                ->take(3)
                ->pluck('user_id')
                ->toArray();

            if (!empty($placemates)) {
                $suggestions = $suggestions->merge(
                    \App\Models\User::whereIn('id', $placemates)
                        ->where('role', 'user')
                        ->withCount(['unlockedPlaces', 'badges', 'followers'])
                        ->get()
                        ->map(function ($u) { $u->suggestion_reason = 'similar_places'; return $u; })
                );
            }
        }

        // 4. Top explorers — highest level active users
        if ($suggestions->count() < $limit) {
            $topIds = \App\Models\User::where('role', 'user')
                ->whereNotIn('id', $excludeIds)
                ->whereNotIn('id', $suggestions->pluck('id'))
                ->where('xp', '>', 0)
                ->orderByDesc('level')
                ->orderByDesc('xp')
                ->take($limit - $suggestions->count())
                ->pluck('id')
                ->toArray();

            if (!empty($topIds)) {
                $suggestions = $suggestions->merge(
                    \App\Models\User::whereIn('id', $topIds)
                        ->withCount(['unlockedPlaces', 'badges', 'followers'])
                        ->get()
                        ->map(function ($u) { $u->suggestion_reason = 'top_explorer'; return $u; })
                );
            }
        }

        // 5. Random discovery — fill remaining
        if ($suggestions->count() < $limit) {
            $randomIds = \App\Models\User::where('role', 'user')
                ->whereNotIn('id', $excludeIds)
                ->whereNotIn('id', $suggestions->pluck('id'))
                ->inRandomOrder()
                ->take($limit - $suggestions->count())
                ->pluck('id')
                ->toArray();

            if (!empty($randomIds)) {
                $suggestions = $suggestions->merge(
                    \App\Models\User::whereIn('id', $randomIds)
                        ->withCount(['unlockedPlaces', 'badges', 'followers'])
                        ->get()
                        ->map(function ($u) { $u->suggestion_reason = 'discover'; return $u; })
                );
            }
        }

        // Format response
        $result = $suggestions->take($limit)->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar_path' => $u->avatar_path,
                'level' => $u->level ?? 1,
                'xp' => $u->xp ?? 0,
                'unlocked_places_count' => $u->unlocked_places_count ?? 0,
                'badges_count' => $u->badges_count ?? 0,
                'followers_count' => $u->followers_count ?? 0,
                'suggestion_reason' => $u->suggestion_reason,
            ];
        })->values();

        return response()->json(['data' => $result]);
    }
}
