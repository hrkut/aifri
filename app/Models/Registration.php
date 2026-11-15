<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use BelongsToConference;

    protected $fillable = [
        'title_before',
        'name',
        'title_after',
        'email',
        'phone',
        'institution',
        'position',
        'participation_type',
        'online_participation',
        'time_start',
        'duration_minutes',
        'program_order',
        'title',
        'abstract',
        'keywords',
        'block',
        'notes',
        'record',
        'presentation',
    ];

    protected $casts = [
        'online_participation' => 'boolean',
    ];
}
