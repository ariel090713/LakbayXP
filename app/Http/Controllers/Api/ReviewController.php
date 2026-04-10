<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Place;
use App\Models\Review;
use App\Models\ReviewPhoto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    // ── Place Reviews ──

    public function createPlaceReview(Request $request, Place $place): JsonResponse
    {
        $me = $request->user();

        // Must have unlocked the place
        if (!$me->unlockedPlaces()->where('places.id', $place->id)->exists()) {
            return response()->json(['message' => 'You must unlock this place before reviewing.'], 422);
        }

        // One review per place per user
        if (Review::where('user_id', $me->id)->where('reviewable_type', 'place')->where('reviewable_id', $place->id)->exists()) {
            return response()->json(['message' => 'You already reviewed this place.'], 422);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:10240'],
        ]);

        $review = Review::create([
            'user_id' => $me->id,
            'reviewable_type' => 'place',
            'reviewable_id' => $place->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'] ?? null,
        ]);

        $this->savePhotos($request, $review);
        $review->load(['reviewer:id,name,username,avatar_path', 'photos']);

        return response()->json(['data' => $review], 201);
    }

    public function placeReviews(Request $request, Place $place): JsonResponse
    {
        $reviews = Review::where('reviewable_type', 'place')
            ->where('reviewable_id', $place->id)
            ->with(['reviewer:id,name,username,avatar_path,level', 'photos'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        $avg = Review::where('reviewable_type', 'place')->where('reviewable_id', $place->id)->avg('rating');
        $count = Review::where('reviewable_type', 'place')->where('reviewable_id', $place->id)->count();

        return response()->json([
            'average_rating' => round($avg, 1),
            'total_reviews' => $count,
            'reviews' => $reviews,
        ]);
    }

    // ── Organizer Reviews (by joiners) ──

    public function createOrganizerReview(Request $request, User $user): JsonResponse
    {
        $me = $request->user();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:2000'],
            'event_id' => ['required', 'exists:events,id'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:10240'],
        ]);

        $event = Event::findOrFail($validated['event_id']);

        // Event must be completed
        if ($event->status !== EventStatus::Completed) {
            return response()->json(['message' => 'Event must be completed before reviewing.'], 422);
        }

        // Must be an approved attendee
        if (!$event->bookings()->where('user_id', $me->id)->where('status', BookingStatus::Approved)->exists()) {
            return response()->json(['message' => 'You must have attended this event.'], 422);
        }

        // Organizer must match
        if ($event->organizer_id !== $user->id) {
            return response()->json(['message' => 'This user is not the organizer of this event.'], 422);
        }

        // One review per organizer per event
        if (Review::where('user_id', $me->id)->where('reviewable_type', 'organizer')->where('reviewable_id', $user->id)->where('event_id', $event->id)->exists()) {
            return response()->json(['message' => 'You already reviewed this organizer for this event.'], 422);
        }

        $review = Review::create([
            'user_id' => $me->id,
            'reviewable_type' => 'organizer',
            'reviewable_id' => $user->id,
            'event_id' => $event->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'] ?? null,
        ]);

        $this->savePhotos($request, $review);
        $review->load(['reviewer:id,name,username,avatar_path', 'event:id,title,slug', 'photos']);

        return response()->json(['data' => $review], 201);
    }

    public function organizerReviews(Request $request, User $user): JsonResponse
    {
        $reviews = Review::where('reviewable_type', 'organizer')
            ->where('reviewable_id', $user->id)
            ->with(['reviewer:id,name,username,avatar_path,level', 'event:id,title,slug', 'photos'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        $avg = Review::where('reviewable_type', 'organizer')->where('reviewable_id', $user->id)->avg('rating');
        $count = Review::where('reviewable_type', 'organizer')->where('reviewable_id', $user->id)->count();

        return response()->json([
            'average_rating' => round($avg, 1),
            'total_reviews' => $count,
            'reviews' => $reviews,
        ]);
    }

    // ── Joiner Reviews (by organizer) ──

    public function createJoinerReview(Request $request, Event $event): JsonResponse
    {
        $me = $request->user();

        if ($event->organizer_id !== $me->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($event->status !== EventStatus::Completed) {
            return response()->json(['message' => 'Event must be completed before reviewing joiners.'], 422);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);

        // Must be an approved attendee
        if (!$event->bookings()->where('user_id', $validated['user_id'])->where('status', BookingStatus::Approved)->exists()) {
            return response()->json(['message' => 'This user did not attend this event.'], 422);
        }

        // One review per joiner per event
        if (Review::where('user_id', $me->id)->where('reviewable_type', 'joiner')->where('reviewable_id', $validated['user_id'])->where('event_id', $event->id)->exists()) {
            return response()->json(['message' => 'You already reviewed this joiner for this event.'], 422);
        }

        $review = Review::create([
            'user_id' => $me->id,
            'reviewable_type' => 'joiner',
            'reviewable_id' => $validated['user_id'],
            'event_id' => $event->id,
            'rating' => $validated['rating'],
            'content' => $validated['content'] ?? null,
        ]);

        $review->load(['reviewer:id,name,username,avatar_path', 'event:id,title,slug']);

        return response()->json(['data' => $review], 201);
    }

    public function joinerReviews(Request $request, User $user): JsonResponse
    {
        $reviews = Review::where('reviewable_type', 'joiner')
            ->where('reviewable_id', $user->id)
            ->with(['reviewer:id,name,username,avatar_path', 'event:id,title,slug'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        $avg = Review::where('reviewable_type', 'joiner')->where('reviewable_id', $user->id)->avg('rating');
        $count = Review::where('reviewable_type', 'joiner')->where('reviewable_id', $user->id)->count();

        return response()->json([
            'average_rating' => round($avg, 1),
            'total_reviews' => $count,
            'reviews' => $reviews,
        ]);
    }

    // ── Edit / Delete ──

    public function update(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:10240'],
            'remove_photos' => ['nullable', 'array'],
        ]);

        if (isset($validated['rating'])) $review->rating = $validated['rating'];
        if (array_key_exists('content', $validated)) $review->content = $validated['content'];
        $review->save();

        // Remove photos
        if (!empty($validated['remove_photos'])) {
            $photos = ReviewPhoto::where('review_id', $review->id)->whereIn('id', $validated['remove_photos'])->get();
            foreach ($photos as $p) { Storage::disk('s3')->delete($p->photo_path); $p->delete(); }
        }

        // Add new photos
        $this->savePhotos($request, $review);

        $review->load(['reviewer:id,name,username,avatar_path', 'photos']);
        return response()->json(['data' => $review]);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        foreach ($review->photos as $p) { Storage::disk('s3')->delete($p->photo_path); }
        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }

    // ── My Reviews ──

    public function myReviews(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['photos', 'event:id,title,slug'])
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json($reviews);
    }

    // ── Helper ──

    private function savePhotos(Request $request, Review $review): void
    {
        if ($request->hasFile('photos')) {
            $maxSort = $review->photos()->max('sort_order') ?? -1;
            foreach ($request->file('photos') as $i => $photo) {
                $path = Storage::disk('s3')->putFile('reviews', $photo);
                ReviewPhoto::create([
                    'review_id' => $review->id,
                    'photo_path' => $path,
                    'sort_order' => $maxSort + $i + 1,
                ]);
            }
        }
    }
}
