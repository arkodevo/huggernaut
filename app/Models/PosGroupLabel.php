<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosGroupLabel extends Model
{
    protected $fillable = ['pos_group_id', 'language_id', 'label'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PosGroup::class, 'pos_group_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
