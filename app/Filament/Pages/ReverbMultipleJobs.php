<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ReverbMultipleJobs extends Page
{
    protected string $view = 'filament.pages.reverb-multiple-jobs';

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Multiple Jobs Monitor';
    }

    public static function getNavigationSort(): ?int
    {
        return 30;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-queue-list';
    }


    public function getTitle(): string
    {
        return 'Job Progress Real-time Monitoring(Multiple Jobs)';
    }


}
