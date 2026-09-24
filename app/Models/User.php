<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'role_id',
        'email',
        'password',
        'phone_number',
        'city',
        'state',
        'zip',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'events_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'user_id');
    }

    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
