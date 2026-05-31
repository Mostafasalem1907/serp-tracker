<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name_ar', 'name_en', 'location_code',
        'timezone', 'se_domain', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** المشاريع المرتبطة بهذه الدولة */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
