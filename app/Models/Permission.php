<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['name', 'slug', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}