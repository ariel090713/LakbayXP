<?php

use App\Enums\BookingStatus;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Exceptions\NoSlotsAvailableException;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Services\BookingService;

// ── BookingService Unit Tests ──

test('BookingService creates a pending booking for a published event', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);

    $service = app(BookingService::class);
    $booking = $service->bookEvent($user, $event);

    expect($booking)->toBeInstanceOf(Booking::class)
        ->and($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->user_id)->toBe($user->id)
        ->and($booking->event_id)->toBe($event->id)
        ->and($booking->approved_at)->toBeNull();
});

test('BookingService auto-approves when auto_approve_bookings is true', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create([
        'max_slots' => 10,
        'auto_approve_bookings' => true,
    ]);

    $service = app(BookingService::class);
    $booking = $service->bookEvent($user, $event);

    expect($booking->status)->toBe(BookingStatus::Approved)
        ->and($booking->approved_at)->not->toBeNull();
});

test('BookingService rejects booking for non-published event', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->create(['status' => EventStatus::Draft]);

    $service = app(BookingService::class);
    $service->bookEvent($user, $event);
})->throws(InvalidArgumentException::class, 'Only published events can be booked.');

test('BookingService rejects booking for past event', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create([
        'event_date' => now()->subDay(),
    ]);

    $service = app(BookingService::class);
    $service->bookEvent($user, $event);
})->throws(InvalidArgumentException::class, 'Cannot book an event with a past date.');

test('BookingService rejects duplicate booking', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);

    $service = app(BookingService::class);
    $service->bookEvent($user, $event);

    // Second booking should fail
    $service->bookEvent($user, $event);
})->throws(InvalidArgumentException::class, 'You already have an active booking for this event.');

test('BookingService throws NoSlotsAvailableException when event is full', function () {
    $event = Event::factory()->published()->create(['max_slots' => 1]);
    $firstUser = User::factory()->create(['role' => UserRole::User]);

    $service = app(BookingService::class);
    $service->bookEvent($firstUser, $event);

    $secondUser = User::factory()->create(['role' => UserRole::User]);
    $service->bookEvent($secondUser, $event);
})->throws(NoSlotsAvailableException::class);

test('BookingService sets event to full when slots reach zero', function () {
    $event = Event::factory()->published()->create(['max_slots' => 1]);
    $user = User::factory()->create(['role' => UserRole::User]);

    $service = app(BookingService::class);
    $service->bookEvent($user, $event);

    $event->refresh();
    expect($event->status)->toBe(EventStatus::Full);
});

test('BookingService approveBooking sets status and approved_at', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'max_slots' => 10,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $result = $service->approveBooking($organizer, $booking);

    expect($result->status)->toBe(BookingStatus::Approved)
        ->and($result->approved_at)->not->toBeNull();
});

test('BookingService approveBooking rejects non-owner', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $otherOrganizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'max_slots' => 10,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $service->approveBooking($otherOrganizer, $booking);
})->throws(\Illuminate\Auth\Access\AuthorizationException::class);

test('BookingService approveBooking sets event to full when slots reach zero', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'max_slots' => 1,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $service->approveBooking($organizer, $booking);

    $event->refresh();
    expect($event->status)->toBe(EventStatus::Full);
});

test('BookingService rejectBooking sets status and rejected_at', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $result = $service->rejectBooking($organizer, $booking);

    expect($result->status)->toBe(BookingStatus::Rejected)
        ->and($result->rejected_at)->not->toBeNull();
});

test('BookingService rejectBooking rejects non-owner', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $otherOrganizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $service->rejectBooking($otherOrganizer, $booking);
})->throws(\Illuminate\Auth\Access\AuthorizationException::class);

test('BookingService cancelBooking sets status to cancelled', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $result = $service->cancelBooking($user, $booking);

    expect($result->status)->toBe(BookingStatus::Cancelled);
});

test('BookingService cancelBooking rejects non-owner', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $otherUser = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => BookingStatus::Pending,
    ]);

    $service = app(BookingService::class);
    $service->cancelBooking($otherUser, $booking);
})->throws(\Illuminate\Auth\Access\AuthorizationException::class);

// ── API Controller Tests ──

test('API book event creates booking', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/events/{$event->id}/book");

    $response->assertStatus(201)
        ->assertJsonPath('booking.event_id', $event->id)
        ->assertJsonPath('booking.user_id', $user->id);
});

test('API book event returns 422 for full event', function () {
    $event = Event::factory()->published()->create(['max_slots' => 1]);
    $firstUser = User::factory()->create(['role' => UserRole::User]);
    Booking::factory()->create([
        'event_id' => $event->id,
        'user_id' => $firstUser->id,
        'status' => BookingStatus::Pending,
    ]);

    $secondUser = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($secondUser, 'sanctum')
        ->postJson("/api/events/{$event->id}/book");

    $response->assertStatus(422);
});

test('API cancel booking works for own booking', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => BookingStatus::Pending,
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/bookings/{$booking->id}");

    $response->assertOk()
        ->assertJsonPath('booking.status', 'cancelled');
});

test('API cancel booking returns 403 for other user', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $otherUser = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'status' => BookingStatus::Pending,
    ]);

    $response = $this->actingAs($otherUser, 'sanctum')
        ->deleteJson("/api/bookings/{$booking->id}");

    $response->assertStatus(403);
});

// ── Web Controller Tests ──

test('organizer can view bookings index for own event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'max_slots' => 10,
    ]);
    Booking::factory()->count(3)->create(['event_id' => $event->id]);

    $response = $this->actingAs($organizer)
        ->get(route('organizer.bookings.index', $event));

    $response->assertOk();
});

test('organizer can approve a pending booking', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
        'max_slots' => 10,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $response = $this->actingAs($organizer)
        ->post(route('organizer.bookings.approve', $booking));

    $response->assertRedirect(route('organizer.bookings.index', $event->id));
    expect($booking->refresh()->status)->toBe(BookingStatus::Approved);
});

test('organizer can reject a pending booking', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create([
        'organizer_id' => $organizer->id,
    ]);
    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::Pending,
    ]);

    $response = $this->actingAs($organizer)
        ->post(route('organizer.bookings.reject', $booking));

    $response->assertRedirect(route('organizer.bookings.index', $event->id));
    expect($booking->refresh()->status)->toBe(BookingStatus::Rejected);
});

test('non-organizer cannot access booking routes', function () {
    $user = User::factory()->create(['role' => UserRole::User]);
    $event = Event::factory()->published()->create(['max_slots' => 10]);

    $response = $this->actingAs($user)
        ->get(route('organizer.bookings.index', $event));

    $response->assertStatus(403);
});
