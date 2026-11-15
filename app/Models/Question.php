<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use BelongsToConference;

    protected $fillable = [
        'name',
        'email',
        'question',
        'answered',
    ];

    protected $casts = [
        'answered' => 'boolean',
    ];
}
