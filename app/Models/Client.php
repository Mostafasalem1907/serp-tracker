<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** المشاريع التابعة لهذا العميل */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** حسابات المستخدمين المرتبطة بهذا العميل */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
