<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionTestAnswer extends Model
{
    protected $fillable = [
        'collection_test_id',
        'word_sense_id',
        'question_index',
        'correct_value',
        'chosen_value',
        'is_correct',
        'hints_used',
        'score_tier',
        'ai_feedback',
        'time_spent_ms',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'hints_used' => 'array',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(CollectionTest::class, 'collection_test_id');
    }

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class);
    }
}
