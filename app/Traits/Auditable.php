<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->createAuditLog('created');
        });

        static::updated(function ($model) {
            $model->createAuditLog('updated');
        });

        static::deleted(function ($model) {
            $model->createAuditLog('deleted');
        });
    }

    protected function createAuditLog($event)
    {
        AuditLog::create([
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'user_id' => auth()->id() ?: $this->create_by,
            'event' => $event,
            'data' => json_encode($event === 'updated' ? $this->getChanges() : $this->toArray()),
        ]);
    }
}