<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected static function shouldLogActivity(): bool
    {
        return auth()->check()
            && ! auth()->user()->hasRole('Owner');
    }

    protected static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            if (! static::shouldLogActivity()) {
                return;
            }

            $model->writeActivityLog('create');
        });

        static::updated(function (Model $model) {
            if (! static::shouldLogActivity()) {
                return;
            }

            $model->writeActivityLog('update');
        });

        static::deleted(function (Model $model) {
            if (! static::shouldLogActivity()) {
                return;
            }
            
            $model->writeActivityLog('delete');
        });
    }

    protected function writeActivityLog(string $action): void
    {
        if (!Auth::check()) {
            return;
        }

        $oldValues = null;
        $newValues = null;

        if ($action === 'update') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getChanges();
        }

        if ($action === 'delete') {
            $oldValues = $this->getAttributes();
        }

        if ($action === 'create') {
            $newValues = $this->getAttributes();
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->getKey(),
            'description' => $this->getActivityDescription($action),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    protected function getActivityDescription(string $action): string
    {
        $modelName = class_basename($this);

        return match ($action) {
            'create' => "{$modelName} berhasil dibuat",
            'update' => "{$modelName} berhasil diperbarui",
            'delete' => "{$modelName} berhasil dihapus",
            default => "{$modelName} melakukan aktivitas",
        };
    }
}