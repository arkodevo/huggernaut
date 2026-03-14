<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (attribute_id, language_id). Use updateOrCreate() for upserts.
class AttributeLabel extends Model
{
    protected $table = 'attribute_labels';

    public $incrementing = false;

    protected $fillable = [
        'attribute_id',
        'language_id',
        'label',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
