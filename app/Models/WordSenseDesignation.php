<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// Multi-select designation pivot for register and dimension attributes.
// All other attributes use direct FKs on word_senses.
class WordSenseDesignation extends Pivot
{
    protected $table = 'word_sense_designations';

    public $incrementing = false;

    protected $fillable = [
        'word_sense_id',
        'designation_id',
    ];
}
