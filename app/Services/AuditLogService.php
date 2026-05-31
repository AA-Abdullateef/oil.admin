<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(
        string $event,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        array $metadata = []
    ): void {
        $request = request();

        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata ?: null,
            'ip_address' => app()->runningInConsole() ? null : $request->ip(),
            'user_agent' => app()->runningInConsole() ? null : (string) $request->userAgent(),
        ]);
    }
}
