<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        }

        unset($data['roles']);

        $data['name'] = strtolower($data['name']);
        $data['full_name'] = ucwords(strtolower($data['full_name'] ?? ''));

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles(
            $this->data['roles'] ?? []
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}