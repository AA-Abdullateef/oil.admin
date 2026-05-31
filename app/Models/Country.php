<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['name', 'slug'];

    public function states(): HasMany  { return $this->hasMany(State::class); }
    public function profiles(): HasMany { return $this->hasMany(Profile::class); }
}