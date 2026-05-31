<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'description', 'is_public',
    ];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    /**
     * Return the value cast to its declared type.
     */
    public function typedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }
}