<?php

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;
use Filament\Pages\Enums\SubNavigationPosition;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 90;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('settings.view') ?? false;
    }

}
