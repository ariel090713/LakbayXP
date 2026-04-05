# Requirements Document

## Introduction

The Travel Adventure Community Platform is a Laravel-based application that combines event booking, social profiles, and gamified achievement tracking for outdoor travel and hiking communities in the Philippines. Organizers create events tied to admin-defined places. Users browse, book, and attend events to unlock places, earn badges, and climb explorer levels. The platform serves a website (Livewire/Volt/Flux), admin and organizer dashboards (Laravel session auth), and a JSON API for mobile apps (Firebase Auth → Sanctum tokens).

## Glossary

- **Platform**: The Travel Adventure Community application including web, dashboards, and API
- **Admin**: A user with the `admin` role who manages places, badges, and organizer verification
- **Organizer**: A user with the `organizer` role who creates and manages events
- **User**: A registered user with the `user` role who browses, books events, and builds a travel profile
- **Place**: An admin-defined travel destination with a category and category-specific fields
- **PlaceCategory**: One of: mountain, beach, island, falls, river, lake, campsite, historical, food_destination, road_trip, hidden_gem
- **Event**: An organizer-created activity tied to a Place with date, fee, slots, and status
- **EventStatus**: One of: draft, published, full, completed, cancelled
- **Booking**: A record linking a User to an Event with approval workflow
- **BookingStatus**: One of: pending, approved, rejected, cancelled
- **PlaceUnlock**: A record proving a User visited a Place, created via one of the unlock methods
- **UnlockMethod**: One of: event_completion, photo_proof, self_report, organizer_verification, admin_approval, qr_code
- **Badge**: An achievement defined by Admin with criteria (unlock_count, category_count, region_count, streak)
- **ExplorerLevel**: A tier computed from total unlocked places: beginner_explorer (0-4), weekend_wanderer (5-14), trail_hunter (15-29), summit_collector (30+)
- **BookingService**: The service class responsible for booking, approving, and rejecting event bookings
- **UnlockService**: The service class responsible for creating place unlock records
- **EventService**: The service class responsible for event lifecycle management
- **AchievementService**: The service class responsible for level calculation and badge evaluation
- **NotificationService**: The service class responsible for sending FCM push notifications and database notifications
- **Sanctum**: Laravel Sanctum, the token-based API authentication system used for mobile app API access
- **Firebase_Auth**: Firebase Authentication used for email/password and Google login on mobile app only (not used for web dashboards)
- **FCM**: Firebase Cloud Messaging used for push notifications to mobile devices
- **Session_Auth**: Laravel's built-in session-based authentication used for admin and organizer web dashboards (email/password login via Livewire views)
- **S3**: Amazon S3 used for all file storage (avatars, photos, proof images)

## Requirements

### Requirement 1A: Web Dashboard Authentication (Admin & Organizer)

**User Story:** As an admin or organizer, I want to log in to my web dashboard using email and password, so that I can manage places, events, and bookings from a browser.

#### Acceptance Criteria

1. WHEN an admin or organizer submits valid email and password credentials via the web login form, THE Platform SHALL authenticate the user using Laravel's built-in session-based authentication and redirect to the appropriate dashboard (`/admin/dashboard` or `/organizer/dashboard`)
2. WHEN an unauthenticated user accesses `/admin/*` or `/organizer/*` routes, THE Platform SHALL redirect the user to the web login page
3. WHEN an admin registers a new admin or organizer account via the web, THE Platform SHALL create a user record with the assigned role, a hashed password, and `beginner_explorer` level
4. THE Platform SHALL generate a unique username from the user's email prefix with a random suffix during web registration
5. WHEN a web dashboard user logs out, THE Platform SHALL invalidate the session and redirect to the login page
6. THE Platform SHALL NOT use Firebase Authentication for any web dashboard routes — admin and organizer accounts are managed entirely through Laravel's built-in auth system

### Requirement 1B: Mobile App Authentication (Firebase)

**User Story:** As a mobile app user, I want to register and log in using email/password or Google login via Firebase, so that I can access the platform from my phone.

#### Acceptance Criteria

1. WHEN a mobile user submits a valid Firebase ID token to `POST /api/auth/firebase`, THE Platform SHALL verify the token via `kreait/laravel-firebase`, find or create a local user record with role `user`, and return a Sanctum API token
2. WHEN a mobile user authenticates via Google login through Firebase, THE Platform SHALL store the `google_id` and `firebase_uid` on the user record
3. WHEN a Firebase ID token is invalid or expired, THE Platform SHALL reject the authentication request and return an error response
4. WHEN an authenticated mobile user submits an FCM token, THE Platform SHALL store the token on the user record for push notification delivery
5. THE Platform SHALL generate a unique username from the user's email prefix with a random suffix during Firebase registration
6. THE Platform SHALL require a valid Sanctum token (obtained via the Firebase auth flow) for all authenticated `/api/*` endpoints
7. THE Platform SHALL NOT provide email/password registration or login endpoints on the API — mobile users authenticate exclusively through Firebase

### Requirement 2: Place Management

**User Story:** As an admin, I want to define and manage travel destinations with category-specific fields, so that organizers can create events tied to curated places.

#### Acceptance Criteria

1. WHEN an admin creates a place, THE Platform SHALL store the name, slug, description, category, region, province, coordinates, cover image path, and category-specific fields in JSON format
2. WHEN an admin assigns a PlaceCategory to a place, THE Platform SHALL accept only one of the 11 defined categories (mountain, beach, island, falls, river, lake, campsite, historical, food_destination, road_trip, hidden_gem)
3. WHEN an admin uploads a cover image for a place, THE Platform SHALL store the file on S3 and save the path on the place record
4. WHEN an admin deactivates a place, THE Platform SHALL set `is_active` to false and exclude the place from public browsing
5. THE Platform SHALL enforce a unique slug for each place
6. WHEN a place has category `mountain`, THE Platform SHALL accept category_fields including difficulty, meters_above_sea_level, trail_class, and estimated_hours

### Requirement 3: Event Management

**User Story:** As an organizer, I want to create and manage events tied to places, so that users can discover and join travel activities.

#### Acceptance Criteria

1. WHEN an organizer creates an event, THE Platform SHALL store the title, slug, description, category, event_date, meeting_place, fee, max_slots, requirements, and status as `draft`
2. WHEN an organizer publishes an event, THE Platform SHALL change the EventStatus from `draft` to `published` and make the event visible to users
3. WHEN an organizer attaches badges to an event, THE Platform SHALL create badge_event pivot records linking the badges to the event
4. THE Platform SHALL enforce a unique slug for each event
5. WHEN a user browses events, THE Platform SHALL return only events with status `published` or `full`
6. WHEN an organizer cancels an event, THE Platform SHALL set the EventStatus to `cancelled` and notify all users with approved bookings via FCM

### Requirement 4: Event Booking

**User Story:** As a user, I want to book events and have my booking approved by the organizer, so that I can join travel activities.

#### Acceptance Criteria

1. WHEN a user books a published event with available slots and no existing active booking, THE BookingService SHALL create a booking with status `pending`
2. WHEN a user books an event that has `auto_approve_bookings` enabled, THE BookingService SHALL create the booking with status `approved` and set `approved_at`
3. WHEN a booking is created and the remaining available slots reach zero, THE BookingService SHALL update the EventStatus to `full`
4. WHEN a user attempts to book an event with zero available slots, THE BookingService SHALL reject the booking and raise a NoSlotsAvailableException
5. WHEN a user attempts to book an event they already have a pending or approved booking for, THE BookingService SHALL reject the duplicate booking
6. WHEN a user attempts to book an event with a past event_date, THE BookingService SHALL reject the booking
7. WHEN two users attempt to book the last available slot simultaneously, THE BookingService SHALL use database row locking to ensure only one booking succeeds
8. WHEN a booking is created, THE NotificationService SHALL send an FCM push notification to the event organizer

### Requirement 5: Booking Approval

**User Story:** As an organizer, I want to approve or reject bookings for my events, so that I can manage who attends.

#### Acceptance Criteria

1. WHEN an organizer approves a pending booking for an event they own, THE BookingService SHALL set the booking status to `approved` and record `approved_at`
2. WHEN an organizer rejects a pending booking for an event they own, THE BookingService SHALL set the booking status to `rejected` and record `rejected_at`
3. WHEN a booking is approved and the remaining available slots reach zero, THE BookingService SHALL update the EventStatus to `full`
4. WHEN a booking status changes to approved or rejected, THE NotificationService SHALL send an FCM push notification to the booking user
5. WHEN a non-owner user attempts to approve or reject a booking, THE BookingService SHALL reject the action

### Requirement 6: Event Completion and Auto-Unlock

**User Story:** As an organizer, I want to mark events as completed so that attendees automatically unlock the associated place.

#### Acceptance Criteria

1. WHEN an organizer marks a published or full event as completed (with event_date in the past or today), THE EventService SHALL set the EventStatus to `completed`
2. WHEN an event is marked completed, THE EventService SHALL create a PlaceUnlock record with method `event_completion` for each user with an approved booking who has not already unlocked that place
3. WHEN an event is marked completed, THE AchievementService SHALL check and award eligible badges to each approved attendee
4. WHEN a non-owner user attempts to complete an event, THE EventService SHALL reject the action
5. WHEN an organizer attempts to complete an event with a future event_date, THE EventService SHALL reject the action

### Requirement 7: Place Unlock System

**User Story:** As a user, I want to unlock places through multiple methods, so that I can build my travel profile and track visited destinations.

#### Acceptance Criteria

1. WHEN a user unlocks a place via any valid UnlockMethod, THE UnlockService SHALL create a PlaceUnlock record with the method, optional proof photo path, optional verifier, and optional event reference
2. WHEN a user submits a photo proof to unlock a place, THE UnlockService SHALL store the photo on S3 and record the path in the PlaceUnlock record
3. WHEN a user attempts to unlock a place they have already unlocked, THE UnlockService SHALL reject the duplicate unlock
4. WHEN a user attempts to unlock an inactive place, THE UnlockService SHALL reject the unlock
5. WHEN a user unlocks a place via `event_completion`, THE UnlockService SHALL verify the event status is `completed` and the user has an approved booking
6. WHEN a user unlocks a place via `organizer_verification`, THE UnlockService SHALL verify the verifier is the event organizer
7. WHEN a user unlocks a place via `admin_approval`, THE UnlockService SHALL verify the verifier has the `admin` role
8. WHEN a place is unlocked, THE AchievementService SHALL recalculate the user's ExplorerLevel and check for newly earned badges
9. THE Platform SHALL enforce a unique constraint on the combination of user_id and place_id in place_unlocks

### Requirement 8: Explorer Level Calculation

**User Story:** As a user, I want my explorer level to reflect my travel progress, so that I can see my growth as a traveler.

#### Acceptance Criteria

1. THE AchievementService SHALL compute ExplorerLevel as `beginner_explorer` when a user has 0 to 4 unlocked places
2. THE AchievementService SHALL compute ExplorerLevel as `weekend_wanderer` when a user has 5 to 14 unlocked places
3. THE AchievementService SHALL compute ExplorerLevel as `trail_hunter` when a user has 15 to 29 unlocked places
4. THE AchievementService SHALL compute ExplorerLevel as `summit_collector` when a user has 30 or more unlocked places
5. THE AchievementService SHALL recalculate and persist the ExplorerLevel on the user record after each place unlock or badge check

### Requirement 9: Badge and Achievement System

**User Story:** As a user, I want to earn badges based on my travel achievements, so that I feel rewarded and motivated to explore more.

#### Acceptance Criteria

1. WHEN the AchievementService evaluates badges for a user, THE AchievementService SHALL compare the user's unlock statistics against each active badge's criteria
2. WHEN a badge has criteria_type `unlock_count`, THE AchievementService SHALL award the badge when the user's total unlocked places meets or exceeds the criteria count
3. WHEN a badge has criteria_type `category_count`, THE AchievementService SHALL award the badge when the user's unlocked places in the specified category meets or exceeds the criteria count
4. WHEN a badge has criteria_type `region_count`, THE AchievementService SHALL award the badge when the user's unlocked places in the specified region meets or exceeds the criteria count
5. WHEN a badge has criteria_type `streak`, THE AchievementService SHALL award the badge when the user's current streak meets or exceeds the criteria days
6. THE AchievementService SHALL award each badge to a user at most once (idempotent badge awarding)
7. THE AchievementService SHALL preserve previously awarded badges when evaluating new badge eligibility
8. WHEN a badge is awarded, THE NotificationService SHALL send an FCM push notification to the user

### Requirement 10: Social Profile and Follow System

**User Story:** As a user, I want a public travel profile and the ability to follow other travelers, so that I can connect with the community.

#### Acceptance Criteria

1. WHEN a user views a profile by username, THE Platform SHALL display the username, bio, avatar, explorer level, unlocked places count, and earned badges
2. WHEN a user follows another user, THE Platform SHALL create a follows record linking follower and following
3. WHEN a user unfollows another user, THE Platform SHALL remove the follows record
4. WHEN a user views a profile, THE Platform SHALL display per-category unlock counts (mountains, beaches, islands, falls, etc.)
5. THE Platform SHALL enforce that a user cannot follow themselves
6. THE Platform SHALL enforce a unique constraint on the follower_id and following_id combination

### Requirement 11: Organizer Verification

**User Story:** As an admin, I want to verify organizer accounts, so that users can trust the events they join.

#### Acceptance Criteria

1. WHEN an admin verifies an organizer, THE Platform SHALL set `is_verified_organizer` to true on the organizer's user record
2. WHEN a user views an organizer's profile or event, THE Platform SHALL display the verified status
3. WHEN a non-admin user attempts to verify an organizer, THE Platform SHALL reject the action

### Requirement 12: File Storage

**User Story:** As a user or organizer, I want to upload photos and images, so that I can share my travel experiences and provide proof of visits.

#### Acceptance Criteria

1. WHEN a user uploads an avatar, THE Platform SHALL store the file on S3 under the `avatars` directory and update the user's `avatar_path`
2. WHEN an organizer uploads event photos after completion, THE Platform SHALL store each file on S3 under the `event-photos` directory and create an EventPhoto record
3. WHEN a user uploads a proof photo for a place unlock, THE Platform SHALL store the file on S3 under the `proof-photos` directory
4. THE Platform SHALL generate public URLs for stored files via the S3 storage facade

### Requirement 13: Push Notifications

**User Story:** As a user, I want to receive push notifications for important events, so that I stay informed about my bookings and achievements.

#### Acceptance Criteria

1. WHEN a booking is created, THE NotificationService SHALL send an FCM notification to the event organizer with the booking details
2. WHEN a booking is approved or rejected, THE NotificationService SHALL send an FCM notification to the booking user with the status
3. WHEN a badge is awarded, THE NotificationService SHALL send an FCM notification to the user with the badge name
4. IF a user has no FCM token stored, THEN THE NotificationService SHALL skip the push notification without error

### Requirement 14: Leaderboard

**User Story:** As a user, I want to see a leaderboard of top explorers, so that I can compare my progress with the community.

#### Acceptance Criteria

1. WHEN a user requests the leaderboard, THE Platform SHALL return users ranked by total unlocked places count in descending order
2. WHEN displaying leaderboard entries, THE Platform SHALL include the username, explorer level, unlocked places count, and badge count for each user

### Requirement 15: Admin Badge Management

**User Story:** As an admin, I want to create and manage badges with flexible criteria, so that I can design a rewarding achievement system.

#### Acceptance Criteria

1. WHEN an admin creates a badge, THE Platform SHALL store the name, slug, description, icon_path, category, criteria_type, and criteria_value in JSON format
2. WHEN an admin deactivates a badge, THE Platform SHALL set `is_active` to false and exclude the badge from future evaluations
3. WHEN an admin attaches a badge to an event, THE Platform SHALL create a badge_event pivot record
4. THE Platform SHALL enforce a unique slug for each badge

### Requirement 16: API Access and Authorization

**User Story:** As a mobile app user, I want secure API access with role-based permissions, so that I can use the platform safely from my phone.

#### Acceptance Criteria

1. THE Platform SHALL require a valid Sanctum token (issued via the Firebase auth flow at `POST /api/auth/firebase`) for all authenticated `/api/*` endpoints
2. WHEN an API request targets an organizer-only endpoint, THE Platform SHALL verify the authenticated user has the `organizer` role
3. WHEN an API request targets an admin-only endpoint, THE Platform SHALL verify the authenticated user has the `admin` role
4. WHEN an unauthorized user accesses a role-restricted API endpoint, THE Platform SHALL return a 403 Forbidden response
5. WHEN a web request targets `/admin/*` routes, THE Platform SHALL verify the user is authenticated via Laravel session and has the `admin` role
6. WHEN a web request targets `/organizer/*` routes, THE Platform SHALL verify the user is authenticated via Laravel session and has the `organizer` role
7. THE Platform SHALL use `auth:sanctum` middleware for `/api/*` routes and `auth` (session) middleware for `/admin/*` and `/organizer/*` web routes

### Requirement 17: Database Integrity

**User Story:** As a system operator, I want data integrity enforced at the database level, so that the platform remains consistent under concurrent access.

#### Acceptance Criteria

1. THE Platform SHALL enforce a unique constraint on `bookings(event_id, user_id)` to prevent duplicate bookings
2. THE Platform SHALL enforce a unique constraint on `place_unlocks(user_id, place_id)` to prevent duplicate unlocks
3. THE Platform SHALL enforce a unique constraint on `user_badges(user_id, badge_id)` to prevent duplicate badge awards
4. THE Platform SHALL enforce a unique constraint on `follows(follower_id, following_id)` to prevent duplicate follow records
5. THE Platform SHALL use database transactions with row-level locking for booking operations to prevent race conditions on slot counts
6. THE Platform SHALL cascade-delete bookings, place_unlocks, user_badges, and follows when the parent user is deleted
