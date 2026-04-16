<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (note_type_id, language_id). Use updateOrCreate() for upserts.
class NoteTypeLabel extends Model
{
    protected $table = 'note_type_labels';

    public $incrementing = false;

    protected $fillable = [
        'note_type_id',
        'language_id',
        'label',
    ];

    public function noteType(): BelongsTo
    {
        return $this->belongsTo(NoteType::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
