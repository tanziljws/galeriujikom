<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
<<<<<<< HEAD
=======
use Laravel\Sanctum\HasApiTokens;
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
<<<<<<< HEAD
    use HasFactory, Notifiable;
=======
    use HasApiTokens, HasFactory, Notifiable;
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function photoReactions(): HasMany
    {
        return $this->hasMany(PhotoReaction::class);
    }

    public function photoComments(): HasMany
    {
        return $this->hasMany(PhotoComment::class);
    }
}
