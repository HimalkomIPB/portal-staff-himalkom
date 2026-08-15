<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;

        if ($user->is_active && $user->department_id) {
            $roles = $user->roles->pluck('name')->toArray();
            if (in_array('managing director', $roles) || in_array('pjs', $roles)) {
                \App\Models\User::where('department_id', $user->department_id)
                    ->where('id', '!=', $user->id)
                    ->whereHas('roles', function ($query) {
                        $query->whereIn('name', ['managing director', 'pjs']);
                    })
                    ->update(['is_active' => false]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
