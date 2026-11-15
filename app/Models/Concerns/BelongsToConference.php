<?php

namespace App\Models\Concerns;

use App\Models\Conference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scopes a model to the current conference edition (Conference::current()).
 *
 * All queries (including route model binding and mass updates) only see rows of
 * the current edition, and new rows are assigned to it automatically.
 * Use ->withoutGlobalScope('conference') to query across editions.
 */
trait BelongsToConference
{
    public static function bootBelongsToConference(): void
    {
        static::addGlobalScope('conference', function (Builder $builder) {
            $builder->where($builder->getModel()->qualifyColumn('conference_id'), Conference::current()->id);
        });

        static::creating(function ($model) {
            if (empty($model->conference_id)) {
                $model->conference_id = Conference::current()->id;
            }
        });
    }

    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }
}
