<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getNavigationGroup(): ?string
    {
        return 'Dashboard';
    }

    public static function getNavigationLabel(): string
    {
        return 'Workspace';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }
}
