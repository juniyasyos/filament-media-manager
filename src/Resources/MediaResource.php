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
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\RelationManagers;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Schemas\MediaForm;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource\Tables\MediaTable as MediaTableBuilder;
use juniyasyos\ShieldLite\HasShieldLite;

class MediaResource extends Resource
{
    use HasShieldLite;

    public function defineGates(): array
    {
        return [
            'media.index' => __('Allows viewing media items'),
            'media.create' => __('Allows creating media'),
            'media.update' => __('Allows updating media'),
            'media.delete' => __('Allows deleting media'),
        ];
    }

    protected static bool $shouldRegisterNavigation = false;

    public static function getModel(): string
    {
        return config('filament-media-manager.model.media');
    }

    public static function canAccess(): bool
    {
        return hexa()->can('media.index');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('filament-media-manager::messages.media.title');
    }

    public static function getLabel(): ?string
    {
        return trans('filament-media-manager::messages.media.single');
    }

    public static function getNavigationLabel(): string
    {
        return config('filament-media-manager.navigation.media.label')
            ?? trans('filament-media-manager::messages.media.title');
    }

    public static function getNavigationGroup(): ?string
    {
        $group = config('filament-media-manager.navigation.group');

        // Treat empty string as no group (null) and avoid fallback to translations.
        if (is_string($group) && trim($group) === '') {
            return null;
        }

        return $group; // May be string or null (no group)
    }

    public static function getNavigationIcon(): ?string
    {
        return config('filament-media-manager.navigation.media.icon', 'heroicon-o-photo');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('filament-media-manager.navigation.media.register', false);
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-media-manager.navigation.media.sort')
            ?? config('filament-media-manager.navigation_sort', 0);
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
