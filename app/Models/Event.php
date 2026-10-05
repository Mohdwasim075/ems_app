<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'category_id',
        'title',
        'description',
        'image',
        'location',
        'start_at',
        'end_at',
        'capacity',
        'available_seats',
        'price',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_deadline' => 'datetime',
        'price' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::deleting(function ($event) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
        });

    }



    public function organizer()
    {
        return $this->belongsTo(User::class, 'role_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

   
}
