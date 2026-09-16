<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $permissions = $this->record
            ->permissions
            ->pluck('name')
            ->toArray();

        foreach ($permissions as $permission) {
            $group = Str::before($permission, '.');

            $data["permissions_{$group}"][] = $permission;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = ucwords(strtolower($data['name']));

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (): bool =>
                    auth()->user()?->can('roles.delete') ?? false
                ),
        ];
    }

    protected function afterSave(): void
    {
        $permissions = [];

        foreach ($this->data as $key => $value) {
            if (str_starts_with($key, 'permissions_') && is_array($value)) {
                $permissions = array_merge($permissions, $value);
            }
        }

        $this->record->syncPermissions($permissions);
    }
}
