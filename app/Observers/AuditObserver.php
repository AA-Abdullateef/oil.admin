<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model, null, $this->cleanAttributes($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $changes = $this->cleanAttributes($model->getChanges());

        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (array_keys($changes) as $key) {
            $oldValues[$key] = $model->getOriginal($key);
        }

        $this->log('updated', $model, $oldValues, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model, $this->cleanAttributes($model->getOriginal()), null);
    }

    public function restored(Model $model): void
    {
        $this->log('restored', $model, null, $this->cleanAttributes($model->getAttributes()));
    }

    private function log(string $event, Model $model, ?array $oldValues, ?array $newValues): void
    {
        if ($model instanceof AuditLog) {
            return;
        }

        app(AuditLogService::class)->log($event, $model, $oldValues, $newValues);
    }

    private function cleanAttributes(array $attributes): array
    {
        unset($attributes['password'], $attributes['remember_token']);

        return $attributes;
    }
}
