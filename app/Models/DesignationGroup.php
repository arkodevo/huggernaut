<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DesignationGroup extends Model
{
    protected $fillable = [
        'attribute_id',
        'slug',
        'sort_order',
    ];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(DesignationGroupLabel::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class)->orderBy('sort_order');
    }
}
