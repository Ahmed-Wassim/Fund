<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZoomMeeting extends Model
{
    protected $guarded = [];
    protected $casts = [
        'start_time' => 'datetime',
    ];

    // Optional: relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}