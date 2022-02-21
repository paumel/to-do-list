<?php

namespace App\Models;

use App\Traits\HasTags;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToDo extends Model
{
    use HasFactory;
    use HasTags;

    protected $fillable = [
        'title',
        'description',
        'completed',
        'due_date',
        'user_id',
        'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeCreatedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeExpiredOnDate(Builder $query, CarbonInterface $date): Builder
    {
        return $query->whereDate('due_date', $date)
            ->whereTime('due_date', 'like', $date->toTimeString('minute') . ':%')
            ->where('completed', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['tag_id'])) {
            $query->hasTagId($filters['tag_id']);
        }

        if (isset($filters['category_id'])) {
            $query->hasCategoryId($filters['category_id']);
        }

        if (isset($filters['completed'])) {
            $query->where('completed', $filters['completed']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('due_date', '>=', $filters['start_date'])->whereNotNull('due_date');
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('due_date', '<=', $filters['end_date'])->whereNotNull('due_date');
        }

        return $query;
    }

    public function scopeHasCategoryId(Builder $query, $category_id): Builder
    {
        return $query->whereHas('category', function ($query) use ($category_id) {
            $query->where('categories.id', $category_id);
        });
    }

    protected function dueDate(): Attribute
    {
        return new Attribute(
            get: fn ($value) => Carbon::parse($value)->setTimezone('Europe/Vilnius')->toDateTimeString(),
            set: fn ($value) => Carbon::parse($value, 'Europe/Vilnius')->setTimezone('UTC')->toDateTimeString(),
        );
    }
}
