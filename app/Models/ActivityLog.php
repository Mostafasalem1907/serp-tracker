<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'model_type', 'model_id',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /** المستخدم الذي قام بالنشاط */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تسجيل نشاط مرتبط بمستخدم
     */
    public static function record(
        string $action,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = [],
        string $notes = ''
    ): void {
        static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => $model ? class_basename($model) : null,
            'model_id'   => $model?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'notes'      => $notes ?: null,
            'created_at' => now(),
        ]);
    }

    /**
     * تسجيل نشاط تلقائي من النظام (Cron) — user_id = null
     */
    public static function recordSystem(string $action, string $notes = ''): void
    {
        static::create([
            'user_id'    => null,
            'action'     => $action,
            'model_type' => null,
            'model_id'   => null,
            'old_values' => null,
            'new_values' => null,
            'ip_address' => null,
            'user_agent' => 'System/Cron',
            'notes'      => $notes ?: null,
            'created_at' => now(),
        ]);
    }
}
