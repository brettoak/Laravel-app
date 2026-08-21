<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TicketManagement extends Page
{
    protected string $view = 'filament.pages.ticket-management';

    public static function getNavigationGroup(): ?string
    {
        return 'Operations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Tickets';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-ticket';
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

    public function getTitle(): string
    {
        return 'Ticket Management';
    }
}
