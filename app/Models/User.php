<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'timezone', 'client_id', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /** هل المستخدم admin؟ */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** هل المستخدم member؟ */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /** هل المستخدم client؟ */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /** العميل المرتبط بهذا المستخدم (للمستخدمين من نوع client) */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** المشاريع المُسنَدة لهذا العضو */
    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    /** تحليلات الذكاء الاصطناعي التي أجراها هذا المستخدم */
    public function aiAnalyses(): HasMany
    {
        return $this->hasMany(AiAnalysis::class);
    }
}
