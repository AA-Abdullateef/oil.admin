<?php

namespace App\Models;

use App\Support\Roles\RoleManager;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ───────────────────────────────────────────

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class);
    }

    public function earningSchedulesCreated(): HasMany
    {
        return $this->hasMany(EarningSchedule::class, 'created_by');
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class, 'user_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'user_id');
    }

    // ── Role helpers ─────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(RoleManager::SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasAnyRole(RoleManager::ADMIN_ROLES);
    }

    /**
     * Check a permission slug.
     *
     * super_admin bypasses the permission table entirely — they can
     * do everything by definition without needing every row seeded.
     *
     * All other roles must have the permission explicitly assigned
     * via the permission_role pivot.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('slug', $permission))
            ->exists();
    }
}
