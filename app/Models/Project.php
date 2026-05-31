<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'name', 'domain', 'country_id',
        'language', 'device', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** العميل المالك للمشروع */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** الدولة المستهدفة للمشروع */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** الكلمات المفتاحية الخاصة بهذا المشروع */
    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    /** الأعضاء المُسنَدون لهذا المشروع */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user');
    }

    /** تحليلات الذكاء الاصطناعي لهذا المشروع */
    public function aiAnalyses(): HasMany
    {
        return $this->hasMany(AiAnalysis::class);
    }
}
