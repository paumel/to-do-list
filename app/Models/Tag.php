<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'taggable');
    }

    public function toDos(): MorphToMany
    {
        return $this->morphedByMany(ToDo::class, 'taggable');
    }

    public function scopeCreatedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
