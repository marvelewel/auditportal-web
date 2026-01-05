<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends Login
{
    // Mengubah Heading Utama
    public function getHeading(): string | Htmlable
    {
        return 'Internal Audit Portal';
    }

    // Mengubah Sub-heading (Sapaan)
    public function getSubheading(): string | Htmlable | null
    {
        return 'Masuk untuk mengakses kertas kerja audit dan laporan.';
    }

    // Custom Form (Optional: Misal ingin label Email jadi "Corporate Email")
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email') // Label lebih profesional
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}