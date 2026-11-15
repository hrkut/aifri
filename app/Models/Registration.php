<?php

namespace App\Models;

use App\Models\Concerns\BelongsToConference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        'review_status',
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
        'reviewed_at' => 'datetime',
    ];

    /** Review of a submitted talk (active participation). */
    public const REVIEW_PENDING = 'pending';
    public const REVIEW_APPROVED = 'approved';
    public const REVIEW_REJECTED = 'rejected';

    public const REVIEW_LABELS = [
        self::REVIEW_PENDING => 'Čaká na posúdenie',
        self::REVIEW_APPROVED => 'Schválený',
        self::REVIEW_REJECTED => 'Zamietnutý',
    ];

    public function isTalk(): bool
    {
        return $this->participation_type === 'presentation';
    }

    /** Review state of a talk; null for passive participants and breaks. */
    public function reviewStatus(): ?string
    {
        return $this->isTalk() ? ($this->review_status ?: self::REVIEW_PENDING) : null;
    }

    public function reviewLabel(): ?string
    {
        $status = $this->reviewStatus();

        return $status ? self::REVIEW_LABELS[$status] : null;
    }

    /** Programme items: breaks and approved talks (pending and rejected talks stay out). */
    public function scopeInProgram(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('participation_type', 'break')
                ->orWhere(function (Builder $q) {
                    $q->where('participation_type', 'presentation')
                        ->where('review_status', self::REVIEW_APPROVED);
                });
        });
    }

    /**
     * Recalculate start times of the whole programme of the current edition from its
     * start time, in programme order (same rule as reordering in the admin).
     */
    public static function recalculateProgramTimes(): void
    {
        $start = trim((string) (Conference::current()->start_time ?: '09:00'));

        try {
            $cursor = Carbon::parse($start);
        } catch (\Throwable) {
            $cursor = Carbon::createFromFormat('H:i', '09:00');
        }

        $items = static::query()
            ->inProgram()
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'duration_minutes']);

        foreach ($items as $index => $item) {
            static::whereKey($item->id)->update([
                'program_order' => $index + 1,
                'time_start' => $cursor->format('H:i'),
            ]);
            $cursor->addMinutes(max(0, (int) $item->duration_minutes));
        }
    }

    /** Approve a talk: it joins the end of the programme. */
    public function approve(): void
    {
        DB::transaction(function () {
            $this->forceFill(['review_status' => self::REVIEW_APPROVED, 'reviewed_at' => now(), 'review_comment' => null]);
            if ($this->program_order === null) {
                $this->program_order = (int) static::query()->inProgram()->max('program_order') + 1;
            }
            $this->save();
            static::recalculateProgramTimes();
        });

        // times are recalculated directly in the database
        $this->refresh();
    }

    /**
     * Reject a talk with the reason for the author: it leaves the programme and the
     * remaining times are recalculated.
     */
    public function reject(string $comment): void
    {
        DB::transaction(function () use ($comment) {
            $this->forceFill([
                'review_status' => self::REVIEW_REJECTED,
                'reviewed_at' => now(),
                'review_comment' => trim($comment),
                'program_order' => null,
                'time_start' => null,
            ])->save();
            static::recalculateProgramTimes();
        });

        $this->refresh();
    }

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
