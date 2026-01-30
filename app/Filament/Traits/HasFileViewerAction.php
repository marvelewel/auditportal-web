<?php

namespace App\Filament\Traits;

use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;

trait HasFileViewerAction
{
    /**
     * Create a reusable file viewer action for tables.
     * 
     * @param string $column The column name that contains the file path
     * @param string $label The label for the action button
     * @param string $disk The storage disk to use
     * @return Action
     */
    public static function viewFileAction(
        string $column = 'file_dokumen',
        string $label = 'Lihat File',
        string $disk = 'public'
    ): Action {
        return Action::make('view_file')
            ->label($label)
            ->icon('heroicon-m-eye')
            ->color('warning')
            ->action(function ($record, $livewire) use ($column, $disk) {
                $filePath = $record->{$column};

                if (!empty($filePath)) {
                    $livewire->dispatch('openFileViewer', path: $filePath, disk: $disk);
                }
            })
            ->visible(fn($record) => !empty($record->{$column}))
            ->tooltip('Lihat file dalam popup');
    }

    /**
     * Create a file viewer action for infolist entries.
     * 
     * @param string $url The file URL
     * @param string $label The label for the action button
     * @param string $disk The storage disk to use
     * @return Action
     */
    public static function infolistFileAction(
        string $label = 'Lihat File',
        string $disk = 'public'
    ): Action {
        return Action::make('view_file_infolist')
            ->label($label)
            ->icon('heroicon-m-eye')
            ->color('warning')
            ->tooltip('Lihat file dalam popup');
    }
}
