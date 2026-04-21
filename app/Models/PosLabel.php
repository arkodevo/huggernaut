<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosLabel extends Model
{
    protected $fillable = [
        'slug',
        'parent_id',
        'group_id',
        'sort_order',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PosGroup::class, 'group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PosLabel::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PosLabel::class, 'parent_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PosLabelTranslation::class, 'pos_id');
    }

    public function wordSenseDefinitions(): HasMany
    {
        return $this->hasMany(WordSenseDefinition::class, 'pos_id');
    }

    // wordSenses() relation retired 2026-04-21 — the word_sense_pos pivot
    // it depended on was dropped. To enumerate senses by POS, query
    // word_sense_definitions.pos_id and group by word_sense_id.
}
