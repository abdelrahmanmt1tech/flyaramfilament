<?php
namespace App\Filament\SharedForms;

use Filament\Forms;

class ContactInfoForm
{
    public static function make(): array
    {
        return [
            Forms\Components\TextInput::make('phone')
                ->label('Phone')
                ->tel()
                ->maxLength(20),

            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email(),

            Forms\Components\TextInput::make('address')
                ->label('Address'),

            Forms\Components\TextInput::make('whatsapp')
                ->label('WhatsApp'),

            Forms\Components\TextInput::make('website')
                ->label('Website')
                ->url(),
        ];
    }
}
