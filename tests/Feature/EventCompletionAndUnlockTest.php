<?php

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\ExplorerLevel;
use App\Enums\PlaceCategory;
use App\Enums\UnlockMethod;
use App\Enums\UserRole;
use App\Models\Badge;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Place;
use App\Models\PlaceUnlock;
use App\Models\User;
use App\Services\AchievementService;
use App\Services\EventService;
use App\Services\UnlockService;
use Illuminate\Auth\Access\AuthorizationException;

// ── AchievementService Tests (Task 11.1) ──

test('calculateExplorerLevel returns BeginnerExplorer for 0-4 unlocks', function () {
    $user = User::factory()->create();
    $service = app(AchievementService::class);

    expect($service->calculateExplorerLevel($user))->toBe(ExplorerLevel::BeginnerExplorer);

    // Create 4 unlocks
    $places = Place::factory()->count(4)->create();
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    expect($service->calculateExplorerLevel($user))->toBe(ExplorerLevel::BeginnerExplorer);
});

test('calculateExplorerLevel returns WeekendWanderer for 5-14 unlocks', function () {
    $user = User::factory()->create();
    $places = Place::factory()->count(5)->create();
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    $service = app(AchievementService::class);
    expect($service->calculateExplorerLevel($user))->toBe(ExplorerLevel::WeekendWanderer);
});

test('calculateExplorerLevel returns TrailHunter for 15-29 unlocks', function () {
    $user = User::factory()->create();
    $places = Place::factory()->count(15)->create();
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    $service = app(AchievementService::class);
    expect($service->calculateExplorerLevel($user))->toBe(ExplorerLevel::TrailHunter);
});

test('calculateExplorerLevel returns SummitCollector for 30+ unlocks', function () {
    $user = User::factory()->create();
    $places = Place::factory()->count(30)->create();
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    $service = app(AchievementService::class);
    expect($service->calculateExplorerLevel($user))->toBe(ExplorerLevel::SummitCollector);
});

test('checkAndAwardBadges awards unlock_count badge when criteria met', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'name' => 'First Steps',
        'slug' => 'first-steps',
        'criteria_type' => 'unlock_count',
        'criteria_value' => ['count' => 3],
        'is_active' => true,
    ]);

    $places = Place::factory()->count(3)->create();
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    $service = app(AchievementService::class);
    $newBadges = $service->checkAndAwardBadges($user);

    expect($newBadges)->toHaveCount(1)
        ->and($newBadges->first()->id)->toBe($badge->id)
        ->and($user->badges()->count())->toBe(1);
});

test('checkAndAwardBadges awards category_count badge', function () {
    $user = User::factory()->create();
    $badge = Badge::create([
        'name' => 'Mountain Lover',
        'slug' => 'mountain-lover',
        'criteria_type' => 'category_count',
        'criteria_value' => ['category' => 'mountain', 'count' => 2],
        'is_active' => true,
    ]);

    $places = Place::factory()->count(2)->create(['category' => PlaceCategory::Mountain]);
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    $service = app(AchievementService::class);
    $newBadges = $service->checkAndAwardBadges($user);

    expect($newBadges)->toHaveCount(1)
        ->and($newBadges->first()->slug)->toBe('mountain-lover');
});

test('checkAndAwardBadges is idempotent', function () {
    $user = User::factory()->create();
    Badge::create([
        'name' => 'Explorer',
        'slug' => 'explorer',
        'criteria_type' => 'unlock_count',
        'criteria_value' => ['count' => 1],
        'is_active' => true,
    ]);

    $place = Place::factory()->create();
    PlaceUnlock::create([
        'user_id' => $user->id,
        'place_id' => $place->id,
        'unlock_method' => UnlockMethod::SelfReport,
    ]);

    $service = app(AchievementService::class);
    $first = $service->checkAndAwardBadges($user);
    $second = $service->checkAndAwardBadges($user);

    expect($first)->toHaveCount(1)
        ->and($second)->toHaveCount(0)
        ->and($user->badges()->count())->toBe(1);
});

test('checkAndAwardBadges skips inactive badges', function () {
    $user = User::factory()->create();
    Badge::create([
        'name' => 'Inactive Badge',
        'slug' => 'inactive-badge',
        'criteria_type' => 'unlock_count',
        'criteria_value' => ['count' => 1],
        'is_active' => false,
    ]);

    $place = Place::factory()->create();
    PlaceUnlock::create([
        'user_id' => $user->id,
        'place_id' => $place->id,
        'unlock_method' => UnlockMethod::SelfReport,
    ]);

    $service = app(AchievementService::class);
    $newBadges = $service->checkAndAwardBadges($user);

    expect($newBadges)->toHaveCount(0);
});

test('checkAndAwardBadges recalculates explorer level', function () {
    $user = User::factory()->create(['explorer_level' => ExplorerLevel::BeginnerExplorer]);
    $places = Place::factory()->count(5)->create();
    foreach ($places as $place) {
        PlaceUnlock::create([
            'user_id' => $user->id,
            'place_id' => $place->id,
            'unlock_method' => UnlockMethod::SelfReport,
        ]);
    }

    $service = app(AchievementService::class);
    $service->checkAndAwardBadges($user);

    expect($user->refresh()->explorer_level)->toBe(ExplorerLevel::WeekendWanderer);
});

// ── UnlockService Tests (Task 9.1) ──

test('UnlockService creates a place unlock via self_report', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();

    $service = app(UnlockService::class);
    $unlock = $service->unlockPlace($user, $place, UnlockMethod::SelfReport);

    expect($unlock)->toBeInstanceOf(PlaceUnlock::class)
        ->and($unlock->user_id)->toBe($user->id)
        ->and($unlock->place_id)->toBe($place->id)
        ->and($unlock->unlock_method)->toBe(UnlockMethod::SelfReport);
});

test('UnlockService rejects duplicate unlock', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();

    $service = app(UnlockService::class);
    $service->unlockPlace($user, $place, UnlockMethod::SelfReport);
    $service->unlockPlace($user, $place, UnlockMethod::SelfReport);
})->throws(InvalidArgumentException::class, 'You have already unlocked this place.');

test('UnlockService rejects inactive place', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create(['is_active' => false]);

    $service = app(UnlockService::class);
    $service->unlockPlace($user, $place, UnlockMethod::SelfReport);
})->throws(InvalidArgumentException::class, 'Cannot unlock an inactive place.');

test('UnlockService validates event_completion requires completed event', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();
    $event = Event::factory()->published()->create(['place_id' => $place->id]);

    $service = app(UnlockService::class);
    $service->unlockPlace($user, $place, UnlockMethod::EventCompletion, event: $event);
})->throws(InvalidArgumentException::class, 'Event must be completed');

test('UnlockService validates event_completion requires approved booking', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();
    $event = Event::factory()->completed()->create(['place_id' => $place->id]);

    $service = app(UnlockService::class);
    $service->unlockPlace($user, $place, UnlockMethod::EventCompletion, event: $event);
})->throws(InvalidArgumentException::class, 'User must have an approved booking');

test('UnlockService validates admin_approval requires admin verifier', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();
    $nonAdmin = User::factory()->create(['role' => UserRole::User]);

    $service = app(UnlockService::class);
    $service->unlockPlace($user, $place, UnlockMethod::AdminApproval, verifier: $nonAdmin);
})->throws(InvalidArgumentException::class, 'Verifier must have admin role');

test('UnlockService triggers badge evaluation after unlock', function () {
    $user = User::factory()->create(['explorer_level' => ExplorerLevel::BeginnerExplorer]);
    Badge::create([
        'name' => 'First Unlock',
        'slug' => 'first-unlock',
        'criteria_type' => 'unlock_count',
        'criteria_value' => ['count' => 1],
        'is_active' => true,
    ]);

    $place = Place::factory()->create();

    $service = app(UnlockService::class);
    $service->unlockPlace($user, $place, UnlockMethod::SelfReport);

    expect($user->badges()->count())->toBe(1);
});

// ── EventService::completeEvent Tests (Task 8.1) ──

test('completeEvent sets status to completed', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->subDay(),
    ]);

    $service = app(EventService::class);
    $completed = $service->completeEvent($organizer, $event);

    expect($completed->status)->toBe(EventStatus::Completed);
});

test('completeEvent auto-unlocks place for approved attendees', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $place = Place::factory()->create();
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'place_id' => $place->id,
        'event_date' => now()->subDay(),
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Booking::create(['event_id' => $event->id, 'user_id' => $user1->id, 'status' => BookingStatus::Approved, 'approved_at' => now()]);
    Booking::create(['event_id' => $event->id, 'user_id' => $user2->id, 'status' => BookingStatus::Approved, 'approved_at' => now()]);

    $service = app(EventService::class);
    $service->completeEvent($organizer, $event);

    expect(PlaceUnlock::where('place_id', $place->id)->count())->toBe(2)
        ->and(PlaceUnlock::where('user_id', $user1->id)->where('place_id', $place->id)->exists())->toBeTrue()
        ->and(PlaceUnlock::where('user_id', $user2->id)->where('place_id', $place->id)->exists())->toBeTrue();
});

test('completeEvent skips users who already unlocked the place', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $place = Place::factory()->create();
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'place_id' => $place->id,
        'event_date' => now()->subDay(),
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Booking::create(['event_id' => $event->id, 'user_id' => $user1->id, 'status' => BookingStatus::Approved, 'approved_at' => now()]);
    Booking::create(['event_id' => $event->id, 'user_id' => $user2->id, 'status' => BookingStatus::Approved, 'approved_at' => now()]);

    // user1 already unlocked
    PlaceUnlock::create(['user_id' => $user1->id, 'place_id' => $place->id, 'unlock_method' => UnlockMethod::SelfReport]);

    $service = app(EventService::class);
    $service->completeEvent($organizer, $event);

    // Only user2 should get a new unlock
    expect(PlaceUnlock::where('place_id', $place->id)->count())->toBe(2)
        ->and(PlaceUnlock::where('user_id', $user2->id)->where('unlock_method', UnlockMethod::EventCompletion)->exists())->toBeTrue();
});

test('completeEvent does not unlock for pending/rejected bookings', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $place = Place::factory()->create();
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'place_id' => $place->id,
        'event_date' => now()->subDay(),
    ]);

    $pendingUser = User::factory()->create();
    $rejectedUser = User::factory()->create();
    Booking::create(['event_id' => $event->id, 'user_id' => $pendingUser->id, 'status' => BookingStatus::Pending]);
    Booking::create(['event_id' => $event->id, 'user_id' => $rejectedUser->id, 'status' => BookingStatus::Rejected]);

    $service = app(EventService::class);
    $service->completeEvent($organizer, $event);

    expect(PlaceUnlock::count())->toBe(0);
});

test('completeEvent rejects non-owner', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $other = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->subDay(),
    ]);

    $service = app(EventService::class);
    $service->completeEvent($other, $event);
})->throws(AuthorizationException::class);

test('completeEvent rejects future event date', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->addWeek(),
    ]);

    $service = app(EventService::class);
    $service->completeEvent($organizer, $event);
})->throws(InvalidArgumentException::class, 'Cannot complete an event with a future date');

test('completeEvent rejects draft event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::Draft,
        'event_date' => now()->subDay(),
    ]);

    $service = app(EventService::class);
    $service->completeEvent($organizer, $event);
})->throws(InvalidArgumentException::class, 'Only published or full events can be completed');


// ── API EventController::complete Tests (Task 8.1) ──

test('API complete event returns success for valid request', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->subDay(),
    ]);

    $response = $this->actingAs($organizer, 'sanctum')
        ->postJson("/api/events/{$event->id}/complete");

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Event completed successfully.']);

    expect($event->refresh()->status)->toBe(EventStatus::Completed);
});

test('API complete event returns 403 for non-owner', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $other = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->subDay(),
    ]);

    $response = $this->actingAs($other, 'sanctum')
        ->postJson("/api/events/{$event->id}/complete");

    $response->assertStatus(403);
});

test('API complete event returns 422 for future date', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->addWeek(),
    ]);

    $response = $this->actingAs($organizer, 'sanctum')
        ->postJson("/api/events/{$event->id}/complete");

    $response->assertStatus(422);
});

// ── API PlaceUnlockController Tests (Task 9.2) ──

test('API unlock place via self_report', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/places/{$place->id}/unlock", [
            'unlock_method' => 'self_report',
        ]);

    $response->assertStatus(201)
        ->assertJsonFragment(['message' => 'Place unlocked successfully.']);

    expect(PlaceUnlock::where('user_id', $user->id)->where('place_id', $place->id)->exists())->toBeTrue();
});

test('API unlock place rejects duplicate', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();
    PlaceUnlock::create([
        'user_id' => $user->id,
        'place_id' => $place->id,
        'unlock_method' => UnlockMethod::SelfReport,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/places/{$place->id}/unlock", [
            'unlock_method' => 'self_report',
        ]);

    $response->assertStatus(422);
});

test('API unlock place rejects invalid method', function () {
    $user = User::factory()->create();
    $place = Place::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/places/{$place->id}/unlock", [
            'unlock_method' => 'invalid_method',
        ]);

    $response->assertStatus(422);
});

// ── Organizer Web Complete Event (Task 8.1) ──

test('organizer can complete event via web', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'event_date' => now()->subDay(),
    ]);

    $response = $this->actingAs($organizer)
        ->post(route('organizer.events.complete', $event));

    $response->assertRedirect(route('organizer.events.show', $event));
    expect($event->refresh()->status)->toBe(EventStatus::Completed);
});
