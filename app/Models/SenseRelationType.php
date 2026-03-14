<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Slugs: synonym · antonym · lexical_cluster · derivative · see_also · family_member · compound
class SenseRelationType extends Model
{
    protected $fillable = [
        'slug',
        'sort_order',
    ];

    public function labels(): HasMany
    {
        return $this->hasMany(SenseRelationTypeLabel::class, 'relation_type_id');
    }

    public function wordSenseRelations(): HasMany
    {
        return $this->hasMany(WordSenseRelation::class, 'relation_type_id');
    }
}
