<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class User extends Model
{
    use HasFactory;
    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function events(){
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function registrations(){
        return $this->hasMany(EventRegistration::class);
    }
}
