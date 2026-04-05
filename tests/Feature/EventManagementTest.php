<?php

use App\Enums\EventStatus;
use App\Enums\PlaceCategory;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\Place;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Auth\Access\AuthorizationException;

// ── EventService Tests ──

test('EventService creates an event with draft status', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $place = Place::factory()->create();

    $service = app(EventService::class);
    $event = $service->create($organizer, [
        'title' => 'Mt. Pulag Adventure',
        'slug' => 'mt-pulag-adventure',
        'place_id' => $place->id,
        'category' => PlaceCategory::Mountain,
        'event_date' => now()->addMonth()->toDateString(),
        'max_slots' => 20,
        'fee' => 3500,
    ]);

    expect($event)->toBeInstanceOf(Event::class)
        ->and($event->status)->toBe(EventStatus::Draft)
        ->and($event->organizer_id)->toBe($organizer->id)
        ->and($event->title)->toBe('Mt. Pulag Adventure')
        ->and($event->max_slots)->toBe(20);
});

test('EventService updates an event', function () {
    $event = Event::factory()->create();

    $service = app(EventService::class);
    $updated = $service->update($event, ['title' => 'Updated Title']);

    expect($updated->title)->toBe('Updated Title');
});

test('EventService publishes a draft event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::Draft,
    ]);

    $service = app(EventService::class);
    $published = $service->publish($organizer, $event);

    expect($published->status)->toBe(EventStatus::Published);
});

test('EventService rejects publishing a non-draft event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::Published,
    ]);

    $service = app(EventService::class);
    $service->publish($organizer, $event);
})->throws(InvalidArgumentException::class);

test('EventService rejects publish by non-owner', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $otherOrganizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    $service = app(EventService::class);
    $service->publish($otherOrganizer, $event);
})->throws(AuthorizationException::class);

test('EventService cancels an event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);

    $service = app(EventService::class);
    $cancelled = $service->cancel($organizer, $event);

    expect($cancelled->status)->toBe(EventStatus::Cancelled);
});

test('EventService rejects cancel by non-owner', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $otherOrganizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);

    $service = app(EventService::class);
    $service->cancel($otherOrganizer, $event);
})->throws(AuthorizationException::class);

// ── OrganizerEventController Tests ──

test('organizer can view events index', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    Event::factory()->count(3)->create(['organizer_id' => $organizer->id]);

    $response = $this->actingAs($organizer)->get(route('organizer.events.index'));

    $response->assertStatus(200);
});

test('organizer can view create event form', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);

    $response = $this->actingAs($organizer)->get(route('organizer.events.create'));

    $response->assertStatus(200);
});

test('organizer can store a new event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $place = Place::factory()->create();

    $response = $this->actingAs($organizer)->post(route('organizer.events.store'), [
        'title' => 'Beach Cleanup',
        'slug' => 'beach-cleanup',
        'place_id' => $place->id,
        'category' => 'beach',
        'event_date' => now()->addMonth()->toDateString(),
        'max_slots' => 15,
        'fee' => 0,
    ]);

    $response->assertRedirect(route('organizer.events.index'));
    expect(Event::where('slug', 'beach-cleanup')->exists())->toBeTrue();
    expect(Event::where('slug', 'beach-cleanup')->first()->status)->toBe(EventStatus::Draft);
});

test('organizer store validates required fields', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);

    $response = $this->actingAs($organizer)->post(route('organizer.events.store'), []);

    $response->assertSessionHasErrors(['title', 'slug', 'place_id', 'category', 'event_date', 'max_slots']);
});

test('organizer store rejects duplicate slug', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    Event::factory()->create(['slug' => 'existing-slug']);
    $place = Place::factory()->create();

    $response = $this->actingAs($organizer)->post(route('organizer.events.store'), [
        'title' => 'Another Event',
        'slug' => 'existing-slug',
        'place_id' => $place->id,
        'category' => 'mountain',
        'event_date' => now()->addMonth()->toDateString(),
        'max_slots' => 10,
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('organizer can update an event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $place = Place::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $organizer->id, 'place_id' => $place->id]);

    $response = $this->actingAs($organizer)->put(route('organizer.events.update', $event), [
        'title' => 'Updated Event',
        'slug' => $event->slug,
        'place_id' => $place->id,
        'category' => $event->category->value,
        'event_date' => now()->addMonth()->toDateString(),
        'max_slots' => 25,
    ]);

    $response->assertRedirect(route('organizer.events.index'));
    expect($event->refresh()->title)->toBe('Updated Event');
});

test('organizer can publish a draft event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => EventStatus::Draft,
    ]);

    $response = $this->actingAs($organizer)->post(route('organizer.events.publish', $event));

    $response->assertRedirect(route('organizer.events.show', $event));
    expect($event->refresh()->status)->toBe(EventStatus::Published);
});

test('organizer can view event show page', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create(['organizer_id' => $organizer->id]);

    $response = $this->actingAs($organizer)->get(route('organizer.events.show', $event));

    $response->assertStatus(200);
});

test('non-organizer cannot access organizer event routes', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($user)->get(route('organizer.events.index'));

    $response->assertStatus(403);
});

// ── API EventController Tests ──

test('API returns only published and full events', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    Event::factory()->create(['organizer_id' => $organizer->id, 'status' => EventStatus::Draft, 'title' => 'Draft Event']);
    Event::factory()->create(['organizer_id' => $organizer->id, 'status' => EventStatus::Published, 'title' => 'Published Event']);
    Event::factory()->create(['organizer_id' => $organizer->id, 'status' => EventStatus::Full, 'title' => 'Full Event']);
    Event::factory()->create(['organizer_id' => $organizer->id, 'status' => EventStatus::Cancelled, 'title' => 'Cancelled Event']);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/events');

    $response->assertStatus(200);
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('Published Event')
        ->and($titles)->toContain('Full Event')
        ->and($titles)->not->toContain('Draft Event')
        ->and($titles)->not->toContain('Cancelled Event');
});

test('API filters events by category', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    Event::factory()->published()->create(['organizer_id' => $organizer->id, 'category' => PlaceCategory::Mountain]);
    Event::factory()->published()->create(['organizer_id' => $organizer->id, 'category' => PlaceCategory::Beach]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/events?category=mountain');

    $response->assertStatus(200);
    $categories = collect($response->json('data'))->pluck('category')->unique();
    expect($categories)->toHaveCount(1)
        ->and($categories->first())->toBe('mountain');
});

test('API filters events by search', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    Event::factory()->published()->create(['organizer_id' => $organizer->id, 'title' => 'Mt. Pulag Adventure']);
    Event::factory()->published()->create(['organizer_id' => $organizer->id, 'title' => 'Beach Party']);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/events?search=Pulag');

    $response->assertStatus(200);
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('Mt. Pulag Adventure')
        ->and($titles)->not->toContain('Beach Party');
});

test('API show returns published event with place and organizer', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->published()->create(['organizer_id' => $organizer->id]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/events/{$event->slug}");

    $response->assertStatus(200)
        ->assertJsonFragment(['title' => $event->title]);
});

test('API show returns 404 for draft event', function () {
    $organizer = User::factory()->create(['role' => UserRole::Organizer]);
    $event = Event::factory()->create(['organizer_id' => $organizer->id, 'status' => EventStatus::Draft]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/events/{$event->slug}");

    $response->assertStatus(404);
});
