<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (designation_id, icon_theme_id). One icon per designation per theme.
// Use updateOrCreate() for upserts.
class DesignationIcon extends Model
{
    protected $table = 'designation_icons';

    public $incrementing = false;

    protected $fillable = [
        'designation_id',
        'icon_theme_id',
        'icon_value',
        'icon_alt',
    ];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function iconTheme(): BelongsTo
    {
        return $this->belongsTo(IconTheme::class);
    }
}
