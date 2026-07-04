<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ContentManagement;
use App\Filament\Pages\ReverbMultipleJobs;
use App\Filament\Pages\ReverbSingleJob;
use App\Filament\Pages\UploadSpreadSheet;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkspaceShortcutsWidget extends Widget
{
    protected static ?int $sort = -20;

    protected int | string | array $columnSpan = 1;

    protected string $view = 'filament.widgets.workspace-shortcuts-widget';

    protected function getViewData(): array
    {
        return [
            'today' => now()->format('l, F j, Y'),
            'shortcuts' => [
                [
                    'label' => 'Content Management',
                    'description' => 'Review articles and content records.',
                    'url' => ContentManagement::getUrl(),
                    'icon' => 'heroicon-o-document-text',
                ],
                [
                    'label' => 'Spreadsheet Import',
                    'description' => 'Upload CSV or Excel files for preview.',
                    'url' => UploadSpreadSheet::getUrl(),
                    'icon' => 'heroicon-o-arrow-up-circle',
                ],
                [
                    'label' => 'Single Job Monitor',
                    'description' => 'Run and watch one realtime queue task.',
                    'url' => ReverbSingleJob::getUrl(),
                    'icon' => 'heroicon-o-play-circle',
                ],
                [
                    'label' => 'Multiple Jobs Monitor',
                    'description' => 'Start and compare concurrent tasks.',
                    'url' => ReverbMultipleJobs::getUrl(),
                    'icon' => 'heroicon-o-queue-list',
                ],
            ],
            'queueStatus' => [
                'pending' => $this->countTableRows('jobs'),
                'failed' => $this->countTableRows('failed_jobs'),
                'batches' => $this->countTableRows('job_batches'),
            ],
        ];
    }

    private function countTableRows(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }
}
