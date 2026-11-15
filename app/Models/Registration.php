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
    ];

    /** Blocks no longer offered; kept only so talks of past editions still show their block. */
    public const RETIRED_BLOCKS = [
        'students' => 'Blok 3 – AI očami študentov',
    ];

    public function blockLabel(): ?string
    {
        if (! $this->block) {
            return null;
        }

        return self::BLOCKS[$this->block] ?? self::RETIRED_BLOCKS[$this->block] ?? $this->block;
    }

    /** Name with academic titles, e.g. "Ing. Jana Nováková, PhD." */
    public function fullName(): string
    {
        $name = trim(implode(' ', array_filter([$this->title_before, $this->name])));

        return $this->title_after ? $name . ', ' . $this->title_after : $name;
    }
}
