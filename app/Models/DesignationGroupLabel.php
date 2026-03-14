<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignationGroupLabel extends Model
{
    protected $fillable = [
        'designation_group_id',
        'language_id',
        'label',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(DesignationGroup::class, 'designation_group_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
