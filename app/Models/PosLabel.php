<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function wordSenses(): BelongsToMany
    {
        return $this->belongsToMany(WordSense::class, 'word_sense_pos', 'pos_id', 'word_sense_id')
            ->using(WordSensePos::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }
}
