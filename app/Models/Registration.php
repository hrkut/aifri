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

    public const ABSTRACT_MAX_WORDS = 300;

    /** Programme blocks a talk can be submitted to (keys stored in `block`). */
    public const BLOCKS = [
        'intro' => 'Úvodný blok – Vízia a trendy',
        'teaching' => 'Blok 1 – AI vo výučbe a výskume',
        'practice' => 'Blok 2 – AI v praxi',
        'students' => 'Blok 3 – AI očami študentov',
    ];

    public function blockLabel(): ?string
    {
        return $this->block ? (self::BLOCKS[$this->block] ?? $this->block) : null;
    }

    /** Name with academic titles, e.g. "Ing. Jana Nováková, PhD." */
    public function fullName(): string
    {
        $name = trim(implode(' ', array_filter([$this->title_before, $this->name])));

        return $this->title_after ? $name . ', ' . $this->title_after : $name;
    }
}
