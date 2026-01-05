<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

/**
 * ============================================================
 * ACTIVITY LOG RESOURCE - ENTERPRISE AUDIT TRAIL
 * ============================================================
 * 
 * This resource is READ-ONLY by design.
 * All users can view, but no one can edit or delete.
 * 
 * Features:
 * - Comprehensive filtering (date, user, event, entity)
 * - View modal with field-level changes
 * - Export capability (optional)
 */
class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?string $modelLabel = 'Activity Log';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static ?string $slug = 'activity-logs';

    /**
     * ============================================================
     * TABLE CONFIGURATION
     * ============================================================
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ======================================================
                // WHEN - Timestamp
                // ======================================================
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable()
                    ->searchable(false),

                // ======================================================
                // WHO - User Information
                // ======================================================
                Tables\Columns\TextColumn::make('causer_name')
                    ->label('User')
                    ->description(fn(ActivityLog $record): string => $record->causer_role ?? '-')
                    ->searchable()
                    ->sortable(),

                // ======================================================
                // WHAT - Action & Entity
                // ======================================================
                Tables\Columns\TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn(ActivityLog $record): string => $record->getEventColor())
                    ->icon(fn(ActivityLog $record): string => $record->getEventIcon())
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Entity')
                    ->formatStateUsing(fn(ActivityLog $record): string => $record->getSubjectTypeName())
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                // ======================================================
                // ADDITIONAL INFO
                // ======================================================
                Tables\Columns\TextColumn::make('causer_ip')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('Log Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'auth' => 'info',
                        'audit' => 'success',
                        'system' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // ======================================================
                // FILTER: Date Range
                // ======================================================
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari: ' . $data['from'];
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai: ' . $data['until'];
                        }
                        return $indicators;
                    }),

                // ======================================================
                // FILTER: User
                // ======================================================
                SelectFilter::make('causer_id')
                    ->label('User')
                    ->options(fn() => User::pluck('name', 'id')->toArray())
                    ->searchable()
                    ->preload(),

                // ======================================================
                // FILTER: Event Type
                // ======================================================
                SelectFilter::make('event')
                    ->label('Event Type')
                    ->options([
                        ActivityLog::EVENT_CREATE => 'Create',
                        ActivityLog::EVENT_UPDATE => 'Update',
                        ActivityLog::EVENT_DELETE => 'Delete',
                        ActivityLog::EVENT_STATUS_CHANGE => 'Status Change',
                        ActivityLog::EVENT_LOGIN => 'Login',
                        ActivityLog::EVENT_LOGOUT => 'Logout',
                        ActivityLog::EVENT_LOGIN_FAILED => 'Login Failed',
                        ActivityLog::EVENT_UPLOAD => 'Upload',
                    ]),

                // ======================================================
                // FILTER: Entity Type
                // ======================================================
                SelectFilter::make('subject_type')
                    ->label('Entity')
                    ->options([
                        'App\Models\AuditRKO' => 'Audit RKO',
                        'App\Models\Finding' => 'Finding',
                        'App\Models\Followup' => 'Follow-Up',
                        'App\Models\Memo' => 'Memo',
                        'App\Models\User' => 'User',
                    ]),

                // ======================================================
                // FILTER: Log Type
                // ======================================================
                SelectFilter::make('log_name')
                    ->label('Log Type')
                    ->options([
                        ActivityLog::LOG_AUTH => 'Authentication',
                        ActivityLog::LOG_AUDIT => 'Audit Trail',
                        ActivityLog::LOG_SYSTEM => 'System',
                    ]),
            ])
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                // No bulk actions - logs are immutable
            ])
            ->poll('30s'); // Auto-refresh every 30 seconds
    }

    /**
     * ============================================================
     * INFOLIST - VIEW MODAL CONFIGURATION
     * ============================================================
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ======================================================
                // SECTION: Overview
                // ======================================================
                Infolists\Components\Section::make('Informasi Aktivitas')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu (WHEN)')
                                    ->dateTime('d M Y, H:i:s')
                                    ->icon('heroicon-o-clock'),

                                Infolists\Components\TextEntry::make('event')
                                    ->label('Event (WHAT)')
                                    ->badge()
                                    ->color(fn(ActivityLog $record): string => $record->getEventColor()),

                                Infolists\Components\TextEntry::make('log_name')
                                    ->label('Log Type')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'auth' => 'info',
                                        'audit' => 'success',
                                        'system' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                // ======================================================
                // SECTION: WHO - User Information
                // ======================================================
                Infolists\Components\Section::make('User (WHO)')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('causer_name')
                                    ->label('Nama User'),

                                Infolists\Components\TextEntry::make('causer_role')
                                    ->label('Role')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('causer_ip')
                                    ->label('IP Address')
                                    ->icon('heroicon-o-globe-alt'),

                                Infolists\Components\TextEntry::make('causer_agent')
                                    ->label('Browser / Device')
                                    ->limit(80),
                            ]),
                    ]),

                // ======================================================
                // SECTION: WHAT - Entity & Changes
                // ======================================================
                Infolists\Components\Section::make('Entity (WHAT)')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('subject_type')
                                    ->label('Entity Type')
                                    ->formatStateUsing(fn(ActivityLog $record): string => $record->getSubjectTypeName())
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('subject_identifier')
                                    ->label('Entity Name/ID')
                                    ->limit(100),
                            ]),

                        Infolists\Components\TextEntry::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ]),

                // ======================================================
                // SECTION: Changes (Before → After)
                // ======================================================
                Infolists\Components\Section::make('Perubahan Data')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(ActivityLog $record): bool => !empty($record->properties))
                    ->schema([
                        Infolists\Components\ViewEntry::make('properties')
                            ->view('filament.infolists.entries.activity-changes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * ============================================================
     * PAGES CONFIGURATION
     * ============================================================
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
            'view' => Pages\ViewActivityLog::route('/{record}'),
        ];
    }

    /**
     * ============================================================
     * DISABLE CREATE, EDIT, DELETE
     * Audit logs are IMMUTABLE
     * ============================================================
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    /**
     * ============================================================
     * NAVIGATION BADGE (Optional: Show recent count)
     * ============================================================
     */
    public static function getNavigationBadge(): ?string
    {
        // Show count of today's activities
        $count = static::getModel()::whereDate('created_at', today())->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
