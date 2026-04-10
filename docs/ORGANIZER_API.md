# PinasLakbay — Organizer API Documentation

Base URL: `https://your-domain.com/api`
Auth: `Authorization: Bearer {sanctum_token}`
All endpoints require `role:organizer`.

---

## Registration & Onboarding

### POST /auth/firebase
Same as explorer login. If the user has `role: organizer`, the app should redirect to onboarding if `onboarding_completed` is false.

**Response includes:**
```json
{
  "token": "1|abc...",
  "user": {
    "id": 5,
    "role": "organizer",
    "onboarding_completed": false
  }
}
```

---

### POST /organizer/onboarding

Complete organizer profile setup. Required before creating events.

**Payload (JSON):**
```json
{
  "organizer_type": "solo",
  "organization_name": "Summit Seekers PH",
  "phone": "09171234567",
  "organizer_bio": "Mountain guide since 2020. Specializing in Cordillera peaks.",
  "specialties": ["mountain", "campsite", "hidden_gem"],
  "social_facebook": "https://facebook.com/summitseekers",
  "social_instagram": "@summitseekers",
  "social_website": "https://summitseekers.ph"
}
```

| Field | Type | Required | Values |
|---|---|---|---|
| `organizer_type` | string | yes | `solo`, `agency`, `organization` |
| `organization_name` | string | no | Max 255 |
| `phone` | string | yes | Max 20 |
| `organizer_bio` | string | yes | Max 500 |
| `specialties` | array | yes | Min 1. Values: `mountain`, `beach`, `island`, `falls`, `river`, `lake`, `campsite`, `historical`, `food_destination`, `road_trip`, `hidden_gem`, `city_tour` |
| `social_facebook` | url | no | |
| `social_instagram` | string | no | |
| `social_website` | url | no | |

**Response 200:**
```json
{
  "message": "Onboarding complete.",
  "user": {
    "id": 5,
    "name": "Juan Guide",
    "organizer_type": "solo",
    "organization_name": "Summit Seekers PH",
    "phone": "09171234567",
    "organizer_bio": "Mountain guide since 2020...",
    "specialties": ["mountain", "campsite", "hidden_gem"],
    "social_links": {
      "facebook": "https://facebook.com/summitseekers",
      "instagram": "@summitseekers",
      "website": "https://summitseekers.ph"
    },
    "onboarding_completed": true,
    "is_verified_organizer": false
  }
}
```

---

## Profile

### GET /organizer/profile

Get organizer profile details.

**Response 200:**
```json
{
  "id": 5,
  "name": "Juan Guide",
  "email": "juan@example.com",
  "username": "juanguide",
  "avatar_url": "https://s3.../avatars/juan.jpg",
  "organizer_type": "solo",
  "organization_name": "Summit Seekers PH",
  "phone": "09171234567",
  "organizer_bio": "Mountain guide since 2020...",
  "specialties": ["mountain", "campsite", "hidden_gem"],
  "social_links": {
    "facebook": "https://facebook.com/summitseekers",
    "instagram": "@summitseekers",
    "website": null
  },
  "is_verified_organizer": true,
  "onboarding_completed": true
}
```

---

### POST /organizer/profile

Update organizer profile. Partial updates OK — only send fields you want to change.

**Payload (JSON):**
```json
{
  "organization_name": "New Name PH",
  "phone": "09181234567",
  "organizer_bio": "Updated bio...",
  "specialties": ["mountain", "beach"],
  "social_facebook": "https://facebook.com/newpage",
  "social_instagram": "@newhandle"
}
```

**Response 200:**
```json
{
  "message": "Profile updated.",
  "user": { ... }
}
```

---

## Dashboard

### GET /organizer/dashboard

Get dashboard stats and upcoming events.

**Response 200:**
```json
{
  "stats": {
    "total_events": 12,
    "published_events": 5,
    "pending_review": 2,
    "completed_events": 4,
    "total_bookings": 87,
    "pending_bookings": 3,
    "is_verified": true
  },
  "upcoming_events": [
    {
      "id": 15,
      "title": "Mt. Pulag Sea of Clouds",
      "slug": "mt-pulag-sea-of-clouds",
      "event_date": "2026-05-15",
      "status": "published",
      "max_slots": 30,
      "cover_image_url": "https://s3.../event-covers/abc.jpg",
      "available_slots": 12,
      "booked_count": 18
    }
  ]
}
```

---

## Events

### GET /organizer/events

List my events. Paginated.

**Query:** `?status=draft|pending_review|published|completed|cancelled&per_page=15&page=1`

**Response 200:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 15,
      "title": "Mt. Pulag Sea of Clouds",
      "slug": "mt-pulag-sea-of-clouds",
      "event_date": "2026-05-15",
      "end_date": "2026-05-16",
      "status": "published",
      "fee": "2500.00",
      "max_slots": 30,
      "difficulty": "hard",
      "cover_image_url": "https://s3.../event-covers/abc.jpg",
      "available_slots": 12,
      "booked_count": 18,
      "pending_bookings_count": 3,
      "created_at": "2026-04-01T10:00:00.000000Z"
    }
  ],
  "last_page": 2,
  "per_page": 15,
  "total": 22
}
```

---

### GET /organizer/events/{id}

Event detail with bookings, itinerary, rules, photos.

**Response 200:**
```json
{
  "id": 15,
  "title": "Mt. Pulag Sea of Clouds",
  "slug": "mt-pulag-sea-of-clouds",
  "description": "Experience the famous sea of clouds...",
  "cover_image_url": "https://s3.../event-covers/abc.jpg",
  "event_date": "2026-05-15",
  "end_date": "2026-05-16",
  "meeting_place": "Baguio City",
  "meeting_time": "5:00 AM",
  "meeting_lat": 16.4023,
  "meeting_lng": 120.596,
  "fee": "2500.00",
  "max_slots": 30,
  "difficulty": "hard",
  "status": "published",
  "auto_approve_bookings": false,
  "available_slots": 12,
  "booked_count": 18,
  "place": { "id": 12, "name": "Mt. Pulag" },
  "itinerary": [
    {
      "place_id": 12,
      "custom_place_name": null,
      "day_number": 1,
      "sort_order": 1,
      "activity": "Summit climb",
      "time_slot": "2:00 AM - 6:00 AM",
      "notes": "Bring headlamp",
      "place": { "id": 12, "name": "Mt. Pulag" }
    }
  ],
  "rules": [
    { "rule_type": "requirement", "content": "Valid ID required", "sort_order": 1 }
  ],
  "photos": [
    { "id": 1, "photo_url": "https://s3.../event-photos/img1.jpg", "caption": null }
  ],
  "bookings": [
    {
      "id": 20,
      "user_id": 3,
      "status": "pending",
      "created_at": "2026-04-05T12:00:00.000000Z",
      "user": { "id": 3, "name": "Maria Santos", "username": "mariasantos", "avatar_path": "avatars/maria.jpg" }
    },
    {
      "id": 18,
      "user_id": 7,
      "status": "approved",
      "approved_at": "2026-04-04T09:00:00.000000Z",
      "user": { "id": 7, "name": "Juan DC", "username": "juandc", "avatar_path": null }
    }
  ]
}
```

---

### POST /organizer/events

Create event. Multipart/form-data.

**Payload:**
```
title: "Mt. Pulag Sea of Clouds"          // required
slug: "mt-pulag-sea-of-clouds"             // required, unique
description: "Experience the famous..."     // optional
cover_image: (file)                         // optional, max 10MB
event_date: "2026-05-15"                   // required, future date
end_date: "2026-05-16"                     // optional
meeting_place: "Baguio City"               // optional
meeting_time: "5:00 AM"                    // optional
meeting_lat: 16.4023                       // optional
meeting_lng: 120.596                       // optional
fee: 2500                                  // optional, default 0
max_slots: 30                              // required
difficulty: "hard"                         // optional: easy, moderate, hard, extreme
auto_approve_bookings: false               // optional
gallery[]: (file)                          // optional, up to 10 files
itinerary: (JSON string)                   // optional, see below
rules: (JSON string)                       // optional, see below
```

**Itinerary JSON:**
```json
[
  {
    "place_id": 12,
    "day": 1,
    "activity": "Summit climb",
    "time": "2:00 AM - 6:00 AM",
    "notes": "Bring headlamp"
  },
  {
    "place_id": null,
    "custom_name": "Ranger Station",
    "custom_location": "Near Ambangeg Trail",
    "day": 1,
    "activity": "Rest",
    "time": "12:00 PM",
    "notes": null
  }
]
```

**Rules JSON:**
```json
[
  { "type": "requirement", "content": "Valid ID required" },
  { "type": "what_to_bring", "content": "Headlamp, warm jacket" },
  { "type": "policy", "content": "No refund within 3 days" }
]
```

Rule types: `requirement`, `inclusion`, `exclusion`, `reminder`, `policy`, `instruction`, `what_to_bring`

**Response 201:**
```json
{
  "message": "Event created.",
  "event": { ... full event object with itinerary, rules, photos }
}
```

> Event is created as `draft`. Must submit for review before publishing.

---

### POST /organizer/events/{id}/update

Update draft event. Same payload as create (all fields optional). Multipart/form-data.

**Extra fields:**
```
remove_photos[]: 1,3    // photo IDs to remove
```

**Response 200:**
```json
{
  "message": "Event updated.",
  "event": { ... }
}
```

**422:** `{ "message": "Only draft events can be edited." }`

---

### POST /organizer/events/{id}/publish

Submit event for admin review. Requires verified organizer account.

**Response 200:** `{ "message": "Event submitted for review." }`
**422:** `{ "message": "Your account must be verified by admin before you can submit events for review." }`

---

### POST /organizer/events/{id}/cancel

Cancel event.

**Response 200:** `{ "message": "Event cancelled." }`

---

### POST /organizer/events/{id}/complete

Complete event. Auto-unlocks places for approved attendees. Only works on event date or after.

**Response 200:** `{ "message": "Event completed. Places unlocked for attendees." }`
**422:** `{ "message": "Cannot complete an event with a future date." }`

---

## Booking Management

### POST /organizer/bookings/{id}/approve

Approve a pending booking.

**Response 200:** `{ "message": "Booking approved." }`
**422:** `{ "message": "Only pending bookings can be approved." }`

---

### POST /organizer/bookings/{id}/reject

Reject a pending booking.

**Response 200:** `{ "message": "Booking rejected." }`

---

### POST /organizer/events/{id}/approve-all

Approve all pending bookings for an event. Stops if slots run out.

**Response 200:** `{ "message": "Approved 5 bookings." }`

---

## Utility

### GET /organizer/places

List all active places for itinerary picker. Not paginated.

**Response 200:**
```json
{
  "data": [
    { "id": 1, "name": "Mt. Apo", "slug": "mt-apo", "category": "mountain" },
    { "id": 2, "name": "Boracay Beach", "slug": "boracay-beach", "category": "beach" }
  ]
}
```

---

## Event Status Flow

```
draft → pending_review → published → completed
                ↓                       ↓
             (rejected → draft)     cancelled
```

- `draft` — can edit, can submit for review (if verified)
- `pending_review` — waiting for admin approval
- `published` — live, accepting bookings
- `full` — all slots taken
- `completed` — event done, places unlocked
- `cancelled` — cancelled by organizer
