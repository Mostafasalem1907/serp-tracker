<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckQueueLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'keyword_id', 'status',
        'error_message', 'attempts', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'created_at'   => 'datetime',
    ];

    /** المشروع المرتبط بعملية الفحص */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** الكلمة المرتبطة بعملية الفحص */
    public function keyword(): BelongsTo
    {
        return $this->belongsTo(Keyword::class);
    }
}
