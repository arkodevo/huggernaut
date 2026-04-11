<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrammarPatternGroup extends Model
{
    protected $fillable = ['slug', 'sort_order'];

    public function labels(): HasMany
    {
        return $this->hasMany(GrammarPatternGroupLabel::class);
    }

    public function patterns(): HasMany
    {
        return $this->hasMany(GrammarPattern::class);
    }
}
