<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// null user_id = system-level standard collection (e.g. "TOCFL Band 1").
// non-null user_id = user's personal custom collection.
// type: standard · custom
class Collection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'name_zh',
        'type',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wordObjects(): BelongsToMany
    {
        return $this->belongsToMany(WordObject::class, 'collection_word')
            ->withPivot('sort_order', 'mastery_level', 'added_at')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    // Legacy — kept for backward compatibility during transition
    public function wordSenses(): BelongsToMany
    {
        return $this->belongsToMany(WordSense::class, 'collection_sense')
            ->using(CollectionSense::class)
            ->withPivot('sort_order', 'mastery_level', 'added_at')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function collectionSenses(): HasMany
    {
        return $this->hasMany(CollectionSense::class);
    }

    public function scopeSystem($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
