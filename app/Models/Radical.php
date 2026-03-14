<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Radical extends Model
{
    protected $fillable = [
        'character',
        'stroke_count',
        'meaning_en',
        'meaning_zh',
    ];

    public function wordObjects(): HasMany
    {
        return $this->hasMany(WordObject::class);
    }
}
