<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysis extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'user_id', 'prompt_used',
        'response', 'tokens_input', 'tokens_output',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** المشروع الذي أُجري له التحليل */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** المستخدم الذي طلب التحليل */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** إجمالي الـ tokens المُستهلكة */
    public function getTotalTokensAttribute(): int
    {
        return $this->tokens_input + $this->tokens_output;
    }
}
