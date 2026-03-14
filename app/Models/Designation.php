<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    protected $fillable = [
        'attribute_id',
        'designation_group_id',
        'slug',
        'sort_order',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(DesignationGroup::class, 'designation_group_id');
    }

    public function labels(): HasMany
    {
        return $this->hasMany(DesignationLabel::class);
    }

    public function icons(): HasMany
    {
        return $this->hasMany(DesignationIcon::class);
    }

    // Word senses where this designation is set as a single-select spectrum FK.
    // Six FKs on word_senses point here: channel, connotation, semantic_mode,
    // sensitivity, domain, tocfl_level, hsk_level.
    // Use named scopes on WordSense to query by designation role.

    // Multi-select pivot: register, dimension
    public function wordSenses(): BelongsToMany
    {
        return $this->belongsToMany(WordSense::class, 'word_sense_designations')
            ->using(WordSenseDesignation::class)
            ->withTimestamps();
    }
}
