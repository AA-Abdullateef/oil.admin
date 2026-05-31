<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = ['country_id', 'name', 'slug'];

    public function country(): BelongsTo  { return $this->belongsTo(Country::class); }
    public function profiles(): HasMany   { return $this->hasMany(Profile::class); }
}