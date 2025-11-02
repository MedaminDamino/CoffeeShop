<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\SetPasswordNotification;
 

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'birthday',
        'role',
        'feedback',
        'google_id',
        'avatar',
        'is_completed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birthday' => 'date',
    ];
}
