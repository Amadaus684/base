<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $permissionGroups = $permissions->groupBy(
            fn (Permission $permission) => Str::before($permission->name, '.')
        );

        $components = [
            TextInput::make('name')
                ->label('Role Name')
                ->required()
                ->maxLength(255),
        ];

        foreach ($permissionGroups as $group => $groupPermissions) {
            $options = $groupPermissions->mapWithKeys(
                fn (Permission $permission) => [
                    $permission->name => Str::after(
                        $permission->name,
                        '.'
                    ),
                ]
            )->toArray();

            $components[] = Section::make(
                Str::headline($group)
            )
                ->schema([
                    CheckboxList::make("permissions_{$group}")
                        ->label('')
                        ->options($options)
                        ->columns(2)
                        ->bulkToggleable()
                        ->dehydrated(false),
                ])
                ->collapsible();
        }

        return $schema
            ->components($components);
    }
}
