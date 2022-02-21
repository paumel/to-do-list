<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeHasTagId(Builder $query, $tag_id): Builder
    {
        return $query->whereHas('tags', function ($query) use ($tag_id) {
            $query->where('tags.id', $tag_id);
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
