<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;

class UploadSpreadSheet extends Page
{
    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Spreadsheet Import';
    }

    public static function getNavigationSort(): ?int
    {
        return 40;
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-up-circle';
    }

    public static function getNavigationBadge(): string
    {
        return "";
    }

    public function getTitle(): string
    {
        return 'Upload Spread Sheet And Show';
    }
    protected string $view = 'filament.pages.upload-spread-sheet';
}
