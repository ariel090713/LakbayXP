<?php

namespace App\Http\Controllers\Api;

use App\Enums\UnlockMethod;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Place;
use App\Services\UnlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class PlaceUnlockController extends Controller
{
    public function __construct(
        protected UnlockService $unlockService,
    ) {}

    /**
     * Unlock a place for the authenticated user.
     */
    public function store(Request $request, Place $place): JsonResponse
    {
        $validated = $request->validate([
            'unlock_method' => ['required', new Enum(UnlockMethod::class)],
            'proof_photo' => ['nullable', 'image', 'max:10240'],
            'event_id' => ['nullable', 'exists:events,id'],
        ]);

        $method = UnlockMethod::from($validated['unlock_method']);
        $event = isset($validated['event_id']) ? Event::find($validated['event_id']) : null;
        $proofPhotoPath = $request->hasFile('proof_photo') ? $request->file('proof_photo') : null;

        try {
            $unlock = $this->unlockService->unlockPlace(
                user: $request->user(),
                place: $place,
                method: $method,
                proofPhotoPath: $proofPhotoPath,
                event: $event,
            );

            return response()->json([
                'message' => 'Place unlocked successfully.',
                'unlock' => $unlock->load('place'),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
