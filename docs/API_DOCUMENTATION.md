# LakbayXP API Documentation

Base URL: `https://lakbayxp-master-w7xpxo.laravel.cloud/api`

## Authentication

All authenticated endpoints require:
```
Authorization: Bearer {sanctum_token}
Accept: application/json
```

---

## 🔓 PUBLIC ENDPOINTS (no auth)

### POST /auth/firebase
Login with Firebase ID token, returns Sanctum bearer token.

**Payload:**
```json
{ "firebase_token": "eyJhbGciOiJSUzI1NiIs..." }
```

**Response 200:**
```json
{
  "token": "7|fWCFBoIb...",
  "user": {
    "id": 9,
    "name": "ariel espiritu",
    "email": "arielespiritu22@gmail.com",
    "username": "arielespiritu22-Hmz0",
    "role": "user",
    "level": 1,
    "xp": 0
  }
}
```

**Error 401:** `{ "message": "Invalid Firebase token." }`

---

### GET /categories
All place categories with icons and counts.

**Response 200:**
```json
[
  { "value": "mountain", "label": "Mountain", "icon": "⛰️", "place_count": 20 },
  { "value": "beach", "label": "Beach", "icon": "🏖️", "place_count": 15 },
  { "value": "island", "label": "Island", "icon": "🏝️", "place_count": 12 },
  { "value": "falls", "label": "Falls", "icon": "💧", "place_count": 10 },
  { "value": "river", "label": "River", "icon": "🌊", "place_count": 3 },
  { "value": "lake", "label": "Lake", "icon": "🏞️", "place_count": 5 },
  { "value": "campsite", "label": "Campsite", "icon": "⛺", "place_count": 8 },
  { "value": "historical", "label": "Historical", "icon": "🏛️", "place_count": 8 },
  { "value": "food_destination", "label": "Food destination", "icon": "🍜", "place_count": 8 },
  { "value": "road_trip", "label": "Road trip", "icon": "🚗", "place_count": 6 },
  { "value": "hidden_gem", "label": "Hidden gem", "icon": "💎", "place_count": 10 }
]
```

---


## 🔐 AUTHENTICATED ENDPOINTS

### GET /me
Current logged-in user profile with stats.

**Response 200:**
```json
{
  "id": 9,
  "name": "ariel espiritu",
  "email": "arielespiritu22@gmail.com",
  "username": "arielespiritu22-Hmz0",
  "avatar_path": null,
  "role": "user",
  "level": 1,
  "xp": 0,
  "xp_progress": {
    "level": 1,
    "total_xp": 0,
    "xp_in_current_level": 0,
    "xp_needed_for_next": 240,
    "progress_percent": 0,
    "is_max_level": false
  },
  "total_points": 0,
  "available_points": 0,
  "unlocked_places_count": 0,
  "badges_count": 0,
  "followers_count": 0,
  "following_count": 0,
  "created_at": "2026-04-06T16:13:54.000000Z"
}
```

---

### POST /auth/fcm-token
Register FCM push notification token.

**Payload:**
```json
{ "fcm_token": "dGVzdC10b2tlbi0xMjM0NTY3ODk..." }
```

**Response 200:** `{ "message": "FCM token updated" }`

---

## 📅 EVENTS

### GET /events
List published events (paginated). Supports filters.

**Query params:** `?category=mountain&search=pulag&place=1&page=1&per_page=15`

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Mt. Pulag Sea of Clouds",
      "slug": "mt-pulag-soc-abc1",
      "description": "Witness the famous sea of clouds...",
      "category": "mountain",
      "event_date": "2026-04-22",
      "end_date": null,
      "meeting_place": "Baguio City, Session Road",
      "meeting_time": "11:00 PM",
      "fee": "3500.00",
      "max_slots": 20,
      "difficulty": "hard",
      "status": "published",
      "auto_approve_bookings": false,
      "place": { "id": 1, "name": "Mt. Pulag", "slug": "mt-pulag" },
      "organizer": { "id": 2, "name": "Lakbay Guides PH", "username": "lakbay-guides-ph" }
    }
  ],
  "current_page": 1,
  "last_page": 2,
  "per_page": 15,
  "total": 18
}
```

### GET /events/{slug}
Single event detail.

**Response 200:** Same as above but single object.

---

### POST /events/{event_id}/book
Book an event slot.

**Payload:** None (just POST with auth header)

**Response 201:**
```json
{
  "message": "Booking created successfully.",
  "booking": {
    "id": 15,
    "event_id": 1,
    "user_id": 9,
    "status": "pending",
    "event": { "id": 1, "title": "Mt. Pulag Sea of Clouds" }
  }
}
```

**Error 422:** `{ "message": "No slots available." }` or `{ "message": "You already have a booking for this event." }`

---

### DELETE /bookings/{booking_id}
Cancel own booking.

**Response 200:**
```json
{
  "message": "Booking cancelled successfully.",
  "booking": { "id": 15, "status": "cancelled" }
}
```

---

## 📍 PLACES

### GET /places
List active places (paginated). Supports search, filters, near-me, and sorting.

**Headers:** `Authorization: Bearer {token}`

**Query Params:**

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `search` | string | — | Search by place name |
| `category` | string | — | Filter by category (e.g. `mountain`, `beach`) |
| `region` | string | — | Filter by region (partial match) |
| `province` | string | — | Filter by province (partial match) |
| `lat` | float | — | User latitude (enables near-me, sorted by distance) |
| `lng` | float | — | User longitude (required with `lat`) |
| `radius` | int | `100` | Radius in km (only with `lat`/`lng`) |
| `sort` | string | — | `popular` (most unlocked), `xp` (highest XP), `newest` |
| `per_page` | int | `15` | Results per page |
| `page` | int | `1` | Page number |

**Example Requests:**
```
GET /api/places?category=mountain&sort=popular
GET /api/places?lat=14.5995&lng=120.9842&radius=50
GET /api/places?region=Cordillera&province=Benguet
GET /api/places?search=pulag&sort=xp
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Mt. Pulag",
      "slug": "mt-pulag",
      "description": "Highest peak in Luzon...",
      "category": "mountain",
      "region": "Cordillera",
      "province": "Benguet",
      "latitude": "16.5870000",
      "longitude": "120.8970000",
      "xp_reward": 150,
      "is_active": true,
      "distance_km": 12.4
    }
  ],
  "current_page": 1,
  "last_page": 7,
  "per_page": 15,
  "total": 105
}
```

**Notes:**
- `distance_km` only present when `lat`/`lng` params are provided
- Near-me results are sorted by distance (closest first)
- Without `lat`/`lng`, sorted alphabetically by default (or by `sort` param)

### GET /places/{slug}
Single place detail.

---

## 🔓 UNLOCKS

### POST /places/{place_id}/unlock
Manually unlock a place.

**Payload:**
```json
{
  "unlock_method": "self_report",
  "event_id": null,
  "proof_photo": null
}
```

`unlock_method` options: `self_report`, `photo_proof`, `qr_code`, `event_completion`, `organizer_verification`, `admin_approval`

**Response 201:**
```json
{
  "message": "Place unlocked successfully.",
  "unlock": {
    "id": 25,
    "user_id": 9,
    "place_id": 1,
    "unlock_method": "self_report",
    "place": { "id": 1, "name": "Mt. Pulag" }
  }
}
```

**Error 422:** `{ "message": "You have already unlocked this place." }`

---

### GET /my-unlocks
Current user's unlocked place IDs (for map, no pagination).

**Response 200:**
```json
{
  "data": [
    { "place_id": 1, "unlocked_at": "2026-04-06T12:00:00Z", "method": "event_completion" },
    { "place_id": 4, "unlocked_at": "2026-04-05T08:30:00Z", "method": "self_report" }
  ]
}
```

---

## 👤 PROFILE

### GET /profile/{username}
Public user profile with stats.

**Response 200:**
```json
{
  "data": {
    "username": "maria-santos-1",
    "bio": "Mountain lover 🏔️",
    "avatar_path": "avatars/abc123.jpg",
    "level": 4,
    "xp": {
      "level": 4,
      "total_xp": 580,
      "xp_in_current_level": 100,
      "xp_needed_for_next": 600,
      "progress_percent": 17,
      "is_max_level": false
    },
    "total_points": 150,
    "available_points": 100,
    "unlocked_places_count": 8,
    "badge_count": 3,
    "followers_count": 25,
    "following_count": 12,
    "badges": [
      { "id": 1, "name": "First Steps", "slug": "first-steps", "icon_path": null, "points": 10, "xp_reward": 0 }
    ],
    "category_counts": {
      "mountain": 3, "beach": 2, "island": 1, "falls": 2, "river": 0, "lake": 0,
      "campsite": 0, "historical": 0, "food_destination": 0, "road_trip": 0, "hidden_gem": 0
    }
  }
}
```

### GET /profile/{username}/unlocks?page=1
User's unlocked places (paginated).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1, "name": "Mt. Pulag", "slug": "mt-pulag", "category": "mountain",
      "region": "Cordillera", "province": "Benguet",
      "pivot": { "created_at": "2026-04-05T12:00:00Z", "unlock_method": "event_completion" }
    }
  ],
  "current_page": 1, "last_page": 1, "per_page": 15, "total": 8
}
```

### GET /profile/{username}/badges?page=1
User's earned badges (paginated).

### POST /profile
Update own profile.

**Payload (multipart/form-data):**
```
avatar: [file]
bio: "Mountain lover 🏔️"
```

**Response 200:**
```json
{
  "message": "Profile updated successfully.",
  "data": { "username": "arielespiritu22-Hmz0", "bio": "Mountain lover 🏔️", "avatar_path": "avatars/abc.jpg" }
}
```

---

## 👥 SOCIAL

### POST /users/{user_id}/follow
Follow a user.

**Payload:** None

**Response 200:** `{ "message": "Followed successfully." }`

### DELETE /users/{user_id}/unfollow
Unfollow a user.

**Response 200:** `{ "message": "Unfollowed successfully." }`

---

## 🏆 LEADERBOARD

### GET /leaderboard?page=1&per_page=20
Top explorers ranked by level and XP (paginated).

**Response 200:**
```json
{
  "data": [
    {
      "id": 5, "name": "Maria Santos", "username": "maria-santos-1",
      "email": "maria-santos0@explorer.ph", "level": 4, "xp": 580,
      "unlocked_places_count": 8, "badges_count": 3
    }
  ],
  "current_page": 1, "last_page": 2, "per_page": 20, "total": 35
}
```

---

## 🏅 BADGES

### GET /badges
All active badges (no pagination).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1, "name": "First Steps", "slug": "first-steps",
      "description": "Unlock your first place.",
      "icon_path": null, "category": null,
      "criteria_type": "unlock_count", "criteria_value": { "count": 1 },
      "points": 10, "xp_reward": 0, "is_active": true
    }
  ]
}
```

---

## 🎁 REWARDS

### GET /rewards?page=1&per_page=15
Available rewards (paginated).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1, "name": "LakbayXP Sticker Pack", "slug": "sticker-pack",
      "description": "Exclusive sticker set", "image_path": null,
      "points_cost": 50, "stock": 100, "is_active": true
    }
  ],
  "current_page": 1, "last_page": 1, "per_page": 15, "total": 3
}
```

### POST /rewards/{reward_id}/redeem
Redeem a reward with badge points.

**Payload:** None

**Response 201:**
```json
{
  "message": "Reward redeemed successfully. Pending admin approval.",
  "redemption": {
    "id": 1, "user_id": 9, "reward_id": 1, "points_spent": 50, "status": "pending",
    "reward": { "id": 1, "name": "LakbayXP Sticker Pack" }
  },
  "available_points": 50
}
```

**Error 422:** `{ "message": "Not enough points." }` or `{ "message": "Reward out of stock." }`

### GET /my-redemptions?page=1
My redemption history (paginated).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1, "points_spent": 50, "status": "pending", "created_at": "2026-04-06T15:00:00Z",
      "reward": { "id": 1, "name": "LakbayXP Sticker Pack" }
    }
  ],
  "current_page": 1, "last_page": 1, "per_page": 15, "total": 1
}
```

---

## 📱 COMMUNITY FEED

### GET /feed?page=1&per_page=15
Smart feed with algorithm: 40% following, 30% trending, 20% discovery, 10% system posts.

**Response 200:**
```json
{
  "data": [
    {
      "id": 12,
      "user_id": 5,
      "content": "Just conquered Mt. Pulag! Sea of clouds was unreal 🏔️☁️",
      "type": "photo",
      "place_id": 1,
      "event_id": null,
      "badge_id": null,
      "is_active": true,
      "created_at": "2026-04-06T14:30:00Z",
      "user": {
        "id": 5, "name": "Maria Santos", "username": "maria-santos-1",
        "avatar_path": "avatars/maria.jpg", "level": 4
      },
      "images": [
        { "id": 1, "image_path": "posts/abc123.jpg", "sort_order": 0 },
        { "id": 2, "image_path": "posts/def456.jpg", "sort_order": 1 }
      ],
      "place": { "id": 1, "name": "Mt. Pulag", "slug": "mt-pulag", "category": "mountain" },
      "event": null,
      "badge": null,
      "reactions_count": 24,
      "comments_count": 8,
      "user_reaction": "love"
    },
    {
      "id": 8,
      "content": "Earned the Mountain Goat badge! 🏅",
      "type": "badge_earned",
      "badge_id": 3,
      "badge": { "id": 3, "name": "Mountain Goat", "slug": "mountain-goat", "icon_path": null },
      "reactions_count": 15,
      "comments_count": 3,
      "user_reaction": null
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 72
}
```

---

### POST /posts
Create a new post (text or with photos).

**Payload (multipart/form-data):**
```
content: "Just conquered Mt. Pulag! 🏔️"
type: "photo"                          (optional, auto-detected)
place_id: 1                            (optional, tag a place)
event_id: 3                            (optional, tag an event)
images[0]: [file]                      (optional, up to 5 images)
images[1]: [file]
```

**Response 201:**
```json
{
  "data": {
    "id": 25,
    "user_id": 9,
    "content": "Just conquered Mt. Pulag! 🏔️",
    "type": "photo",
    "place_id": 1,
    "created_at": "2026-04-06T16:00:00Z",
    "user": { "id": 9, "name": "ariel espiritu", "username": "arielespiritu22-Hmz0", "level": 1 },
    "images": [
      { "id": 10, "image_path": "posts/abc.jpg", "sort_order": 0 }
    ],
    "reactions_count": 0,
    "comments_count": 0
  }
}
```

---

### GET /posts/{post_id}
Single post with comments.

**Response 200:**
```json
{
  "data": {
    "id": 12,
    "content": "Just conquered Mt. Pulag!",
    "type": "photo",
    "user": { "id": 5, "name": "Maria Santos", "level": 4 },
    "images": [ { "image_path": "posts/abc.jpg" } ],
    "place": { "id": 1, "name": "Mt. Pulag" },
    "reactions_count": 24,
    "comments_count": 8,
    "comments": [
      {
        "id": 1,
        "content": "Ganda! 😍",
        "user": { "id": 7, "name": "Carlo Bautista", "username": "carlo-b" },
        "parent_id": null,
        "created_at": "2026-04-06T15:00:00Z",
        "replies": [
          {
            "id": 3,
            "content": "Thanks bro!",
            "user": { "id": 5, "name": "Maria Santos" },
            "parent_id": 1
          }
        ]
      }
    ]
  }
}
```

---

### DELETE /posts/{post_id}
Delete own post.

**Response 200:** `{ "message": "Post deleted." }`
**Error 403:** `{ "message": "Unauthorized." }`

---

### GET /posts/{post_id}/comments?page=1&per_page=15
Paginated comments with nested replies.

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "content": "Ganda! 😍",
      "user": { "id": 7, "name": "Carlo Bautista", "username": "carlo-b", "avatar_path": null },
      "parent_id": null,
      "created_at": "2026-04-06T15:00:00Z",
      "replies": [
        { "id": 3, "content": "Thanks!", "user": { "id": 5, "name": "Maria Santos" }, "parent_id": 1 }
      ]
    }
  ],
  "current_page": 1, "last_page": 1, "per_page": 15, "total": 5
}
```

---

### POST /posts/{post_id}/comments
Add a comment (or reply to a comment).

**Payload:**
```json
{
  "content": "Ganda! 😍",
  "parent_id": null
}
```

For reply: `{ "content": "Thanks!", "parent_id": 1 }`

**Response 201:**
```json
{
  "data": {
    "id": 15,
    "post_id": 12,
    "content": "Ganda! 😍",
    "parent_id": null,
    "user": { "id": 9, "name": "ariel espiritu", "username": "arielespiritu22-Hmz0" },
    "created_at": "2026-04-06T16:30:00Z"
  }
}
```

---

### POST /posts/{post_id}/react
Toggle reaction. Same type = remove. Different type = change.

**Payload:**
```json
{ "type": "love" }
```

Types: `like`, `love`, `fire`, `wow`, `congrats`

**Response 200 (reacted):**
```json
{ "message": "Reacted.", "reacted": true, "type": "love", "reactions_count": 25 }
```

**Response 200 (unreacted — same type again):**
```json
{ "message": "Reaction removed.", "reacted": false, "reactions_count": 23 }
```

---

### GET /users/{user_id}/posts?page=1
A user's posts (for profile screen, paginated).

**Response 200:** Same format as feed but filtered to one user.

---

### GET /suggested-explorers?limit=10
Suggested users to follow. Algorithm: mutual friends → same events → similar places → top explorers → random.

**Response 200:**
```json
{
  "data": [
    {
      "id": 5,
      "name": "Maria Santos",
      "username": "maria-santos-1",
      "avatar_path": "avatars/maria.jpg",
      "level": 4,
      "xp": 580,
      "unlocked_places_count": 8,
      "badges_count": 3,
      "followers_count": 25,
      "suggestion_reason": "mutual_friends"
    },
    {
      "id": 12,
      "name": "Carlo Bautista",
      "username": "carlo-bautista-6",
      "avatar_path": null,
      "level": 3,
      "xp": 320,
      "unlocked_places_count": 5,
      "badges_count": 2,
      "followers_count": 18,
      "suggestion_reason": "same_events"
    },
    {
      "id": 8,
      "name": "Nico Castillo",
      "suggestion_reason": "similar_places"
    },
    {
      "id": 3,
      "name": "Jose Reyes",
      "suggestion_reason": "top_explorer"
    },
    {
      "id": 22,
      "name": "Raf Espiritu",
      "suggestion_reason": "discover"
    }
  ]
}
```

`suggestion_reason` values:
- `mutual_friends` — followed by people you follow
- `same_events` — attended the same events as you
- `similar_places` — unlocked the same places as you
- `top_explorer` — high level, active user
- `discover` — random discovery

---

## 📋 PAGINATION FORMAT

All paginated endpoints return:
```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 72,
  "next_page_url": "https://.../api/events?page=2",
  "prev_page_url": null
}
```

Flutter: load next page when `current_page < last_page` on scroll.

---

## ⚠️ ERROR RESPONSES

**401 Unauthenticated:**
```json
{ "message": "Unauthenticated." }
```

**403 Forbidden:**
```json
{ "message": "Unauthorized." }
```

**422 Validation Error:**
```json
{
  "message": "The content field is required.",
  "errors": { "content": ["The content field is required."] }
}
```

**500 Server Error:**
```json
{ "message": "Server Error" }
```


---

## 🧭 EXPLORERS

### GET /explorers
Browse all explorers with smart sorting, filters, and near-me. Paginated.

**Headers:** `Authorization: Bearer {token}`

**Query Params:**

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `sort` | string | `top` | Sort algorithm: `top`, `active`, `popular`, `newest`, `near_me` |
| `search` | string | — | Search by name or username |
| `min_level` | int | — | Minimum level filter |
| `max_level` | int | — | Maximum level filter |
| `has_badge` | int | — | Filter users who have this badge ID |
| `city` | string | — | Filter by city name (partial match) |
| `lat` | float | — | User latitude (required for `near_me`) |
| `lng` | float | — | User longitude (required for `near_me`) |
| `radius` | int | `100` | Radius in km (only for `near_me`) |
| `per_page` | int | `20` | Results per page |
| `page` | int | `1` | Page number |

**Sort Algorithms:**
- `top` — highest level + XP first (default)
- `active` — most recently unlocked a place
- `popular` — most followers
- `newest` — newest accounts first
- `near_me` — closest to your lat/lng (requires `lat` + `lng` params)

**Example Requests:**
```
GET /api/explorers?sort=top&per_page=20
GET /api/explorers?sort=near_me&lat=14.5995&lng=120.9842&radius=50
GET /api/explorers?search=maria&min_level=3
GET /api/explorers?has_badge=1&sort=popular
GET /api/explorers?city=Manila&sort=active
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 5,
      "name": "Maria Santos",
      "username": "maria-santos-1",
      "email": "maria-santos0@explorer.ph",
      "avatar_path": "avatars/maria.jpg",
      "level": 4,
      "xp": 580,
      "latitude": "14.5995000",
      "longitude": "120.9842000",
      "city": "Manila",
      "total_points": 150,
      "available_points": 100,
      "created_at": "2026-04-01T10:00:00Z",
      "unlocked_places_count": 8,
      "badges_count": 3,
      "followers_count": 25,
      "following_count": 12,
      "is_following": false,
      "distance_km": 2.4
    },
    {
      "id": 12,
      "name": "Carlo Bautista",
      "username": "carlo-bautista-6",
      "avatar_path": null,
      "level": 3,
      "xp": 320,
      "city": "Quezon City",
      "unlocked_places_count": 5,
      "badges_count": 2,
      "followers_count": 18,
      "following_count": 8,
      "is_following": true,
      "distance_km": 5.1
    }
  ],
  "current_page": 1,
  "last_page": 2,
  "per_page": 20,
  "total": 38
}
```

**Notes:**
- `is_following` — whether the current user follows this explorer
- `distance_km` — only present when `sort=near_me`
- Users without lat/lng are excluded from `near_me` results

**Error 422 (near_me without coords):**
```json
{ "message": "lat and lng required for near_me sort." }
```

---

### POST /location
Update current user's location (call on app launch or when location changes).

**Headers:** `Authorization: Bearer {token}`

**Payload:**
```json
{
  "latitude": 14.5995,
  "longitude": 120.9842,
  "city": "Manila"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `latitude` | float | yes | -90 to 90 |
| `longitude` | float | yes | -180 to 180 |
| `city` | string | no | City/area name (max 100 chars) |

**Response 200:**
```json
{ "message": "Location updated." }
```

**Error 422:**
```json
{
  "message": "The latitude field is required.",
  "errors": { "latitude": ["The latitude field is required."] }
}
```

### GET /places/all
All active places with coordinates (for map markers, no pagination).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Mt. Pulag",
      "slug": "mt-pulag",
      "category": "mountain",
      "region": "Cordillera",
      "province": "Benguet",
      "latitude": "16.5870000",
      "longitude": "120.8970000",
      "xp_reward": 150,
      "unlocked_by_users_count": 8
    }
  ]
}
```

---

### GET /regions
All 17 Philippine regions with their provinces (for filter dropdowns, no auth).

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "National Capital Region (NCR)",
      "provinces": ["Manila", "Quezon City", "Makati", "Pasig", "Taguig"]
    },
    {
      "id": 2,
      "name": "Cordillera Administrative Region (CAR)",
      "provinces": ["Abra", "Apayao", "Benguet", "Ifugao", "Kalinga", "Mountain Province"]
    },
    {
      "id": 3,
      "name": "Ilocos Region",
      "provinces": ["Ilocos Norte", "Ilocos Sur", "La Union", "Pangasinan"]
    }
  ]
}
```

---