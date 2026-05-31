<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Keyword extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id', 'keyword', 'device', 'tags', 'is_active',
        'search_volume', 'cpc', 'competition',
    ];

    protected $casts = [
        'tags'          => 'array',
        'is_active'     => 'boolean',
        'search_volume' => 'integer',
        'cpc'           => 'decimal:2',
    ];

    /** المشروع التابع له هذه الكلمة */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** كل نتائج الفحص لهذه الكلمة */
    public function rankChecks(): HasMany
    {
        return $this->hasMany(RankCheck::class);
    }

    /** آخر نتيجة فحص لهذه الكلمة */
    public function latestRankCheck(): HasOne
    {
        return $this->hasOne(RankCheck::class)->latestOfMany('checked_at');
    }

    /**
     * الجهاز الفعلي المستخدم في الفحص:
     * لو الكلمة عندها device صريح يستخدمه، وإلا يرث من المشروع
     */
    public function getEffectiveDeviceAttribute(): string
    {
        return $this->device ?? $this->project->device ?? 'desktop';
    }

    /** label للعرض في الـ UI */
    public function getDeviceLabelAttribute(): string
    {
        return match($this->effective_device) {
            'mobile'  => '📱',
            'desktop' => '🖥️',
            default   => '🖥️',
        };
    }
}
