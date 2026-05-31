<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Method extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name'];

    public function subMethods(): HasMany
    {
        return $this->hasMany(SubMethod::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
