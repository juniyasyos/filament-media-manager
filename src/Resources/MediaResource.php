<?php

namespace Juniyasyos\FilamentMediaManager\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Juniyasyos\FilamentMediaManager\Models\Media;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\RelationManagers;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Schemas\MediaForm;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Tables\MediaTable as MediaTableBuilder;

class MediaResource extends Resource implements HasShieldPermissions
{
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
        ];
    }

    protected static bool $shouldRegisterNavigation = false;

    public static function getModel(): string
    {
        return config('filament-media-manager.model.media');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('filament-media-manager::messages.media.title');
    }

    public static function getLabel(): ?string
    {
        return trans('filament-media-manager::messages.media.single');
    }

    public static function form(Schema $schema): Schema
    {
        return MediaForm::schema($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaTableBuilder::table($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/')
        ];
    }
}
