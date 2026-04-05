<?php

use App\Enums\PlaceCategory;
use App\Enums\UserRole;
use App\Models\Place;
use App\Models\User;
use App\Services\PlaceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

// ── PlaceService Tests ──

test('PlaceService creates a place with valid data', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $service = app(PlaceService::class);
    $place = $service->create([
        'name' => 'Mt. Pulag',
        'slug' => 'mt-pulag',
        'description' => 'Third highest mountain in the Philippines',
        'category' => 'mountain',
        'region' => 'Cordillera',
        'province' => 'Benguet',
        'latitude' => 16.5870,
        'longitude' => 120.8830,
        'category_fields' => ['difficulty' => 'moderate', 'meters_above_sea_level' => 2922],
        'is_active' => true,
        'created_by' => $admin->id,
    ]);

    expect($place)->toBeInstanceOf(Place::class)
        ->and($place->name)->toBe('Mt. Pulag')
        ->and($place->slug)->toBe('mt-pulag')
        ->and($place->category)->toBe(PlaceCategory::Mountain)
        ->and($place->region)->toBe('Cordillera')
        ->and($place->category_fields)->toBe(['difficulty' => 'moderate', 'meters_above_sea_level' => 2922])
        ->and($place->is_active)->toBeTrue();
});

test('PlaceService rejects invalid category', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $service = app(PlaceService::class);
    $service->create([
        'name' => 'Bad Place',
        'slug' => 'bad-place',
        'category' => 'invalid_category',
        'created_by' => $admin->id,
    ]);
})->throws(ValidationException::class);

test('PlaceService updates a place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id]);

    $service = app(PlaceService::class);
    $updated = $service->update($place, [
        'name' => 'Updated Name',
        'category' => 'beach',
    ]);

    expect($updated->name)->toBe('Updated Name')
        ->and($updated->category)->toBe(PlaceCategory::Beach);
});

test('PlaceService deactivates a place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id, 'is_active' => true]);

    $service = app(PlaceService::class);
    $deactivated = $service->deactivate($place);

    expect($deactivated->is_active)->toBeFalse();
});


// ── AdminPlaceController Tests ──

test('admin can view places index', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Place::factory()->count(3)->create(['created_by' => $admin->id]);

    $response = $this->actingAs($admin)->get(route('admin.places.index'));

    $response->assertStatus(200);
});

test('admin can store a new place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post(route('admin.places.store'), [
        'name' => 'Kawasan Falls',
        'slug' => 'kawasan-falls',
        'description' => 'Multi-layered waterfall',
        'category' => 'falls',
        'region' => 'Central Visayas',
        'province' => 'Cebu',
        'latitude' => 9.8100,
        'longitude' => 123.8600,
        'category_fields' => ['difficulty' => 'easy'],
    ]);

    $response->assertRedirect(route('admin.places.index'));
    expect(Place::where('slug', 'kawasan-falls')->exists())->toBeTrue();
});

test('admin store validates required fields', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post(route('admin.places.store'), []);

    $response->assertSessionHasErrors(['name', 'slug', 'category']);
});

test('admin store rejects duplicate slug', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Place::factory()->create(['slug' => 'existing-slug', 'created_by' => $admin->id]);

    $response = $this->actingAs($admin)->post(route('admin.places.store'), [
        'name' => 'Another Place',
        'slug' => 'existing-slug',
        'category' => 'beach',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

test('admin store rejects invalid category', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->post(route('admin.places.store'), [
        'name' => 'Bad Place',
        'slug' => 'bad-place',
        'category' => 'not_a_category',
    ]);

    $response->assertSessionHasErrors(['category']);
});

test('admin can update a place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id]);

    $response = $this->actingAs($admin)->put(route('admin.places.update', $place), [
        'name' => 'Updated Place',
        'slug' => $place->slug,
        'category' => 'island',
    ]);

    $response->assertRedirect(route('admin.places.index'));
    expect($place->refresh()->name)->toBe('Updated Place');
});

test('admin destroy deactivates a place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id, 'is_active' => true]);

    $response = $this->actingAs($admin)->delete(route('admin.places.destroy', $place));

    $response->assertRedirect(route('admin.places.index'));
    expect($place->refresh()->is_active)->toBeFalse();
});

test('non-admin cannot access admin place routes', function () {
    $user = User::factory()->create(['role' => UserRole::User]);

    $response = $this->actingAs($user)->get(route('admin.places.index'));

    $response->assertStatus(403);
});

test('admin can store a place with cover image uploaded to S3', function () {
    Storage::fake('s3');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('cover.jpg', 800, 600);

    $response = $this->actingAs($admin)->post(route('admin.places.store'), [
        'name' => 'Image Place',
        'slug' => 'image-place',
        'category' => 'beach',
        'cover_image' => $file,
    ]);

    $response->assertRedirect(route('admin.places.index'));
    $place = Place::where('slug', 'image-place')->first();
    expect($place)->not->toBeNull()
        ->and($place->cover_image_path)->not->toBeNull()
        ->and($place->cover_image_path)->toStartWith('place-covers/');
    Storage::disk('s3')->assertExists($place->cover_image_path);
});

test('admin can update a place with new cover image uploaded to S3', function () {
    Storage::fake('s3');
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id]);

    $file = UploadedFile::fake()->image('new-cover.png', 1024, 768);

    $response = $this->actingAs($admin)->put(route('admin.places.update', $place), [
        'name' => $place->name,
        'slug' => $place->slug,
        'category' => $place->category->value,
        'cover_image' => $file,
    ]);

    $response->assertRedirect(route('admin.places.index'));
    $place->refresh();
    expect($place->cover_image_path)->not->toBeNull()
        ->and($place->cover_image_path)->toStartWith('place-covers/');
    Storage::disk('s3')->assertExists($place->cover_image_path);
});

test('admin store rejects non-image cover file', function () {
    Storage::fake('s3');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->actingAs($admin)->post(route('admin.places.store'), [
        'name' => 'Bad File Place',
        'slug' => 'bad-file-place',
        'category' => 'mountain',
        'cover_image' => $file,
    ]);

    $response->assertSessionHasErrors(['cover_image']);
});

test('admin store rejects oversized cover image', function () {
    Storage::fake('s3');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->image('huge.jpg')->size(6000); // 6MB, exceeds 5MB limit

    $response = $this->actingAs($admin)->post(route('admin.places.store'), [
        'name' => 'Big Image Place',
        'slug' => 'big-image-place',
        'category' => 'mountain',
        'cover_image' => $file,
    ]);

    $response->assertSessionHasErrors(['cover_image']);
});

// ── API PlaceController Tests ──

test('API returns only active places', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Place::factory()->create(['created_by' => $admin->id, 'is_active' => true, 'name' => 'Active Place']);
    Place::factory()->create(['created_by' => $admin->id, 'is_active' => false, 'name' => 'Inactive Place']);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/places');

    $response->assertStatus(200);
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('Active Place')
        ->and($names)->not->toContain('Inactive Place');
});

test('API filters places by category', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Place::factory()->create(['created_by' => $admin->id, 'category' => PlaceCategory::Mountain, 'is_active' => true]);
    Place::factory()->create(['created_by' => $admin->id, 'category' => PlaceCategory::Beach, 'is_active' => true]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/places?category=mountain');

    $response->assertStatus(200);
    $categories = collect($response->json('data'))->pluck('category')->unique();
    expect($categories)->toHaveCount(1)
        ->and($categories->first())->toBe('mountain');
});

test('API filters places by search name', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    Place::factory()->create(['created_by' => $admin->id, 'name' => 'Mt. Pulag', 'is_active' => true]);
    Place::factory()->create(['created_by' => $admin->id, 'name' => 'Boracay Beach', 'is_active' => true]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/places?search=Pulag');

    $response->assertStatus(200);
    $names = collect($response->json('data'))->pluck('name');
    expect($names)->toContain('Mt. Pulag')
        ->and($names)->not->toContain('Boracay Beach');
});

test('API show returns active place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id, 'is_active' => true]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/places/{$place->slug}");

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => $place->name]);
});

test('API show returns 404 for inactive place', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $place = Place::factory()->create(['created_by' => $admin->id, 'is_active' => false]);

    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson("/api/places/{$place->slug}");

    $response->assertStatus(404);
});
