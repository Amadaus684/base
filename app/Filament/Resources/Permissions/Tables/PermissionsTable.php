<?php

namespace App\Filament\Resources\Permissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\DeleteAction;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->formatStateUsing(function (string $state): string {
                        return Str::headline(
                            str_replace('.', ' ', $state)
                        );
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge(),

                TextColumn::make('roles_count')
                    ->label('Roles')
                    ->counts('roles')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordUrl(null)
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record): bool =>
                        auth()->user()?->can('permissions.update') ?? false
                    )
                    ->before(function ($action, $record) {
                        if ($record->roles()->exists()) {
                            $roles = $record->roles
                                ->pluck('name')
                                ->implode(', ');

                            \Filament\Notifications\Notification::make()
                                ->title('Permission cannot be updated')
                                ->body("This permission is assigned to: {$roles}")
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
                DeleteAction::make()
                    ->visible(fn ($record): bool =>
                        auth()->user()?->can('permissions.delete') ?? false
                    )
                    ->before(function ($action, $record) {
                        if ($record->roles()->exists()) {
                            $roles = $record->roles
                                ->pluck('name')
                                ->implode(', ');

                            \Filament\Notifications\Notification::make()
                                ->title('Permission cannot be deleted')
                                ->body("This permission is assigned to: {$roles}")
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($action, $records) {
                            $inUse = $records->filter(
                                fn ($permission) => $permission->roles()->exists()
                            );

                            if ($inUse->isNotEmpty()) {
                                $names = $inUse
                                    ->map(fn ($permission) => $permission->name)
                                    ->implode(', ');

                                \Filament\Notifications\Notification::make()
                                    ->title('Permission cannot be deleted')
                                    ->body("The following permissions are still assigned to roles: {$names}")
                                    ->danger()
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}