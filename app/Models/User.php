<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'gender',
        'birth_date',
        'type',
        'title',
        'bio',
        'profile_image',
        'password',
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
        ];
    }

    /**
     * Get the identifier that will be stored in the JWT token.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function businesses()
    {
        return $this->hasMany(Business::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'investor_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'investor_id');
    }

    // Scopes
    public function scopeInvestors($query)
    {
        return $query->where('user_type', 'investor');
    }

    public function scopeOwners($query)
    {
        return $query->where('user_type', 'owner');
    }

    // Accessors
    public function getProfileImageUrlAttribute()
    {
        return $this->profile_image ? asset('storage/' . $this->profile_image) : null;
    }

    public function zoomMeetings()
    {
        return $this->hasMany(ZoomMeeting::class);
    }
}