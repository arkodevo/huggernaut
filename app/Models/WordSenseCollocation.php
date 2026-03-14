<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Sense-level collocations resolve to a word_object (not a sense), because
// collocational partners are typically identified orthographically.
class WordSenseCollocation extends Pivot
{
    protected $table = 'word_sense_collocations';

    public $incrementing = false;

    protected $fillable = [
        'word_sense_id',
        'collocation_word_object_id',
    ];
}
