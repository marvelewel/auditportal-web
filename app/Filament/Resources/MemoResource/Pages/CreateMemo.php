<?php

namespace App\Filament\Resources\MemoResource\Pages;

use App\Filament\Resources\MemoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMemo extends CreateRecord
{
    protected static string $resource = MemoResource::class;

    // ✅ REVISI: Redirect ke halaman LIST setelah create sukses
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}