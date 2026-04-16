<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Slugs: formula · usage-note · learner-traps (extensible — seed new rows to add types)
class NoteType extends Model
{
    protected $fillable = [
        'slug',
        'sort_order',
    ];

    public function labels(): HasMany
    {
        return $this->hasMany(NoteTypeLabel::class);
    }

    public function wordSenseNotes(): HasMany
    {
        return $this->hasMany(WordSenseNote::class);
    }
}
