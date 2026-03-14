<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (relation_type_id, language_id). Use updateOrCreate() for upserts.
class SenseRelationTypeLabel extends Model
{
    protected $table = 'sense_relation_type_labels';

    public $incrementing = false;

    protected $fillable = [
        'relation_type_id',
        'language_id',
        'label',
    ];

    public function relationType(): BelongsTo
    {
        return $this->belongsTo(SenseRelationType::class, 'relation_type_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
