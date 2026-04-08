


---

## 🔔 NOTIFICATIONS

### GET /notifications
Current user's notifications (paginated).

**Headers:** `Authorization: Bearer {token}`

**Query Params:** `?per_page=20&page=1`

**Response 200:**
```json
{
  "data": [
    {
      "id": 45,
      "user_id": 9,
      "type": "badge_earned",
      "title": "🏅 Badge Unlocked!",
      "body": "You earned the \"Mountain Goat\" badge! +50 points",
      "data": {
        "badge_id": 3,
        "badge_name": "Mountain Goat",
        "points": 50
      },
      "is_read": false,
      "created_at": "2026-04-08T14:30:00Z"
    },
    {
      "id": 44,
      "type": "level_up",
      "title": "⚡ Level Up!",
      "body": "You reached Level 4!",
      "data": { "old_level": 3, "new_level": 4 },
      "is_read": false
    },
    {
      "id": 43,
      "type": "place_unlocked",
      "title": "🔓 Place Unlocked!",
      "body": "You unlocked Mt. Pulag! +150 XP",
      "data": { "place_id": 1, "place_name": "Mt. Pulag", "xp_earned": 150 },
      "is_read": true
    },
    {
      "id": 42,
      "type": "booking_approved",
      "title": "🎫 Booking Confirmed!",
      "body": "You're in! Mt. Pulag Sea of Clouds on Apr 22, 2026",
      "data": { "booking_id": 15, "event_id": 1, "event_title": "Mt. Pulag Sea of Clouds" },
      "is_read": true
    },
    {
      "id": 41,
      "type": "booking_rejected",
      "title": "Booking Update",
      "body": "Your booking for Siargao Surf Camp was not approved.",
      "data": { "booking_id": 16, "event_id": 5 },
      "is_read": true
    },
    {
      "id": 40,
      "type": "event_completed",
      "title": "🏆 Adventure Complete!",
      "body": "You completed Mt. Pulag Sea of Clouds!",
      "data": { "event_id": 1, "event_title": "Mt. Pulag Sea of Clouds" },
      "is_read": true
    },
    {
      "id": 39,
      "type": "follow",
      "title": "👤 New Follower",
      "body": "Maria Santos started following you.",
      "data": { "follower_id": 5, "follower_name": "Maria Santos", "follower_username": "maria-santos-1" },
      "is_read": true
    },
    {
      "id": 38,
      "type": "comment",
      "title": "💬 New Comment",
      "body": "Carlo Bautista commented on your post.",
      "data": { "post_id": 12, "commenter_id": 7, "commenter_name": "Carlo Bautista" },
      "is_read": true
    },
    {
      "id": 37,
      "type": "reaction",
      "title": "❤️ New Reaction",
      "body": "Maria Santos reacted to your post.",
      "data": { "post_id": 12, "reactor_id": 5, "reaction_type": "love" },
      "is_read": true
    },
    {
      "id": 36,
      "type": "xp_earned",
      "title": "⚡ XP Bonus!",
      "body": "+100 XP: Promo bonus for early adopter",
      "data": { "amount": 100 },
      "is_read": true
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 20,
  "total": 45
}
```

**Notification Types:**

| Type | Trigger | Data Fields |
|------|---------|-------------|
| `badge_earned` | Badge awarded | `badge_id`, `badge_name`, `points` |
| `level_up` | XP causes level increase | `old_level`, `new_level` |
| `place_unlocked` | Place unlocked | `place_id`, `place_name`, `xp_earned` |
| `booking_approved` | Organizer approves booking | `booking_id`, `event_id`, `event_title` |
| `booking_rejected` | Organizer rejects booking | `booking_id`, `event_id` |
| `event_completed` | Event marked complete | `event_id`, `event_title` |
| `follow` | Someone follows you | `follower_id`, `follower_name`, `follower_username` |
| `comment` | Someone comments on your post | `post_id`, `commenter_id`, `commenter_name` |
| `reaction` | Someone reacts to your post | `post_id`, `reactor_id`, `reaction_type` |
| `xp_earned` | Admin grants XP | `amount` |
| `booking_created` | New booking (for organizer) | `booking_id`, `event_id` |

---

### GET /notifications/unread-count
Unread notification count (for badge dot on tab).

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{ "unread_count": 3 }
```

---

### POST /notifications/{id}/read
Mark a single notification as read.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{ "message": "Marked as read." }
```

**Error 403:** Not your notification.

---

### POST /notifications/read-all
Mark all notifications as read.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{ "message": "All marked as read." }
```

---

## 🏅 MY BADGES

### GET /my-badges
Current user's earned badges with viewed status.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Welcome Explorer",
      "slug": "welcome-explorer",
      "description": "Welcome to LakbayXP!",
      "icon_path": "badges/welcome.png",
      "icon_url": "https://bucket.s3.amazonaws.com/badges/welcome.png",
      "points": 10,
      "xp_reward": 10,
      "pivot": {
        "awarded_at": "2026-04-08T10:00:00Z",
        "is_viewed": false
      }
    },
    {
      "id": 3,
      "name": "Mountain Goat",
      "slug": "mountain-goat",
      "icon_url": "https://bucket.s3.amazonaws.com/badges/mountain-goat.png",
      "points": 50,
      "pivot": {
        "awarded_at": "2026-04-08T12:00:00Z",
        "is_viewed": true
      }
    }
  ],
  "unviewed_count": 1
}
```

---

### POST /my-badges/{badge_id}/view
Mark a badge as viewed.

**Response 200:** `{ "message": "Badge marked as viewed." }`

---

### POST /my-badges/view-all
Mark all badges as viewed.

**Response 200:** `{ "message": "All badges marked as viewed." }`
