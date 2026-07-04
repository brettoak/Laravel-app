<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WorkspaceStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -30;

    protected ?string $heading = 'Workspace overview';

    protected ?string $description = 'A quick read on content, users, and queue health.';

    protected int | array | null $columns = [
        'md' => 2,
        'xl' => 4,
    ];

    protected function getStats(): array
    {
        return [
            Stat::make('Articles', number_format(Article::count()))
                ->description('Published content records')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->icon('heroicon-o-document-text'),

            Stat::make('Comments', number_format(Comment::count()))
                ->description('Reader conversation volume')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info')
                ->icon('heroicon-o-chat-bubble-left-right'),

            Stat::make('Users', number_format(User::count()))
                ->description('Registered workspace accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Queue', number_format($this->getPendingJobsCount()))
                ->description(number_format($this->getFailedJobsCount()) . ' failed jobs')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($this->getFailedJobsCount() > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-queue-list'),
        ];
    }

    private function getPendingJobsCount(): int
    {
        if (! Schema::hasTable('jobs')) {
            return 0;
        }

        return DB::table('jobs')->count();
    }

    private function getFailedJobsCount(): int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return 0;
        }

        return DB::table('failed_jobs')->count();
    }
}
