<?php

namespace Juniyasyos\FilamentMediaManager\Resources;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\RelationManagers;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Schemas\FolderForm;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Tables\FoldersTable;
use juniyasyos\ShieldLite\HasShieldLite;

class FolderResource extends Resource
{
    use HasShieldLite;

    public function defineGates(): array
    {
        return [
            'folder.index' => __('Allows viewing folders'),
            'folder.create' => __('Allows creating folders'),
            'folder.update' => __('Allows updating folders'),
            'folder.delete' => __('Allows deleting folders'),
        ];
    }
    protected static bool $isScopedToTenant = false;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-folder';

    protected static string | BackedEnum | null $activeNavigationIcon = 'heroicon-c-folder';

    public static function getModel(): string
    {
        return config('filament-media-manager.model.folder');
    }

    public static function canAccess(): bool
    {
        return hexa()->can('folder.index');
    }

    public static function canCreate(): bool
    {
        return hexa()->can('folder.create');
    }

    public static function canEdit($record): bool
    {
        return hexa()->can('folder.update');
    }

    public static function canDelete($record): bool
    {
        return hexa()->can('folder.delete');
    }

    public static function getNavigationLabel(): string
    {
        return config('filament-media-manager.navigation.folders.label')
            ?? trans('filament-media-manager::messages.folders.title');
    }

    public static function getPluralLabel(): ?string
    {
        if (request()->has('model_type') && !request()->has('collection')) {
            return str(request()->get('model_type'))->afterLast('\\')->title();
        } else if (request()->has('model_type') && request()->has('collection')) {
            return str(request()->get('collection'))->title();
        } else {
            return trans('filament-media-manager::messages.folders.title');
        }
    }

    public static function getLabel(): ?string
    {
        return trans('filament-media-manager::messages.folders.single');
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
        return config('filament-media-manager.navigation.folders.icon', 'heroicon-o-folder');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) config('filament-media-manager.navigation.folders.register', true);
    }

    public static function getNavigationSort(): ?int
    {
        return config('filament-media-manager.navigation.folders.sort')
            ?? config('filament-media-manager.navigation_sort', 0);
    }

    public static function form(Schema $schema): Schema
    {
        return FolderForm::schema($schema);
    }

    public static function table(Table $table): Table
    {
        return FoldersTable::table($table);
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
            'index' => Pages\FoldersManager::route('/'),
            'folders-manager' => Pages\FoldersManager::route('/folders'),
            'media' => \Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages\ListMedia::route('/media-name={folderName}'),
        ];
    }
}
