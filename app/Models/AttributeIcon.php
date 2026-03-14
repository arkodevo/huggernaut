<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (attribute_id, icon_theme_id). Group/header icon per attribute per theme.
// Use updateOrCreate() for upserts.
class AttributeIcon extends Model
{
    protected $table = 'attribute_icons';

    public $incrementing = false;

    protected $fillable = [
        'attribute_id',
        'icon_theme_id',
        'icon_value',
        'icon_alt',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function iconTheme(): BelongsTo
    {
        return $this->belongsTo(IconTheme::class);
    }
}
