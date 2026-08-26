<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::log('created', $model, [], $model->toArray());
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            $original = $model->getOriginal();

            $oldValues = [];
            $newValues = [];
            foreach ($dirty as $key => $value) {
                $oldValues[$key] = $original[$key] ?? null;
                $newValues[$key] = $value;
            }

            AuditLog::log('updated', $model, $oldValues, $newValues);
        });

        static::deleted(function ($model) {
            AuditLog::log('deleted', $model, $model->toArray(), []);
        });
    }
}
