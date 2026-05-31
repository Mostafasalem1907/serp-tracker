<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankCheck extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'keyword_id', 'rank', 'url', 'title', 'source', 'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /** الكلمة المفتاحية التي تخص هذه النتيجة */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
