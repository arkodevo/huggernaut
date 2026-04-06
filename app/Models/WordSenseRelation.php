<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Unified typed relation system. Three-column composite PK:
// (word_sense_id, related_word_text, relation_type_id).
//
// Relations store the target word as text — linking happens at render time
// when the target word exists in the lexicon.
class WordSenseRelation extends Model
{
    protected $table = 'word_sense_relations';

    public $incrementing = false;

    protected $fillable = [
        'word_sense_id',
        'related_word_text',
        'relation_type_id',
        'editorial_note',
    ];

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class, 'word_sense_id');
    }

    public function relationType(): BelongsTo
    {
        return $this->belongsTo(SenseRelationType::class, 'relation_type_id');
    }
}
