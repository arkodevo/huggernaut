<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionTest extends Model
{
    protected $fillable = [
        'user_id',
        'collection_id',
        'test_mode',
        'attribute_slug',
        'total_questions',
        'clean_count',
        'assisted_count',
        'learning_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CollectionTestAnswer::class);
    }
}
