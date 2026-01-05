<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditRKOResource;
use App\Filament\Resources\FindingResource;
use App\Filament\Resources\MemoResource;
use Filament\Widgets\Widget;

class QuickLinksWidget extends Widget
{
    // Ini mengarah ke resources/views/filament/widgets/quick-links-widget.blade.php
    protected static string $view = 'filament.widgets.quick-links-widget';

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'links' => [
                [
                    'label' => 'Buat Rencana Audit',
                    'url' => AuditRKOResource::getUrl('create'),
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'color' => 'wismilak-green',
                    'desc' => 'Daftarkan jadwal audit baru',
                ],
                [
                    'label' => 'Input Temuan Baru',
                    'url' => FindingResource::getUrl('create'),
                    'icon' => 'heroicon-o-exclamation-triangle',
                    'color' => 'wismilak-green',
                    'desc' => 'Catat ketidaksesuaian audit',
                ],
                [
                    'label' => 'Upload Dokumen',
                    'url' => MemoResource::getUrl('create'),
                    'icon' => 'heroicon-o-arrow-up-tray',
                    'color' => 'wismilak-green',
                    'desc' => 'Arsip memo atau kebijakan',
                ],
            ],
        ];
    }
}