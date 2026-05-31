<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'user_id', 'country_id', 'state_id',
        'address', 'gender', 'date_of_birth', 'kyc_status',
        'kyc_submitted_at', 'kyc_reviewed_at', 'kyc_reviewed_by',
        'kyc_rejection_reason', 'id_document_front', 'id_document_back',
        'selfie_with_id', 'proof_of_address', 'id_document_type',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth'     => 'date',
            'kyc_submitted_at'  => 'datetime',
            'kyc_reviewed_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function country(): BelongsTo  { return $this->belongsTo(Country::class); }
    public function state(): BelongsTo    { return $this->belongsTo(State::class); }
    public function reviewedBy(): BelongsTo { return $this->belongsTo(User::class, 'kyc_reviewed_by'); }
}
