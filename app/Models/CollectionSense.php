<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Pivot for collection_sense — carries mastery level and sort order.
// mastery_level: 0-5 (0 = unseen, 5 = mastered).
// added_at: timestamp set on insert.
class CollectionSense extends Pivot
{
    protected $table = 'collection_sense';

    public $incrementing = false;

    protected $fillable = [
        'collection_id',
        'word_sense_id',
        'sort_order',
        'mastery_level',
        'added_at',
    ];

    protected function casts(): array
    {
        return [
            'added_at' => 'datetime',
        ];
    }
}
