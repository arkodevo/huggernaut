<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Composite PK: (designation_id, language_id). Use updateOrCreate() for upserts.
class DesignationLabel extends Model
{
    protected $table = 'designation_labels';

    public $incrementing = false;

    protected $fillable = [
        'designation_id',
        'language_id',
        'label',
    ];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
