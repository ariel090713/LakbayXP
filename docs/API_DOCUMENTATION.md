# LakbayXP — Events & Bookings API (Mobile App)

Base URL: `https://your-domain.com/api`
Auth: `Authorization: Bearer {sanctum_token}`

---

## GET /events

List published/full events. Paginated.

**Query Parameters:**

| Param | Type | Description |
|---|---|---|
| `page` | int | Page number (default 1) |
| `per_page` | int | Items per page (default 15) |
| `category` | string | `mountain`, `beach`, `island`, `falls`, `river`, `lake`, `campsite`, `historical`, `food_destination`, `road_trip`, `hidden_gem` |
| `search` | string | Search title, description, meeting_place |
| `region` | string | Filter by place region (partial match) |
| `province` | string | Filter by place province (partial match) |
| `difficulty` | string | `easy`, `moderate`, `hard`, `extreme` |
| `date_from` | date | Events on or after (YYYY-MM-DD) |
| `date_to` | date | Events on or before |
| `fee_min` | number | Minimum fee |
| `fee_max` | number | Maximum fee |
| `available_only` | bool | Only events with open slots |
| `lat` | float | User latitude (enables near-me + distance_km) |
| `lng` | float | User longitude |
| `radius` | int | Radius in km (default 100, requires lat/lng) |
| `sort` | string | `date` (default), `fee_low`, `fee_high`, `newest`, `popular` |

**Example:** `GET /api/events?category=mountain&difficulty=hard&sort=newest`

**Near-me:** `GET /api/events?lat=14.5995&lng=120.9842&radius=50`

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "organizer_id": 5,
      "place_id": 12,
      "title": "Mt. Pulag Sea of Clouds",
      "slug": "mt-pulag-sea-of-clouds",
      "description": "Experience the famous sea of clouds...",
      "cover_image_path": "event-covers/abc123.jpg",
      "cover_image_url": "https://s3.../event-covers/abc123.jpg",
      "category": "mountain",
      "event_date": "2026-05-15",
      "end_date": "2026-05-16",
      "meeting_place": "Baguio City, Session Road",
      "meeting_time": "5:00 AM",
      "meeting_lat": 16.4023,
      "meeting_lng": 120.596,
      "fee": "2500.00",
      "max_slots": 30,
      "difficulty": "hard",
      "status": "published",
      "auto_approve_bookings": false,
      "created_at": "2026-04-01T10:00:00.000000Z",
      "updated_at": "2026-04-02T08:00:00.000000Z",
      "distance_km": 12.5,
      "place": {
        "id": 12,
        "name": "Mt. Pulag",
        "slug": "mt-pulag",
        "category": "mountain",
        "region": "Region I — Ilocos Region",
        "province": "Benguet",
        "latitude": 16.5897,
        "longitude": 120.8979,
        "xp_reward": 100,
        "points_reward": 50,
        "cover_image_url": "https://s3.../places/mt-pulag.jpg"
      },
      "organizer": {
        "id": 5,
        "name": "Adventure PH",
        "username": "adventureph",
        "avatar_path": "avatars/org5.jpg",
        "is_verified_organizer": true
      }
    }
  ],
  "last_page": 3,
  "per_page": 15,
  "total": 42
}
```

> `distance_km` only present when `lat`/`lng` are provided.

---

## GET /events/{slug}

Full event detail with place, organizer, itinerary, rules, gallery.

**Response 200:**
```json
{
  "id": 1,
  "title": "Mt. Pulag Sea of Clouds",
  "slug": "mt-pulag-sea-of-clouds",
  "description": "Experience the famous sea of clouds...",
  "cover_image_url": "https://s3.../event-covers/abc123.jpg",
  "category": "mountain",
  "event_date": "2026-05-15",
  "end_date": "2026-05-16",
  "meeting_place": "Baguio City, Session Road",
  "meeting_time": "5:00 AM",
  "meeting_lat": 16.4023,
  "meeting_lng": 120.596,
  "fee": "2500.00",
  "max_slots": 30,
  "difficulty": "hard",
  "status": "published",
  "auto_approve_bookings": false,
  "place": {
    "id": 12,
    "name": "Mt. Pulag",
    "slug": "mt-pulag",
    "category": "mountain",
    "description": "Highest peak in Luzon...",
    "region": "Region I — Ilocos Region",
    "province": "Benguet",
    "latitude": 16.5897,
    "longitude": 120.8979,
    "xp_reward": 100,
    "points_reward": 50,
    "cover_image_url": "https://s3.../places/mt-pulag.jpg",
    "images": [
      { "id": 1, "image_url": "https://s3.../place-images/pulag1.jpg" }
    ]
  },
  "organizer": {
    "id": 5,
    "name": "Adventure PH",
    "username": "adventureph",
    "avatar_path": "avatars/org5.jpg",
    "is_verified_organizer": true,
    "organization_name": "Adventure Philippines Inc."
  },
  "itinerary": [
    {
      "id": 1,
      "place_id": 12,
      "custom_place_name": null,
      "custom_place_location": null,
      "day_number": 1,
      "sort_order": 1,
      "activity": "Summit climb",
      "time_slot": "2:00 AM - 6:00 AM",
      "notes": "Bring headlamp",
      "place": { "id": 12, "name": "Mt. Pulag", "slug": "mt-pulag", "xp_reward": 100 }
    },
    {
      "id": 2,
      "place_id": null,
      "custom_place_name": "Ranger Station Camp",
      "custom_place_location": "Near Ambangeg Trail entrance",
      "day_number": 1,
      "sort_order": 2,
      "activity": "Rest & lunch",
      "time_slot": "12:00 PM - 1:00 PM",
      "notes": null,
      "place": null
    }
  ],
  "rules": [
    { "rule_type": "requirement", "content": "Must bring valid ID and medical certificate", "sort_order": 1 },
    { "rule_type": "what_to_bring", "content": "Headlamp, warm jacket, trail shoes", "sort_order": 2 },
    { "rule_type": "policy", "content": "No refund within 3 days of event", "sort_order": 3 }
  ],
  "photos": [
    { "id": 1, "photo_url": "https://s3.../event-photos/img1.jpg", "caption": null },
    { "id": 2, "photo_url": "https://s3.../event-photos/img2.jpg", "caption": null }
  ]
}
```

**404:** `{ "message": "Event not found." }`

> **Itinerary notes:** `place_id = null` means custom place (no XP). Use `custom_place_name` for display.
> **rule_type values:** `requirement`, `inclusion`, `exclusion`, `reminder`, `policy`, `instruction`, `what_to_bring`

---

## POST /events/{id}/book

Book an event for the authenticated user. No request body needed.

**Response 201:**
```json
{
  "message": "Booking created successfully.",
  "booking": {
    "id": 15,
    "event_id": 1,
    "user_id": 3,
    "status": "approved",
    "approved_at": "2026-04-05T12:00:00.000000Z",
    "rejected_at": null,
    "notes": null,
    "created_at": "2026-04-05T12:00:00.000000Z",
    "event": {
      "id": 1,
      "title": "Mt. Pulag Sea of Clouds",
      "slug": "mt-pulag-sea-of-clouds",
      "event_date": "2026-05-15",
      "fee": "2500.00",
      "status": "published",
      "cover_image_url": "https://s3.../event-covers/abc123.jpg"
    }
  }
}
```

> If event has `auto_approve_bookings = true`, status is `"approved"` immediately. Otherwise `"pending"`.

**422:** `{ "message": "No slots available." }` or `{ "message": "You have already booked this event." }`

---

## GET /my-bookings

List authenticated user's bookings. Paginated.

**Query Parameters:**

| Param | Type | Description |
|---|---|---|
| `page` | int | Page number |
| `per_page` | int | Items per page (default 15) |
| `status` | string | Filter: `pending`, `approved`, `rejected`, `cancelled` |

**Example:** `GET /api/my-bookings?status=approved`

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 15,
      "event_id": 1,
      "user_id": 3,
      "status": "approved",
      "approved_at": "2026-04-05T12:00:00.000000Z",
      "rejected_at": null,
      "notes": null,
      "created_at": "2026-04-05T12:00:00.000000Z",
      "updated_at": "2026-04-05T12:00:00.000000Z",
      "event": {
        "id": 1,
        "title": "Mt. Pulag Sea of Clouds",
        "slug": "mt-pulag-sea-of-clouds",
        "cover_image_path": "event-covers/abc123.jpg",
        "cover_image_url": "https://s3.../event-covers/abc123.jpg",
        "event_date": "2026-05-15",
        "end_date": "2026-05-16",
        "meeting_place": "Baguio City, Session Road",
        "meeting_time": "5:00 AM",
        "fee": "2500.00",
        "status": "published",
        "difficulty": "hard",
        "category": "mountain",
        "organizer": {
          "id": 5,
          "name": "Adventure PH",
          "username": "adventureph",
          "avatar_path": "avatars/org5.jpg"
        }
      }
    },
    {
      "id": 8,
      "event_id": 3,
      "user_id": 3,
      "status": "pending",
      "approved_at": null,
      "rejected_at": null,
      "notes": null,
      "created_at": "2026-04-03T09:00:00.000000Z",
      "updated_at": "2026-04-03T09:00:00.000000Z",
      "event": {
        "id": 3,
        "title": "Kawasan Falls Canyoneering",
        "slug": "kawasan-falls-canyoneering",
        "cover_image_url": "https://s3.../event-covers/kawasan.jpg",
        "event_date": "2026-06-01",
        "end_date": null,
        "meeting_place": "Cebu City, SM Seaside",
        "meeting_time": "6:00 AM",
        "fee": "1800.00",
        "status": "published",
        "difficulty": "moderate",
        "category": "falls",
        "organizer": {
          "id": 7,
          "name": "Cebu Adventures",
          "username": "cebuadv",
          "avatar_path": null
        }
      }
    }
  ],
  "last_page": 1,
  "per_page": 15,
  "total": 2
}
```

---

## DELETE /bookings/{id}

Cancel own booking.

**Response 200:**
```json
{
  "message": "Booking cancelled successfully.",
  "booking": {
    "id": 15,
    "event_id": 1,
    "user_id": 3,
    "status": "cancelled",
    "approved_at": null,
    "rejected_at": null,
    "notes": null,
    "created_at": "2026-04-05T12:00:00.000000Z",
    "updated_at": "2026-04-05T12:30:00.000000Z"
  }
}
```

**403:** `{ "message": "You do not own this booking." }`
**422:** `{ "message": "Booking is already cancelled." }`

---

## Field Reference

### Booking Status Values
| Status | Description |
|---|---|
| `pending` | Waiting for organizer approval |
| `approved` | Confirmed, user is attending |
| `rejected` | Organizer rejected the booking |
| `cancelled` | User cancelled their booking |

### Event Difficulty Values
`easy`, `moderate`, `hard`, `extreme`

### Rule Type Values
`requirement`, `inclusion`, `exclusion`, `reminder`, `policy`, `instruction`, `what_to_bring`

### Category Values
`mountain`, `beach`, `island`, `falls`, `river`, `lake`, `campsite`, `historical`, `food_destination`, `road_trip`, `hidden_gem`
