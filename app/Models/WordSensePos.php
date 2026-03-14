<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Filter-index pivot: derived from word_sense_definitions, kept for query performance.
// is_primary marks the dominant POS for a sense (e.g. a primarily-noun sense).
class WordSensePos extends Pivot
{
    protected $table = 'word_sense_pos';

    public $incrementing = false;

    protected $fillable = [
        'word_sense_id',
        'pos_id',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
