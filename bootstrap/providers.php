<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\SuperadminPanelProvider;
use App\Providers\ViewServiceProvider;

return [
    AppServiceProvider::class,
    SuperadminPanelProvider::class,
    ViewServiceProvider::class,
];
