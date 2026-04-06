<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Collocations stored as text — segmentation and linking happens at render time.
class WordSenseCollocation extends Model
{
    protected $table = 'word_sense_collocations';

    public $incrementing = false;

    protected $fillable = [
        'word_sense_id',
        'collocation_text',
    ];

    public function sense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class, 'word_sense_id');
    }
}
