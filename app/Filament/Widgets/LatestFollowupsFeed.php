<?php

namespace App\Filament\Widgets;

use App\Models\Followup;
use App\Filament\Resources\FindingResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestFollowupsFeed extends TableWidget
{
    protected static ?string $heading = 'Aktivitas Follow-Up Terbaru';
    protected static ?int $sort = 30;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            // ✅ POSISI QUERY HARUS DI SINI (Dalam Method Table)
            ->query(
                Followup::query()
                    ->with(['finding', 'creator'])
                    ->orderByDesc('tanggal_fu')
                    ->limit(8)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_fu')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Oleh')
                    ->badge()
                    ->color('info')
                    ->description(fn ($record) => strtoupper($record->creator?->role ?? '-')),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Aktivitas')
                    ->wrap()
                    ->limit(80),

                Tables\Columns\TextColumn::make('finding.deskripsi_temuan')
                    ->label('Temuan')
                    ->limit(40)
                    ->color('gray'),
            ])
            ->recordUrl(function ($record) {
                // Pastikan finding ada sebelum membuat URL untuk mencegah error null
                if (! $record->finding) {
                    return null;
                }
                
                return FindingResource::getUrl('view', [
                    'record' => $record->finding,
                ]);
            });
    }
}