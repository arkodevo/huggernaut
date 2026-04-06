<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Pivot: word_sense ↔ domain designation (many-to-many).
// Ordered by sort_order (most relevant first). Max 4 domains per sense.
class WordSenseDomain extends Pivot
{
    protected $table = 'word_sense_domains';

    public $incrementing = true;

    protected $fillable = [
        'word_sense_id',
        'designation_id',
        'sort_order',
    ];
}
