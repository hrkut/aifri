<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * One edition (ročník) of the conference.
 *
 * Exactly one edition is "active" – it is the one shown on the public site.
 * Admins may work with a different edition (e.g. browse the archive); that
 * choice is applied per request via Conference::setCurrent().
 */
class Conference extends Model
{
    private const CURRENT_KEY = 'conference.current';

    protected $fillable = [
        'year',
        'date',
        'registration_deadline',
        'notification_date',
        'teams_url',
        'is_active',
        'start_time',
        'presentation_minutes',
        'break_minutes',
    ];

    protected $casts = [
        'year' => 'integer',
        'date' => 'date',
        'registration_deadline' => 'date',
        'notification_date' => 'date',
        'is_active' => 'boolean',
        'presentation_minutes' => 'integer',
        'break_minutes' => 'integer',
    ];

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class)->withoutGlobalScope('conference');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->withoutGlobalScope('conference');
    }

    /**
     * The edition the current request works with (active one, unless an admin selected another).
     */
    public static function current(): self
    {
        // Cached on the request (not the container) so it never leaks into the next request.
        $attributes = request()->attributes;

        if (! $attributes->has(self::CURRENT_KEY)) {
            $attributes->set(self::CURRENT_KEY, static::active());
        }

        return $attributes->get(self::CURRENT_KEY);
    }

    public static function setCurrent(self $conference): void
    {
        request()->attributes->set(self::CURRENT_KEY, $conference);
    }

    /**
     * The edition shown on the public site.
     */
    public static function active(): self
    {
        return static::query()->where('is_active', true)->orderByDesc('date')->first()
            ?? static::query()->orderByDesc('date')->first()
            ?? static::create([
                'year' => (int) now()->year,
                'start_time' => '09:00',
                'presentation_minutes' => 15,
                'break_minutes' => 20,
                'is_active' => true,
            ]);
    }

    public function activate(): void
    {
        DB::transaction(function () {
            static::query()->where('id', '!=', $this->id)->update(['is_active' => false]);
            $this->forceFill(['is_active' => true])->save();
        });
    }

    /**
     * Past, unpublished editions are an archive: admins can browse them but not change them.
     */
    public function isReadOnly(): bool
    {
        return ! $this->is_active && $this->date !== null && $this->date->lt(today());
    }

    /** Ordinal of this edition (1 for the first year, 2 for the next one, …). */
    public function editionNumber(): int
    {
        return static::query()->where('year', '<=', $this->year)->count();
    }

    /** "H:i" start time, tolerant of the DB returning "H:i:s". */
    public function startTimeLabel(): string
    {
        try {
            return Carbon::parse((string) ($this->start_time ?: '09:00'))->format('G:i');
        } catch (\Throwable) {
            return '9:00';
        }
    }

    /** e.g. "2. 2. 2027" */
    public static function formatDate(?Carbon $date): ?string
    {
        return $date?->format('j. n. Y');
    }

    /** e.g. "2. 2. 2027 (utorok) od 9:00" */
    public function dateLabel(): string
    {
        if (! $this->date) {
            return 'termín bude upresnený';
        }

        $day = $this->date->copy()->locale('sk')->isoFormat('dddd');

        return self::formatDate($this->date) . " ({$day}) od " . $this->startTimeLabel();
    }

    /** Registration is offered until the deadline (or until the conference day if no deadline is set). */
    public function isRegistrationOpen(): bool
    {
        $until = $this->registration_deadline ?? $this->date;

        return $until !== null && now()->startOfDay()->lte($until);
    }
}
