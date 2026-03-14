<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosGroup extends Model
{
    protected $fillable = ['slug', 'sort_order'];

    public function labels(): HasMany
    {
        return $this->hasMany(PosGroupLabel::class);
    }

    public function posLabels(): HasMany
    {
        return $this->hasMany(PosLabel::class, 'group_id');
    }
}
