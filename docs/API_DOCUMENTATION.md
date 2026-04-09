# LakbayXP — Mobile App API Documentation

Base URL: `https://your-domain.com/api`
Auth: `Authorization: Bearer {sanctum_token}` (all endpoints except noted)

---

## Follow System

### POST /users/{id}/follow

Follow a user. No body needed.

**Response 201:**
```json
{ "message": "Successfully followed user." }
```

**422:** `{ "message": "You cannot follow yourself." }` or `{ "message": "You are already following this user." }`

---

### DELETE /users/{id}/unfollow

Unfollow a user. No body needed.

**Response 200:**
```json
{ "message": "Successfully unfollowed user." }
```

**422:** `{ "message": "You are not following this user." }`

---

### GET /users/{id}/followers

List followers of a user. Paginated.

**Query:** `?per_page=20&page=1`

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 3,
      "name": "Maria Santos",
      "username": "mariasantos",
      "avatar_url": "https://s3.../avatars/maria.jpg",
      "level": 5,
      "xp": 820,
      "is_following": true,
      "is_me": false
    },
    {
      "id": 7,
      "name": "Juan Dela Cruz",
      "username": "juandc",
      "avatar_url": null,
      "level": 2,
      "xp": 150,
      "is_following": false,
      "is_me": false
    }
  ],
  "last_page": 1,
  "per_page": 20,
  "total": 2
}
```

> `is_following` = whether YOU (the authenticated user) are following this person.
> `is_me` = whether this entry is your own account.

---

### GET /users/{id}/following

List users that a user is following. Paginated. Same response shape as followers.

**Query:** `?per_page=20&page=1`

**Response 200:** Same structure as `/followers` above.

---

## Travel Buddy System

### POST /users/{id}/buddy-request

Send a travel buddy request to a user. No body needed.

**Response 201:**
```json
{
  "message": "Buddy request sent.",
  "request": {
    "id": 5,
    "requester_id": 1,
    "receiver_id": 3,
    "status": "pending",
    "accepted_at": null,
    "created_at": "2026-04-09T10:00:00.000000Z",
    "updated_at": "2026-04-09T10:00:00.000000Z"
  }
}
```

**422:**
- `{ "message": "Cannot send buddy request to yourself." }`
- `{ "message": "You are already travel buddies." }`
- `{ "message": "A buddy request already exists." }`

> If a previous request was declined, sending again will re-open it as pending.

---

### GET /travel-buddies

List my accepted travel buddies. Paginated.

**Query:** `?per_page=20&page=1`

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 5,
      "buddy": {
        "id": 3,
        "name": "Maria Santos",
        "username": "mariasantos",
        "avatar_url": "https://s3.../avatars/maria.jpg",
        "level": 5,
        "xp": 820
      },
      "accepted_at": "2026-04-09T12:00:00.000000Z",
      "created_at": "2026-04-09T10:00:00.000000Z"
    }
  ],
  "last_page": 1,
  "per_page": 20,
  "total": 1
}
```

> `buddy` is always the other person (not you).

---

### GET /travel-buddies/pending-received

List buddy requests I received (waiting for my approval). Paginated.

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 8,
      "requester": {
        "id": 7,
        "name": "Juan Dela Cruz",
        "username": "juandc",
        "avatar_url": null,
        "level": 2,
        "xp": 150
      },
      "created_at": "2026-04-09T14:00:00.000000Z"
    }
  ],
  "last_page": 1,
  "per_page": 20,
  "total": 1
}
```

---

### GET /travel-buddies/pending-sent

List buddy requests I sent (waiting for their approval). Paginated.

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 9,
      "receiver": {
        "id": 12,
        "name": "Ana Reyes",
        "username": "anareyes",
        "avatar_url": "https://s3.../avatars/ana.jpg",
        "level": 8,
        "xp": 1500
      },
      "created_at": "2026-04-09T15:00:00.000000Z"
    }
  ],
  "last_page": 1,
  "per_page": 20,
  "total": 1
}
```

---

### POST /travel-buddies/{id}/accept

Accept a buddy request. Only the receiver can accept. No body needed.

**Response 200:**
```json
{ "message": "Buddy request accepted." }
```

**403:** `{ "message": "Unauthorized." }`
**422:** `{ "message": "Request is not pending." }`

> Sends a push notification to the requester.

---

### POST /travel-buddies/{id}/decline

Decline a buddy request. Only the receiver can decline. No body needed.

**Response 200:**
```json
{ "message": "Buddy request declined." }
```

**403:** `{ "message": "Unauthorized." }`
**422:** `{ "message": "Request is not pending." }`

---

### DELETE /travel-buddies/{id}

Remove a travel buddy. Either side can remove. No body needed.

**Response 200:**
```json
{ "message": "Travel buddy removed." }
```

**403:** `{ "message": "Unauthorized." }`
**422:** `{ "message": "Not a travel buddy." }`

---

## Events & Bookings

### GET /events

List published events. Paginated.

**Query Parameters:**

| Param | Type | Description |
|---|---|---|
| `page` | int | Page number (default 1) |
| `per_page` | int | Items per page (default 15) |
| `category` | string | mountain, beach, island, falls, river, lake, campsite, historical, food_destination, road_trip, hidden_gem |
| `search` | string | Search title, description, meeting_place |
| `region` | string | Filter by place region |
| `province` | string | Filter by place province |
| `difficulty` | string | easy, moderate, hard, extreme |
| `date_from` | date | YYYY-MM-DD |
| `date_to` | date | YYYY-MM-DD |
| `fee_min` | number | Minimum fee |
| `fee_max` | number | Maximum fee |
| `available_only` | bool | Only events with open slots |
| `lat` | float | Enables near-me + distance_km |
| `lng` | float | Requires lat |
| `radius` | int | km (default 100) |
| `sort` | string | date, fee_low, fee_high, newest, popular |

**Response 200:** Paginated events with `place`, `organizer`, `cover_image_url`, `available_slots`, `booked_count`. `distance_km` included when lat/lng provided.

---

### GET /events/{slug}

Full event detail with place, organizer, itinerary, rules, photos.

**Response includes:** `itinerary[]` (with `place` or `custom_place_name`), `rules[]` (rule_type + content), `photos[]` (with `photo_url`), `cover_image_url`, `available_slots`, `booked_count`.

---

### POST /events/{id}/book

Book an event. No body needed.

**201:** Booking created (status = approved if auto_approve, else pending).
**422:** No slots / already booked.

---

### GET /my-bookings

List my bookings. Paginated.

**Query:** `?status=pending|approved|rejected|cancelled&per_page=15`

**Response:** Paginated bookings with `event` (title, slug, cover_image_url, date, fee, organizer).

---

### DELETE /bookings/{id}

Cancel my booking.

**200:** `{ "message": "Booking cancelled successfully.", "booking": {...} }`
**403:** Not your booking.
**422:** Already cancelled.

---

## Community Posts

### PUT /posts/{id}

Edit a post. Multipart/form-data.

**Payload:** `content` (string, optional), `images[]` (files, optional, max 5, max 10MB each)

**200:** Updated post with user, images, reactions_count, comments_count.

---

### DELETE /posts/{postId}/images/{imageId}

Delete a single image from your post.

**200:** `{ "message": "Image deleted." }`

---

## Places

### GET /places

**Extra filter:** `?unlock=unlocked` (only unlocked) or `?unlock=locked` (only locked).

---

## Reaction Types

`like`, `love`, `fire`, `wow`, `congrats`, `thumbs_down`, `sad`, `angry`
