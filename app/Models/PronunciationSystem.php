<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PronunciationSystem extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'language',
        'description',
    ];

    public function wordPronunciations(): HasMany
    {
        return $this->hasMany(WordPronunciation::class);
    }
}
