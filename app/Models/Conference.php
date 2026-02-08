<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    protected $fillable = [
        'start_time',
        'presentation_minutes',
        'break_minutes',
    ];

    protected $casts = [
        'presentation_minutes' => 'integer',
        'break_minutes' => 'integer',
    ];
}

