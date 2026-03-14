<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (icon_theme_id, language_id). Use updateOrCreate() for upserts.
// Used for system themes only; user forks use the theme's own name field.
class IconThemeLabel extends Model
{
    protected $table = 'icon_theme_labels';

    public $incrementing = false;

    protected $fillable = [
        'icon_theme_id',
        'language_id',
        'label',
    ];

    public function iconTheme(): BelongsTo
    {
        return $this->belongsTo(IconTheme::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
