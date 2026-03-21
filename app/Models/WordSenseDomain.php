<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Pivot: word_sense ↔ domain designation (many-to-many).
// is_primary distinguishes the canonical domain from secondary contexts.
class WordSenseDomain extends Pivot
{
    protected $table = 'word_sense_domains';

    public $incrementing = true;

    protected $fillable = [
        'word_sense_id',
        'designation_id',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
