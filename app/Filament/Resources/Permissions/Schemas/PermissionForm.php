<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Permission Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('module.action')
                    ->helperText('Use the format: module.action, for example surat.view or users.create')
                    ->hintIcon(
                        'heroicon-m-information-circle',
                        tooltip: 'Permission names should use lowercase letters and dots. Recommended actions: view, create, update, delete.'
                    )
                    ->rules([
                        'regex:/^[a-z][a-z0-9_]*\.(view|create|update|delete)$/',
                    ])
                    ->validationMessages([
                        'regex' => 'Use the format module.action, for example surat.view, users.create, or surat.delete',
                    ]),

                Select::make('guard_name')
                    ->label('Guard')
                    ->options([
                        'web' => 'web',
                        'api' => 'api',
                    ])
                    ->default('web')
                    ->required()
                    ->native(false)
                    ->helperText('The guard used by your application for authentication and permissions.')
                    ->hintIcon(
                        'heroicon-m-information-circle',
                        tooltip: 'For this application, use web.'
                    ),
            ]);
    }
}