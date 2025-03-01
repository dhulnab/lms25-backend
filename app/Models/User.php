<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'role', 'status', 'balance', 'fcm_token'];

    public function routeNotificationForWebPush($notification)
    {
        Log::info('User subscriptions:', [$this->webPushSubscriptions]);
        return $this->webPushSubscriptions; // Adjust according to your actual implementation
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the identifier that will be stored in the JWT.
     *
     * @return mixed
     */

    public function canAccessPanel(Panel $panel): bool
    {
        return ($this->role === 'admin') && $this->hasVerifiedEmail();
    }
    public function book_borrowings(): HasMany
    {
        return $this->hasMany(Book_borrowing::class);
    }
    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }
    public function borrow_requests(): HasMany
    {
        return $this->hasMany(Borrow_request::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
    public function book_for_sell_copies(): HasMany
    {
        return $this->hasMany(Book_for_sell_copy::class, 'user_id');
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
