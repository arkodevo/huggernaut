<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Unified typed relation system. Three-column composite PK:
// (word_sense_id, related_sense_id, relation_type_id).
//
// Relation type slugs: synonym · antonym · lexical_cluster · derivative
//                      see_also · family_member · compound
//
// family_member: POS of related_sense carries noun/adj/adv detail —
// no redundant type per form needed.
class WordSenseRelation extends Model
{
    protected $table = 'word_sense_relations';

    public $incrementing = false;

    protected $fillable = [
        'word_sense_id',
        'related_sense_id',
        'relation_type_id',
        'editorial_note',
    ];

    public function wordSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class, 'word_sense_id');
    }

    public function relatedSense(): BelongsTo
    {
        return $this->belongsTo(WordSense::class, 'related_sense_id');
    }

    public function relationType(): BelongsTo
    {
        return $this->belongsTo(SenseRelationType::class, 'relation_type_id');
    }
}
