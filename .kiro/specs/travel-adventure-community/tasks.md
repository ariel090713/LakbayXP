# Implementation Plan: Travel Adventure Community Platform

## Overview

Implement a Laravel-based travel adventure community platform with event booking, social profiles, gamified achievements, dual auth (Firebase for mobile API, session for web dashboards), S3 file storage, and FCM push notifications. Built with Livewire, Volt, Flux UI, and Tailwind CSS.

## Tasks

- [x] 1. Database schema, enums, and base model setup
  - [x] 1.1 Create PHP enums for UserRole, PlaceCategory, EventStatus, BookingStatus, UnlockMethod, ExplorerLevel
    - Create `app/Enums/UserRole.php`, `PlaceCategory.php`, `EventStatus.php`, `BookingStatus.php`, `UnlockMethod.php`, `ExplorerLevel.php` as string-backed enums matching the design
    - _Requirements: 1A.3, 2.2, 3.1, 4.1, 7.1, 8.1–8.4_

  - [x] 1.2 Create migration to add columns to the users table
    - Add `username` (unique), `bio`, `avatar_path`, `role` (default 'user'), `firebase_uid` (unique nullable), `google_id` (nullable), `explorer_level` (default 'beginner_explorer'), `is_verified_organizer` (default false), `fcm_token` (nullable) to the existing users table
    - Make `password` nullable (Firebase users may not have one)
    - _Requirements: 1A.3, 1A.4, 1B.1, 1B.2, 1B.5, 8.5, 11.1_

  - [x] 1.3 Create migrations for places, events, bookings, place_unlocks, badges, user_badges, badge_event, event_photos, and follows tables
    - Follow the exact schema from the design document including all foreign keys, unique constraints, indexes, and cascade deletes
    - Ensure `bookings(event_id, user_id)` unique, `place_unlocks(user_id, place_id)` unique, `user_badges(user_id, badge_id)` unique, `follows(follower_id, following_id)` primary key
    - _Requirements: 17.1, 17.2, 17.3, 17.4, 17.6_

  - [x] 1.4 Update the User model with new fields, casts, and relationships
    - Add fillable fields, enum casts for `role` and `explorer_level`, relationships: `organizedEvents`, `bookings`, `unlockedPlaces` (BelongsToMany via place_unlocks), `badges` (BelongsToMany via user_badges), `followers`, `following` (BelongsToMany via follows)
    - Add `HasApiTokens` trait for Sanctum
    - _Requirements: 1A.3, 1B.1, 10.1, 10.2_

  - [x] 1.5 Create Eloquent models: Place, Event, Booking, PlaceUnlock, Badge, EventPhoto
    - Define fillable, casts (enums, JSON, dates), and all relationships per the design
    - Add `availableSlots()` method on Event model
    - _Requirements: 2.1, 3.1, 4.1, 7.1, 15.1_

  - [ ]* 1.6 Write property tests for database constraints
    - **Property 4: Duplicate bookings are rejected** — verify unique constraint on `bookings(event_id, user_id)`
    - **Property 10: Duplicate place unlocks are rejected** — verify unique constraint on `place_unlocks(user_id, place_id)`
    - **Property 13: Badge awarding idempotent** — verify unique constraint on `user_badges(user_id, badge_id)`
    - **Property 18: Follow uniqueness** — verify unique constraint on `follows(follower_id, following_id)`
    - **Property 24: Cascade deletion** — verify deleting a user cascades to bookings, place_unlocks, user_badges, follows
    - **Validates: Requirements 17.1, 17.2, 17.3, 17.4, 17.6**

- [x] 2. Checkpoint — Run migrations and verify schema
  - Ensure all migrations run cleanly on SQLite, all tests pass, ask the user if questions arise.

- [x] 3. Authentication system (dual auth: session web + Firebase API)
  - [x] 3.1 Install and configure Laravel Sanctum and kreait/laravel-firebase packages
    - Run `composer require laravel/sanctum kreait/laravel-firebase`
    - Publish Sanctum config, add `HasApiTokens` to User model (if not done in 1.4)
    - Configure Firebase credentials in `config/services.php` or `config/firebase.php`
    - _Requirements: 1B.1, 1B.6, 16.1_

  - [x] 3.2 Create the RoleMiddleware for role-based access control
    - Create `app/Http/Middleware/RoleMiddleware.php` that checks `$user->role` against the required role parameter
    - Register as `role` alias in bootstrap/app.php
    - Return 403 for API requests, redirect for web requests when role doesn't match
    - _Requirements: 16.2, 16.3, 16.4, 16.5, 16.6, 16.7_

  - [x] 3.3 Create AuthController with firebaseLogin endpoint
    - Create `app/Http/Controllers/Api/AuthController.php` with `firebaseLogin` method
    - Verify Firebase ID token via kreait, find or create user with role `user`, generate username from email prefix + random suffix, store `firebase_uid` and `google_id`, return Sanctum token
    - Add `updateFcmToken` method to store FCM token on authenticated user
    - _Requirements: 1B.1, 1B.2, 1B.3, 1B.4, 1B.5, 1B.7_

  - [x] 3.4 Configure API routes with Sanctum middleware
    - Set up `routes/api.php` with `auth:sanctum` middleware group for all authenticated endpoints
    - Add `role:organizer` middleware for organizer-only API endpoints
    - Add public `POST /api/auth/firebase` route outside auth middleware
    - _Requirements: 1B.6, 16.1, 16.7_

  - [x] 3.5 Configure web dashboard routes with session auth and role middleware
    - Add `/admin/*` routes with `['auth', 'role:admin']` middleware
    - Add `/organizer/*` routes with `['auth', 'role:organizer']` middleware
    - Ensure existing Livewire login/register views handle admin/organizer web auth
    - _Requirements: 1A.1, 1A.2, 1A.5, 1A.6, 16.5, 16.6, 16.7_

  - [ ]* 3.6 Write property tests for authentication and authorization
    - **Property 22: Role-based API authorization** — test 403 for wrong roles on organizer/admin endpoints, 401 without token, redirect for unauthenticated web requests
    - **Property 25: Registration creates user with correct defaults** — test Firebase registration creates user with role `user`, `beginner_explorer`, username from email; web registration creates admin/organizer with password hash
    - **Validates: Requirements 1A.1–1A.6, 1B.1–1B.7, 16.1–16.7**

- [x] 4. Checkpoint — Verify auth flows work
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Place management (admin CRUD)
  - [x] 5.1 Create PlaceService and admin Place controllers
    - Create `app/Services/PlaceService.php` with create, update, deactivate methods
    - Create `app/Http/Controllers/Admin/AdminPlaceController.php` (web resource controller)
    - Create `app/Http/Controllers/Api/PlaceController.php` (API index/show for mobile)
    - Validate PlaceCategory enum, enforce unique slug, handle category_fields JSON
    - _Requirements: 2.1, 2.2, 2.4, 2.5, 2.6_

  - [x] 5.2 Create admin Place Livewire/Volt views
    - Create Volt pages for place list, create, edit under `resources/views/livewire/admin/places/`
    - Include form fields: name, slug, description, category (select from PlaceCategory enum), region, province, coordinates, cover image upload, category_fields (dynamic based on category)
    - Use Flux UI components for forms and tables
    - _Requirements: 2.1, 2.2, 2.3, 2.6_

  - [x] 5.3 Implement S3 cover image upload for places
    - Use `Storage::disk('s3')->putFile('place-covers', ...)` for cover image uploads
    - Save path to `cover_image_path` on the place record
    - _Requirements: 2.3, 12.4_

  - [ ]* 5.4 Write property tests for place management
    - **Property 15: Deactivated places excluded from browsing** — verify inactive places don't appear in public queries
    - **Property 19: Unique slug enforcement** — verify duplicate slugs are rejected
    - **Property 20: PlaceCategory validation** — verify only valid enum values accepted
    - **Property 27: Place data round-trip** — verify all fields persist and read back correctly
    - **Validates: Requirements 2.1–2.6**

- [x] 6. Event management (organizer CRUD + publish/cancel)
  - [x] 6.1 Create EventService with lifecycle methods
    - Create `app/Services/EventService.php` with create, update, publish, cancel, complete methods
    - Publish: change status from `draft` to `published`
    - Cancel: set status to `cancelled`, notify approved booking users via FCM
    - Complete: covered in task 8
    - _Requirements: 3.1, 3.2, 3.4, 3.6_

  - [x] 6.2 Create organizer Event controllers and API controllers
    - Create `app/Http/Controllers/Organizer/OrganizerEventController.php` (web resource + publish/complete)
    - Create `app/Http/Controllers/Api/EventController.php` (API index/show/complete)
    - API browse returns only `published` or `full` events
    - _Requirements: 3.1, 3.2, 3.5, 3.6_

  - [x] 6.3 Create organizer Event Livewire/Volt views
    - Create Volt pages for event list, create, edit, show (with bookings) under `resources/views/livewire/organizer/events/`
    - Include badge attachment (badge_event pivot), form fields per design schema
    - Use Flux UI components
    - _Requirements: 3.1, 3.2, 3.3_

  - [ ]* 6.4 Write property tests for event management
    - **Property 17: Event browsing returns only published or full** — verify draft/completed/cancelled excluded from public browse
    - **Property 19: Unique slug enforcement for events** — verify duplicate event slugs rejected
    - **Property 28: Event creation defaults to draft** — verify new events have status `draft`
    - **Validates: Requirements 3.1–3.6**

- [x] 7. Booking system (book, approve, reject, cancel)
  - [x] 7.1 Create BookingService with bookEvent, approveBooking, rejectBooking methods
    - Create `app/Services/BookingService.php` implementing the booking algorithm from the design
    - Use `DB::transaction` with `lockForUpdate()` for race condition prevention
    - Validate: event is published, slots available, no duplicate booking, event_date not past
    - Auto-approve if `auto_approve_bookings` is true
    - Update event status to `full` when slots reach zero
    - Create custom `NoSlotsAvailableException`
    - _Requirements: 4.1–4.7, 5.1–5.5, 17.5_

  - [x] 7.2 Create booking API and web controllers
    - Create `app/Http/Controllers/Api/BookingController.php` (store, cancel)
    - Create `app/Http/Controllers/Organizer/OrganizerBookingController.php` (index, approve, reject)
    - Enforce organizer ownership check on approve/reject
    - _Requirements: 4.1, 5.1, 5.2, 5.5_

  - [x] 7.3 Create organizer booking management Volt views
    - Create Volt page for booking list per event under `resources/views/livewire/organizer/bookings/`
    - Show pending bookings with approve/reject actions, approved/rejected lists
    - Use Flux UI components
    - _Requirements: 5.1, 5.2_

  - [ ]* 7.4 Write property tests for booking system
    - **Property 2: Booking a published event with available slots produces valid booking** — test pending/auto-approved status
    - **Property 3: Event status becomes Full when slots reach zero** — test on booking creation and approval
    - **Property 4: Duplicate bookings are rejected** — test same user can't book twice
    - **Property 5: Booking a full event is rejected** — test NoSlotsAvailableException
    - **Property 6: Only event owner can approve/reject** — test non-owner gets rejected
    - **Property 7: Approval/rejection sets correct status and timestamps** — test approved_at/rejected_at
    - **Validates: Requirements 4.1–4.8, 5.1–5.5, 17.1, 17.5**

- [x] 8. Event completion and auto-unlock
  - [x] 8.1 Implement completeEvent in EventService
    - Validate organizer ownership, event status is published/full, event_date is past/today
    - Set status to `completed`
    - Iterate approved bookings, create PlaceUnlock with method `event_completion` for users who haven't already unlocked the place
    - Trigger badge evaluation for each attendee via AchievementService
    - _Requirements: 6.1–6.5_

  - [ ]* 8.2 Write property tests for event completion
    - **Property 8: Event completion auto-unlocks for all approved attendees** — verify exactly (N-K) unlocks created
    - **Property 9: Only event owner can complete, only for past/today dates** — verify non-owner and future date rejected
    - **Validates: Requirements 6.1–6.5**

- [x] 9. Place unlock system
  - [x] 9.1 Create UnlockService with unlockPlace method
    - Create `app/Services/UnlockService.php` implementing all unlock method preconditions from the design
    - Validate: place is active, no duplicate unlock, method-specific preconditions (event_completion requires completed event + approved booking, organizer_verification requires organizer verifier, admin_approval requires admin verifier)
    - Store proof photo on S3 if provided
    - After unlock: trigger AchievementService to recalculate level and check badges
    - _Requirements: 7.1–7.9_

  - [x] 9.2 Create unlock API controller
    - Create `app/Http/Controllers/Api/PlaceUnlockController.php` with store method
    - Accept unlock_method, optional proof photo, optional event_id, optional verifier_id
    - _Requirements: 7.1, 7.2_

  - [ ]* 9.3 Write property tests for place unlock system
    - **Property 10: Duplicate place unlocks rejected** — verify second unlock for same user+place fails
    - **Property 11: Unlock method preconditions enforced** — verify event_completion, organizer_verification, admin_approval preconditions
    - **Property 12: Unlock triggers level recalculation and badge evaluation** — verify level and badges updated after unlock
    - **Validates: Requirements 7.1–7.9**

- [x] 10. Checkpoint — Verify core booking and unlock flows
  - Ensure all tests pass, ask the user if questions arise.

- [x] 11. Achievement system (explorer levels + badges)
  - [x] 11.1 Create AchievementService with calculateExplorerLevel and checkAndAwardBadges
    - Create `app/Services/AchievementService.php`
    - `calculateExplorerLevel`: pure computation based on unlock count thresholds (0-4, 5-14, 15-29, 30+)
    - `checkAndAwardBadges`: evaluate all active badges not yet awarded, compare against user's unlock stats (total, by_category, by_region, current_streak), attach newly earned badges, recalculate explorer level
    - `getUserUnlockStats`: helper to compute total unlocks, per-category counts, per-region counts, streak
    - `meetsCriteria`: match badge criteria_type against stats
    - _Requirements: 8.1–8.5, 9.1–9.7_

  - [ ]* 11.2 Write property tests for achievement system
    - **Property 1: Explorer level determined by unlock count thresholds** — test all boundary values (0,4,5,14,15,29,30)
    - **Property 13: Badge awarding is idempotent and monotonic** — test multiple calls don't create duplicates, don't remove existing
    - **Property 14: Badge criteria evaluation matches criteria type** — test unlock_count, category_count, region_count, streak
    - **Property 16: Deactivated badges excluded from evaluation** — test inactive badges skipped
    - **Validates: Requirements 8.1–8.5, 9.1–9.8, 15.2**

- [x] 12. Admin badge management
  - [x] 12.1 Create admin Badge controllers and Volt views
    - Create `app/Http/Controllers/Admin/AdminBadgeController.php` (web resource controller)
    - Create `app/Http/Controllers/Api/BadgeController.php` (API index for mobile)
    - Create Volt pages for badge list, create, edit under `resources/views/livewire/admin/badges/`
    - Include criteria_type select, criteria_value JSON fields, icon upload, event attachment
    - _Requirements: 15.1–15.4_

  - [ ]* 12.2 Write unit tests for badge management
    - Test badge CRUD, unique slug enforcement, deactivation excludes from evaluation
    - _Requirements: 15.1–15.4_

- [x] 13. Notification system (FCM + database notifications)
  - [x] 13.1 Create NotificationService with FCM push methods
    - Create `app/Services/NotificationService.php` with `sendPush`, `notifyBookingCreated`, `notifyBookingStatusChanged`, `notifyBadgeAwarded` methods per the design
    - Gracefully skip if user has no `fcm_token`
    - Use `kreait/laravel-firebase` messaging
    - _Requirements: 13.1–13.4_

  - [x] 13.2 Integrate NotificationService into BookingService, EventService, and AchievementService
    - Call `notifyBookingCreated` after booking creation in BookingService
    - Call `notifyBookingStatusChanged` after approve/reject in BookingService
    - Call `notifyBadgeAwarded` after badge award in AchievementService
    - Call notification on event cancellation in EventService (notify approved booking users)
    - _Requirements: 4.8, 5.4, 9.8, 3.6_

  - [ ]* 13.3 Write property tests for notification system
    - **Property 21: Notifications sent for booking/badge events, graceful with missing FCM tokens** — test each notification trigger, test null fcm_token skips without error
    - **Validates: Requirements 4.8, 5.4, 9.8, 13.1–13.4**

- [x] 14. Social profile and follow system
  - [x] 14.1 Create ProfileController and FollowController for API
    - Create `app/Http/Controllers/Api/ProfileController.php` with show, unlocks, badges methods
    - Create `app/Http/Controllers/Api/FollowController.php` with store (follow) and destroy (unfollow)
    - Profile response includes: username, bio, avatar, explorer_level, unlocked_places_count, badges, per-category unlock counts
    - Prevent self-follow
    - _Requirements: 10.1–10.6_

  - [ ]* 14.2 Write property tests for social features
    - **Property 18: Follow/unfollow round-trip** — test follow then unfollow restores original state, self-follow rejected
    - **Property 26: Profile displays complete travel statistics** — test all profile fields present, per-category counts sum to total
    - **Validates: Requirements 10.1–10.6, 17.4**

- [x] 15. Leaderboard
  - [x] 15.1 Create LeaderboardController for API
    - Create `app/Http/Controllers/Api/LeaderboardController.php` with index method
    - Return users ranked by total unlocked places descending
    - Include username, explorer_level, unlock count, badge count per entry
    - _Requirements: 14.1, 14.2_

  - [ ]* 15.2 Write property test for leaderboard
    - **Property 23: Leaderboard sorted by unlock count descending** — verify ordering and included fields
    - **Validates: Requirements 14.1, 14.2**

- [x] 16. Organizer verification (admin action)
  - [x] 16.1 Create AdminOrganizerController for web dashboard
    - Create `app/Http/Controllers/Admin/AdminOrganizerController.php` with index and verify methods
    - Set `is_verified_organizer` to true on the organizer's user record
    - Only admin role can verify
    - Create Volt page for organizer list with verify action under `resources/views/livewire/admin/organizers/`
    - _Requirements: 11.1–11.3_

  - [ ]* 16.2 Write unit tests for organizer verification
    - Test admin can verify, non-admin rejected, verified status displayed
    - _Requirements: 11.1–11.3_

- [x] 17. File storage (S3 uploads for avatars, event photos, proof photos)
  - [x] 17.1 Implement file upload handling across controllers
    - Avatar upload in profile update (API + web): store under `avatars/` on S3
    - Event photo upload in OrganizerEventPhotoController: store under `event-photos/` on S3, create EventPhoto record
    - Proof photo upload in PlaceUnlockController: store under `proof-photos/` on S3
    - Generate public URLs via `Storage::disk('s3')->url()`
    - _Requirements: 12.1–12.4_

  - [ ]* 17.2 Write unit tests for file storage
    - Test each upload type stores to correct S3 directory, test URL generation
    - _Requirements: 12.1–12.4_

- [x] 18. Admin and organizer dashboard layouts
  - [x] 18.1 Create admin dashboard layout and landing page
    - Create layout component at `resources/views/components/layouts/admin.blade.php` with sidebar navigation (Places, Badges, Organizers)
    - Create `resources/views/livewire/admin/dashboard.blade.php` Volt page with summary stats
    - _Requirements: 1A.1_

  - [x] 18.2 Create organizer dashboard layout and landing page
    - Create layout component at `resources/views/components/layouts/organizer.blade.php` with sidebar navigation (Events, Bookings)
    - Create `resources/views/livewire/organizer/dashboard.blade.php` Volt page with event/booking summary
    - _Requirements: 1A.1_

- [x] 19. Wire all API routes together
  - [x] 19.1 Finalize routes/api.php with all endpoints
    - Wire all API controllers: auth, events, bookings, places, unlocks, profiles, follows, leaderboard, badges, FCM token
    - Apply correct middleware groups: public, auth:sanctum, role:organizer
    - Match the route structure from the design document
    - _Requirements: 1B.6, 16.1, 16.7_

  - [x] 19.2 Finalize routes/web.php with all admin and organizer routes
    - Wire all web controllers: admin places, badges, organizers, dashboard; organizer events, bookings, photos, dashboard
    - Apply correct middleware groups: auth + role:admin, auth + role:organizer
    - Match the route structure from the design document
    - _Requirements: 1A.1, 1A.2, 16.5, 16.6, 16.7_

- [x] 20. Database seeder
  - [x] 20.1 Create seeders for development data
    - Create factories and seeders for: admin user, organizer user, regular users, places (various categories), events (various statuses), bookings, badges, place_unlocks
    - Update `DatabaseSeeder.php` to run all seeders
    - _Requirements: all (development support)_

- [x] 21. Final checkpoint — Full test suite
  - Ensure all tests pass, run `php artisan migrate:fresh --seed` to verify seeder, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- The project uses PHP (Laravel) with Pest for testing
- SQLite for local dev, MySQL for production — migrations must be compatible with both
- All file uploads go to S3 via Laravel Storage facade
- Firebase Auth is mobile-only; web dashboards use Laravel session auth exclusively
