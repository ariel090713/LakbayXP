<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\ExplorerLevel;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'bio',
        'avatar_path',
        'cover_photo_path',
        'role',
        'firebase_uid',
        'google_id',
        'explorer_level',
        'is_verified_organizer',
        'fcm_token',
        'total_points',
        'available_points',
        'xp',
        'level',
        'latitude',
        'longitude',
        'city',
        'organizer_type',
        'organization_name',
        'phone',
        'organizer_bio',
        'social_links',
        'specialties',
        'onboarding_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['avatar_url', 'cover_photo_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? \Storage::disk('s3')->url($this->avatar_path) : null;
    }

    public function getCoverPhotoUrlAttribute(): ?string
    {
        $path = $this->cover_photo_path ?? null;
        return $path ? \Storage::disk('s3')->url($path) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'explorer_level' => ExplorerLevel::class,
            'is_verified_organizer' => 'boolean',
            'onboarding_completed' => 'boolean',
            'social_links' => 'array',
            'specialties' => 'array',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Events organized by this user.
     */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Bookings made by this user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Places unlocked by this user.
     */
    public function unlockedPlaces(): BelongsToMany
    {
        return $this->belongsToMany(Place::class, 'place_unlocks')
            ->withTimestamps();
    }

    /**
     * Badges earned by this user.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('awarded_at', 'is_viewed');
    }

    /**
     * Users who follow this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Users this user is following.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Reward redemptions by this user.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(RewardRedemption::class);
    }

    public function xpHistory(): HasMany
    {
        return $this->hasMany(XpHistory::class);
    }
}
