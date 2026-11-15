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
        'contribution_deadline',
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
        'contribution_deadline' => 'date',
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
        return ! $this->is_active && $this->date !== null && $this->date->toDateString() < local_now()->toDateString();
    }

    /** Ordinal of this edition (1 for the first year, 2 for the next one, …). */
    public function editionNumber(): int
    {
        // asked several times per page (stamp, recordings list); count only once per edition
        return $this->editionNumber ??= static::query()->where('year', '<=', $this->year)->count();
    }

    private ?int $editionNumber = null;

    /** "2. ročník" / "2nd edition" in the current language. */
    public function editionLabel(): string
    {
        $n = $this->editionNumber();

        if (app()->getLocale() !== 'en') {
            return "{$n}. ročník";
        }

        $suffix = in_array($n % 100, [11, 12, 13], true) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th');

        return "{$n}{$suffix} edition";
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
        if ($date === null) {
            return null;
        }

        // "2. 2. 2027" in Slovak, "2 February 2027" in English
        return app()->getLocale() === 'en'
            ? $date->copy()->locale('en')->isoFormat('D MMMM YYYY')
            : $date->format('j. n. Y');
    }

    /** e.g. "2. 2. 2027 (utorok) od 9:00" / "Tuesday, 2 February 2027, from 9:00" */
    public function dateLabel(): string
    {
        if (! $this->date) {
            return __('termín bude upresnený');
        }

        if (app()->getLocale() === 'en') {
            return $this->date->copy()->locale('en')->isoFormat('dddd') . ', ' . self::formatDate($this->date)
                . ', from ' . $this->startTimeLabel();
        }

        $day = $this->date->copy()->locale('sk')->isoFormat('dddd');

        return self::formatDate($this->date) . " ({$day}) od " . $this->startTimeLabel();
    }

    /** Registration is offered until the deadline (or until the conference day if no deadline is set). */
    public function isRegistrationOpen(): bool
    {
        return self::notPassed($this->registration_deadline ?? $this->date);
    }

    /** Last day for submitting a contribution (falls back to the registration deadline). */
    public function contributionDeadline(): ?Carbon
    {
        $deadline = $this->contribution_deadline ?? $this->registration_deadline ?? $this->date;

        // Never later than the general registration deadline
        return $this->registration_deadline && $deadline && $deadline->gt($this->registration_deadline)
            ? $this->registration_deadline
            : $deadline;
    }

    /** Active participation (with a talk) can be registered until the contribution deadline. */
    public function isContributionOpen(): bool
    {
        return self::notPassed($this->contributionDeadline());
    }

    private static function notPassed(?Carbon $until): bool
    {
        // compared as calendar dates in the conference's time zone (the deadline day is still open)
        return $until !== null && local_now()->toDateString() <= $until->toDateString();
    }
}
