<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (pos_id, language_id). Use updateOrCreate() for upserts.
class PosLabelTranslation extends Model
{
    protected $table = 'pos_label_translations';

    public $incrementing = false;

    protected $fillable = [
        'pos_id',
        'language_id',
        'label',
    ];

    public function posLabel(): BelongsTo
    {
        return $this->belongsTo(PosLabel::class, 'pos_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
