<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserType;
use App\Filter\UserFilter;
use Modules\Media\Traits\MediaTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable,MediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'reset_code_expires_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password'  => 'hashed',
            'is_notify' => 'boolean',
            'is_banned' => 'boolean',
            'is_active' => 'boolean',
            'is_super'  => 'boolean',
            'user_type' => UserType::class,
        ];
    }

    public static $ADMINS = ['admin' => 'Admin', 'vendor' => 'Vendor'];

    protected array $mediaColumns = [
        'avatar' => [
            'is_single'  => true,
            'type'       => 'image',
            'option'     => 'avatar',
            'default'    => null,
        ]
    ];

    // attributes

    // scopes

    public function scopeFilter($query, UserFilter $filter)
    {
        return $filter->apply($query);
    }
    // relations
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function authenticationVerifications(): HasMany
    {
        return $this->hasMany(AuthenticationVerification::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    // funcations
    public function isBanned(): bool
    {
        if (!$this->is_banned) {
            return false;
        }

        if ($this->banned_until && $this->banned_until->isPast()) {
            $this->update(['is_banned' => false, 'banned_until' => null, 'ban_reason' => null]);
            return false;
        }

        return true;
    }

    public function routeNotificationForFcm()
    {
        return $this->devices->where('status', true)->whereNotNull('device_token')->pluck('device_token', 'device_type')->toArray();
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'last_login_at' => now(),
            'timezone'      => config('app.timezone')
        ];
    }

}
