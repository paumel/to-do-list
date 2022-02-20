<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class ToDo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'finished',
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
        'finished' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeCreatedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeExpiredOnDate(Builder $query, CarbonInterface $date): Builder
    {
        return $query->whereDate('due_date', $date)
            ->where('finished', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['tag_id'])) {
            $query->hasTagId($filters['tag_id']);
        }

        if (isset($filters['category_id'])) {
            $query->hasCategoryId($filters['category_id']);
        }

        if (isset($filters['finished'])) {
            $query->where('finished', $filters['finished']);
        }

        if (isset($filters['start_date'])) {
            $query->whereDate('due_date', '>=' , $filters['start_date'])->whereNotNull('due_date');
        }

        if (isset($filters['end_date'])) {
            $query->whereDate('due_date', '<=' , $filters['end_date'])->whereNotNull('due_date');
        }

        return $query;
    }

    public function scopeHasTagId(Builder $query, $tag_id): Builder
    {
        return $query->whereHas('tags', function ($query) use ($tag_id) {
            $query->where('tags.id', $tag_id);
        });
    }

    public function scopeHasCategoryId(Builder $query, $category_id): Builder
    {
        return $query->whereHas('category', function ($query) use ($category_id) {
            $query->where('categories.id', $category_id);
        });
    }

    public function attachTags(array $tags, $userId): void
    {
        $newTags = [];

        foreach ($tags ?? [] as $tag) {
            $tag = Tag::firstOrCreate([
                'name' => $tag,
                'user_id' => $userId,
            ]);
            $newTags[] = $tag->id;
        }

        $this->tags()->sync($newTags);
    }
}
