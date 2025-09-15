<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;

class FolderForm
{
    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Hidden fields for user access
                Hidden::make('user_id')
                    ->visible(config('filament-media-manager.allow_user_access', false))
                    ->default(Auth::id()),

                Hidden::make('user_type')
                    ->visible(config('filament-media-manager.allow_user_access', false))
                    ->default(get_class(Auth::user())),

                // Parent folder selection (only shown when creating subfolder)
                Select::make('parent_id')
                    ->label('Parent Folder')
                    ->placeholder('Root Level (No Parent)')
                    ->searchable()
                    ->options(function () {
                        return Folder::query()
                            ->whereDoesntHave('parent')
                            ->orWhereNull('parent_id')
                            ->pluck('name', 'id');
                    })
                    ->hint('Leave empty to create a root level folder')
                    ->columnSpanFull(),

                // Main input - only folder name required
                TextInput::make('name')
                    ->label('Folder Name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter folder name')
                    ->autofocus(),

                // Auto-generated collection (hidden from user)
                Hidden::make('collection'),

                // Auto-generated icon (hidden from user)
                Hidden::make('icon')
                    ->default('heroicon-o-folder'),

                // Auto-generated color (hidden from user)
                Hidden::make('color')
                    ->default('#3B82F6'), // Blue color

                // Optional fields - collapsible section for advanced options
                Section::make('Advanced Options')
                    ->description('Optional settings that can be configured later')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->placeholder('Optional folder description'),

                        Toggle::make('is_protected')
                            ->label('Password Protected')
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('password')
                            ->label('Password')
                            ->hidden(fn($get) => !$get('is_protected'))
                            ->password()
                            ->revealable()
                            ->required(fn($get) => $get('is_protected'))
                            ->maxLength(255),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->hidden(fn($get) => !$get('is_protected'))
                            ->password()
                            ->required(fn($get) => $get('is_protected'))
                            ->revealable()
                            ->same('password')
                            ->maxLength(255)
                    ])
            ])->columns(1);
    }
}

