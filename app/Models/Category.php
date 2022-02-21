<?php

namespace App\Models;

use App\Traits\HasTags;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    use HasTags;

    protected $fillable = [
        'title',
        'max_to_dos',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toDos(): HasMany
    {
        return $this->hasMany(ToDo::class);
    }

    /**
     * Get the remaining to do count
     *
     * @return Attribute
     */
    protected function remainingToDosCount(): Attribute
    {
        return new Attribute(
            get: fn($value) => $this->max_to_dos - $this->toDos()->count(),
        );
    }

    public function scopeCreatedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (isset($filters['tag_id'])) {
            $query->hasTagId($filters['tag_id']);
        }
        return $query;
    }
}
