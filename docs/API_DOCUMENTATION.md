


---

## ⚡ XP SYSTEM

### GET /xp-history
Current user's XP transaction history (paginated).

**Headers:** `Authorization: Bearer {token}`

**Query Params:** `?per_page=15&page=1`

**Response 200:**
```json
{
  "data": [
    {
      "id": 45,
      "user_id": 9,
      "amount": 150,
      "source": "place_unlock",
      "category": "mountain",
      "description": "Unlocked Mt. Pulag (+150 XP)",
      "place_id": 1,
      "badge_id": null,
      "event_id": 3,
      "granted_by": null,
      "balance_after": 310,
      "created_at": "2026-04-08T10:30:00Z",
      "place": { "id": 1, "name": "Mt. Pulag", "slug": "mt-pulag" },
      "badge": null,
      "event": { "id": 3, "title": "Mt. Pulag Sea of Clouds", "slug": "mt-pulag-soc" },
      "granted_by_user": null
    },
    {
      "id": 44,
      "amount": 50,
      "source": "badge",
      "category": null,
      "description": "Earned badge: Mountain Goat (+50 XP)",
      "badge": { "id": 3, "name": "Mountain Goat", "slug": "mountain-goat" },
      "balance_after": 160
    },
    {
      "id": 1,
      "amount": 10,
      "source": "welcome",
      "category": null,
      "description": "Welcome to LakbayXP! (+10 XP)",
      "balance_after": 10
    }
  ],
  "current_page": 1,
  "last_page": 3,
  "per_page": 15,
  "total": 45
}
```

**XP Sources:** `place_unlock`, `badge`, `event`, `welcome`, `admin`, `promo`, `referral`

---

### GET /xp-categories
Current user's XP breakdown by place category.

**Headers:** `Authorization: Bearer {token}`

**Response 200:**
```json
{
  "data": {
    "mountain": 450,
    "beach": 160,
    "island": 210,
    "falls": 80,
    "campsite": 70,
    "historical": 50
  }
}
```

---

### GET /leaderboard/category/{category}
Category-specific leaderboard. Ranks users by total XP earned in that category.

**Headers:** `Authorization: Bearer {token}`

**Path Params:** `category` — mountain, beach, island, falls, river, lake, campsite, historical, food_destination, road_trip, hidden_gem

**Query Params:** `?per_page=20&page=1`

**Example:** `GET /api/leaderboard/category/mountain`

**Response 200:**
```json
{
  "data": [
    {
      "user_id": 5,
      "category_xp": 450,
      "name": "Maria Santos",
      "username": "maria-santos-1",
      "avatar_url": "https://bucket.s3.amazonaws.com/avatars/abc.jpg",
      "level": 4,
      "total_xp": 580
    },
    {
      "user_id": 12,
      "category_xp": 320,
      "name": "Carlo Bautista",
      "username": "carlo-bautista-6",
      "avatar_url": null,
      "level": 3,
      "total_xp": 420
    }
  ],
  "current_page": 1,
  "last_page": 2,
  "per_page": 20,
  "total": 35
}
```

---

### POST /admin/grant-xp
Admin grants XP to any user (admin role required).

**Headers:** `Authorization: Bearer {admin_token}`

**Payload:**
```json
{
  "user_id": 5,
  "amount": 100,
  "description": "Promo bonus for early adopter",
  "category": "mountain"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `user_id` | int | yes | Target user ID |
| `amount` | int | yes | XP to grant (1-10000) |
| `description` | string | yes | Reason (logged in history) |
| `category` | string | no | Place category for category leaderboard |

**Response 200:**
```json
{
  "message": "Granted 100 XP to Maria Santos.",
  "result": {
    "leveled_up": true,
    "old_level": 3,
    "new_level": 4,
    "xp_gained": 100,
    "total_xp": 680
  }
}
```

**Error 403:** Not admin role
**Error 422:** Validation error
