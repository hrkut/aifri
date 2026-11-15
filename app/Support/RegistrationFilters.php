<?php

namespace App\Support;

use App\Models\Registration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The admin registration filters (search, participation type and form, review state).
 * Shared by the dashboard table, the Excel export and the attendance list, so all
 * three always show the same people.
 */
class RegistrationFilters
{
    public string $q;

    public string $participationType;

    public string $onlineParticipation;

    public string $review;

    public function __construct(Request $request)
    {
        $this->q = trim((string) $request->query('q', ''));
        $this->participationType = (string) $request->query('participation_type', '');
        $this->onlineParticipation = (string) $request->query('online_participation', '');

        // Backward compatibility for old checkbox URLs
        if ($this->participationType === '' && $request->boolean('only_active')) {
            $this->participationType = 'active';
        }
        if ($this->onlineParticipation === '' && $request->boolean('only_in_person')) {
            $this->onlineParticipation = 'in_person';
        }

        $review = (string) $request->query('review', '');
        $this->review = array_key_exists($review, Registration::REVIEW_LABELS) ? $review : '';
    }

    /** @return Builder<Registration> */
    public function query(): Builder
    {
        // breaks are programme items, not participants
        $query = Registration::query()->where('participation_type', '!=', 'break');

        if ($this->q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $this->q) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('institution', 'like', $like);
            });
        }

        if ($this->participationType === 'active') {
            $query->where('participation_type', 'presentation');
        } elseif ($this->participationType === 'passive') {
            $query->where('participation_type', 'passive');
        }

        if ($this->onlineParticipation === 'online') {
            $query->where('online_participation', true);
        } elseif ($this->onlineParticipation === 'in_person') {
            $query->where('online_participation', false);
        }

        // Review state of talks (implies active participation)
        if ($this->review !== '') {
            $query->where('participation_type', 'presentation');
            $this->review === Registration::REVIEW_PENDING
                ? $query->where(fn ($q) => $q->whereNull('review_status')->orWhere('review_status', Registration::REVIEW_PENDING))
                : $query->where('review_status', $this->review);
        }

        return $query;
    }

    /** Dashboard/export sorting: [column, direction], invalid values fall back to newest first. */
    public static function sort(Request $request): array
    {
        $sortable = ['name', 'email', 'participation_type', 'online_participation', 'institution', 'created_at'];

        $sort = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        return [
            in_array($sort, $sortable, true) ? $sort : 'created_at',
            in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc',
        ];
    }
}
