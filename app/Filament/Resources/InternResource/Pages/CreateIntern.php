<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Filament\Resources\InternResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIntern extends CreateRecord
{
    protected static string $resource = InternResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
