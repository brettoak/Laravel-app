<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ReverbSingleJob extends Page
{
    protected string $view = 'filament.pages.reverb-single-job';

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Single Job Monitor';
    }

    public static function getNavigationSort(): ?int
    {
        return 20;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationBadge(): ?string
    {
        return "";
    }

    public static function getNavigationIcon(): string
    {
        return "heroicon-o-play-circle";
    }

    public function getTitle(): string
    {
        return "Job Progress Real-time Monitoring(Single Job)";
    }
}
