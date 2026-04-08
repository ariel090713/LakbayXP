# LakbayXP — AI Cron Jobs Documentation

## Overview

Two automated cron jobs that use Gemini AI + Pexels to grow and maintain the places database.

| Job | Command | Schedule | What it does |
|-----|---------|----------|-------------|
| Discover | `places:discover` | Daily 2:00 AM | Finds new PH travel places via AI |
| Update | `places:update` | Daily 3:00 AM | Corrects/enhances existing place data + fetches photos |

---

## Environment Variables Required

```env
GEMINI_API_KEY=your-gemini-api-key
PEXELS_API_KEY=your-pexels-api-key
UNSPLASH_ACCESS_KEY=your-unsplash-key    # optional fallback
```

Get keys from:
- Gemini: https://aistudio.google.com/apikey
- Pexels: https://www.pexels.com/api/ (sign up → copy key, no review needed)
- Unsplash: https://unsplash.com/developers (optional)

---

## Job 1: Discover New Places

**Command:** `php artisan places:discover`

**What it does:**
1. Gets all existing place names from DB
2. Sends prompt to Gemini 2.0 Flash asking for new PH destinations not in the list
3. AI returns JSON with name, category, description, region, province, lat/lng, XP, and category-specific meta
4. Creates each place in DB with `is_active = true`
5. Saves category-specific meta fields (elevation, trail class, sand type, etc.)
6. Optionally fetches a cover photo from Pexels/Unsplash

**Options:**
```bash
php artisan places:discover                          # 10 places, mixed categories
php artisan places:discover --count=50               # 50 places
php artisan places:discover --category=mountain      # mountains only
php artisan places:discover --count=20 --with-photos # 20 places + fetch photos
```

**AI Prompt generates per category:**
- Mountain: elevation, trail class (1-9), difficulty, trail type, hours, jump-off, permit/guide
- Beach: sand type, water activities, entrance fee, best season, accessibility
- Island: how to get there, boat ride minutes, overnight, entrance fee
- Falls: height, layers, swimming, trek minutes, difficulty
- River: activity type, rapids class, length, best season
- Lake: elevation, swimming/boating, trek required
- Campsite: type, tent rental, facilities, fee, phone signal
- Historical: period, year built, heritage status, guided tour
- Food: cuisine, must-try dish, price range, food type
- Road trip: distance, drive hours, highlights, road condition, vehicle
- Hidden gem: discovery tip, crowd level, best time

**Rate limits:**
- Gemini: 1 request per batch (all places in one prompt)
- Pexels: 1 sec delay between photo fetches (200 req/hr limit)

**Logs:** `storage/logs/places-discover.log`

---

## Job 2: Update Existing Places

**Command:** `php artisan places:update`

**What it does:**
1. Picks places with incomplete data (no description, default text, missing coords, 0 XP)
2. Sends each place to Gemini for verification and enhancement
3. AI returns corrected description (5-8 sentences), accurate lat/lng, region, province, XP
4. AI also returns category-specific meta fields
5. Updates the place record and saves meta
6. Optionally fetches photos for places without images

**Options:**
```bash
php artisan places:update                        # 20 places with missing data
php artisan places:update --limit=50             # 50 places
php artisan places:update --photos               # also fetch missing photos
php artisan places:update --force                # update ALL places, not just incomplete
php artisan places:update --limit=100 --photos --force  # full update run
```

**Priority order:**
1. Places with null/empty description
2. Places with default "A beautiful..." description
3. Places with null lat/lng
4. Places with 0 XP reward
5. If none incomplete, updates oldest (by `updated_at`)

**Rate limits:**
- Gemini: 1 sec delay between places
- Pexels: 1 sec delay between photo fetches

**Logs:** `storage/logs/places-update.log`

---

## Schedule (automatic)

Defined in `routes/console.php`:

```php
// Daily at 2 AM — discover 10 new places with photos
Schedule::command('places:discover --count=10 --with-photos')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Daily at 3 AM — update 20 places with correct data + photos
Schedule::command('places:update --limit=20 --photos')
    ->dailyAt('03:00')
    ->withoutOverlapping();
```

**Growth rate:** 10 new places/day = ~300/month = ~3,650/year

**Laravel Cloud:** Scheduler runs automatically if you have `php artisan schedule:run` in your deploy config (Laravel Cloud handles this by default).

---

## Manual Bulk Run

To quickly populate 2000+ places:

```bash
# Run in batches to avoid Gemini token limits
php artisan places:discover --count=10 --category=mountain --with-photos
php artisan places:discover --count=10 --category=beach --with-photos
php artisan places:discover --count=10 --category=island --with-photos
php artisan places:discover --count=10 --category=falls --with-photos
php artisan places:discover --count=10 --category=river --with-photos
php artisan places:discover --count=10 --category=lake --with-photos
php artisan places:discover --count=10 --category=campsite --with-photos
php artisan places:discover --count=10 --category=historical --with-photos
php artisan places:discover --count=10 --category=food_destination --with-photos
php artisan places:discover --count=10 --category=road_trip --with-photos
php artisan places:discover --count=10 --category=hidden_gem --with-photos

# Then update all with correct details
php artisan places:update --limit=200 --photos --force
```

Repeat the discover batches multiple times (the AI skips duplicates).

---

## Photo Sources

| Source | Priority | Rate Limit | Quality |
|--------|----------|-----------|---------|
| Pexels | 1st (default) | 200 req/hr | Good |
| Unsplash | 2nd (fallback) | 50 req/hr | Higher |

Photos are:
- Downloaded from the API
- Uploaded to S3 under `place-gallery/` with random hash filenames
- Saved in `place_images` table with `image_source` field
- Auto-set as cover photo if place has none

---

## Files

```
app/Services/PlaceAiService.php          # AI + photo logic
app/Console/Commands/DiscoverPlaces.php  # Discover command
app/Console/Commands/UpdatePlaces.php    # Update command
routes/console.php                       # Schedule config
config/place_fields.php                  # Category-specific field definitions
```
