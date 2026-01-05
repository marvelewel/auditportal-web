<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * ============================================================
 * RECENT ACTIVITY WIDGET
 * ============================================================
 * 
 * Displays the most recent activities on the dashboard.
 * Quick overview of WHO did WHAT and WHEN.
 */
class RecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Terbaru';

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    /**
     * Limit the number of records shown
     */
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return ActivityLog::query()
            ->orderBy('created_at', 'desc')
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('Waktu')
                ->dateTime('d M Y, H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('causer_name')
                ->label('User')
                ->description(fn(ActivityLog $record): string => $record->causer_role ?? '-'),

            Tables\Columns\TextColumn::make('event')
                ->label('Event')
                ->badge()
                ->color(fn(ActivityLog $record): string => $record->getEventColor())
                ->icon(fn(ActivityLog $record): string => $record->getEventIcon()),

            Tables\Columns\TextColumn::make('description')
                ->label('Deskripsi')
                ->limit(50)
                ->wrap(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label('Lihat')
                ->icon('heroicon-o-eye')
                ->url(fn(ActivityLog $record): string => route('filament.portal.resources.activity-logs.view', $record))
                ->openUrlInNewTab(false),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    /**
     * Only show if there are activities
     */
    public static function canView(): bool
    {
        return ActivityLog::exists();
    }
}
