<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    /**
     * Contoh:
     * user, role, permission, post, category
     */
    protected static ?string $permissionPrefix = null;

    protected static function hasPermission(string $action): bool
    {
        if (! static::$permissionPrefix) {
            return true;
        }

        return auth()->user()?->can(
            static::$permissionPrefix . '.' . $action
        ) ?? false;
    }

    public static function canViewAny(): bool
    {
        return static::hasPermission('view');
    }

    public static function canView($record): bool
    {
        return static::hasPermission('view');
    }

    public static function canCreate(): bool
    {
        return static::hasPermission('create');
    }

    public static function canEdit($record): bool
    {
        return static::hasPermission('update');
    }

    public static function canDelete($record): bool
    {
        return static::hasPermission('delete');
    }

    public static function canDeleteAny(): bool
    {
        return static::hasPermission('delete');
    }
}