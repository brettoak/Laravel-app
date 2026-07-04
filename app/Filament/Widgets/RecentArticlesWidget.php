<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use Filament\Widgets\Widget;

class RecentArticlesWidget extends Widget
{
    protected static ?int $sort = -10;

    protected int | string | array $columnSpan = 1;

    protected string $view = 'filament.widgets.recent-articles-widget';

    protected function getViewData(): array
    {
        return [
            'articles' => Article::query()
                ->with('user')
                ->withCount('comments')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }
}
