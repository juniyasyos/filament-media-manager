<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FolderForm
{
    public static function schema(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Hidden::make('user_id')
                    ->visible(config('filament-media-manager.allow_user_access', false))
                    ->default(Auth::id()),

                Forms\Components\Hidden::make('user_type')
                    ->visible(config('filament-media-manager.allow_user_access', false))
                    ->default(get_class(Auth::user())),

                Forms\Components\TextInput::make('name')
                    ->label(trans('filament-media-manager::messages.folders.columns.name'))
                    ->columnSpanFull()
                    ->lazy()
                    ->afterStateUpdated(function ($set, $get) {
                        $set('collection', Str::slug($get('name')));
                    })
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('collection')
                    ->label(trans('filament-media-manager::messages.folders.columns.collection'))
                    ->columnSpanFull()
                    ->readOnly()
                    ->unique()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(trans('filament-media-manager::messages.folders.columns.description'))
                    ->columnSpanFull()
                    ->maxLength(255),
                Forms\Components\TextInput::make('icon')
                    ->label(trans('filament-media-manager::messages.folders.columns.icon'))
                    ->placeholder('heroicon-o-folder')
                    ->maxLength(255),
                Forms\Components\ColorPicker::make('color')
                    ->label(trans('filament-media-manager::messages.folders.columns.color')),
                Forms\Components\Toggle::make('is_protected')
                    ->label(trans('filament-media-manager::messages.folders.columns.is_protected'))
                    ->live()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('password')
                    ->label(trans('filament-media-manager::messages.folders.columns.password'))
                    ->hidden(fn($get) => !$get('is_protected'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password_confirmation')
                    ->label(trans('filament-media-manager::messages.folders.columns.password_confirmation'))
                    ->hidden(fn($get) => !$get('is_protected'))
                    ->password()
                    ->required()
                    ->revealable()
                    ->same('password')
                    ->maxLength(255)
            ])->columns(2);
    }
}

