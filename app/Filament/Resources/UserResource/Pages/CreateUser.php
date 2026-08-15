<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\UserCreatedMail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $randomPassword = Str::random(12);
        $data['password'] = Hash::make($randomPassword);

        Mail::to($data['email'])->send(new UserCreatedMail($data, $randomPassword));

        return $data;
    }

    protected function afterCreate(): void
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
