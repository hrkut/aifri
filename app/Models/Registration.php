<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
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
        'title',
        'abstract',
        'keywords',
        'block',
        'notes',
    ];

    protected $casts = [
        'online_participation' => 'boolean',
    ];
}
