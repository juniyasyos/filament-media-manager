<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class ListFolders extends ManageRecords
{
    protected static string $resource = FolderResource::class;

    /**
     * Check if subfolder feature is enabled
     */
    protected function isSubfolderEnabled(): bool
    {
        try {
            return filament('filament-media-manager')->allowSubFolders ?? false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Back to parent button when in subfolder view
        if (request()->has('parent_id')) {
            $parentFolder = Folder::find(request()->get('parent_id'));

            $actions[] = Actions\Action::make('back_to_parent')
                ->label(trans('filament-media-manager::messages.folders.actions.back'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(function () use ($parentFolder) {
                    if ($parentFolder && $parentFolder->parent_id) {
                        return FolderResource::getUrl('index', ['parent_id' => $parentFolder->parent_id]);
                    }
                    return FolderResource::getUrl('index');
                });
        }

        $actions[] = Actions\CreateAction::make()
            ->mutateFormDataUsing(function (array $data) {
                if (request()->has('parent_id')) {
                    $data['parent_id'] = request()->get('parent_id');
                }
                return $data;
            });

        return $actions;
    }

    public function mount(): void
    {
        session()->forget(['folder_id', 'folder_password']);
    }

    public function folderAction(?Folder $item = null)
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(fn(array $arguments) => $this->shouldRequirePassword($arguments['record']))
            ->form(fn(array $arguments) => $this->getPasswordForm($arguments['record']))
            ->action(fn(array $arguments, array $data) => $this->handleFolderAction($arguments['record'], $data))
            ->view('filament-media-manager::pages.folder-action', ['item' => $item]);
    }

    public function createSubfolderAction(?Folder $folder = null)
    {
        return Actions\Action::make('createSubfolder')
            ->label(trans('filament-media-manager::messages.folders.actions.create_subfolder'))
            ->icon('heroicon-o-folder-plus')
            ->color('success')
            ->size('sm')
            ->tooltip(trans('filament-media-manager::messages.folders.actions.create_subfolder'))
            ->visible(fn() => $this->isSubfolderEnabled())
            ->form([
                TextInput::make('name')
                    ->label(trans('filament-media-manager::messages.folders.columns.name'))
                    ->columnSpanFull()
                    ->lazy()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('collection', Str::slug($state));
                    })
                    ->required()
                    ->maxLength(255),
                TextInput::make('collection')
                    ->label(trans('filament-media-manager::messages.folders.columns.collection'))
                    ->columnSpanFull()
                    ->readOnly()
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(trans('filament-media-manager::messages.folders.columns.description'))
                    ->columnSpanFull()
                    ->maxLength(255),
                IconPicker::make('icon')
                    ->label(trans('filament-media-manager::messages.folders.columns.icon')),
                ColorPicker::make('color')
                    ->label(trans('filament-media-manager::messages.folders.columns.color')),
                Toggle::make('is_protected')
                    ->label(trans('filament-media-manager::messages.folders.columns.is_protected'))
                    ->live()
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->label(trans('filament-media-manager::messages.folders.columns.password'))
                    ->hidden(fn($get) => !$get('is_protected'))
                    ->password()
                    ->revealable()
                    ->required(fn($get) => $get('is_protected'))
                    ->maxLength(255),
                TextInput::make('password_confirmation')
                    ->label(trans('filament-media-manager::messages.folders.columns.password_confirmation'))
                    ->hidden(fn($get) => !$get('is_protected'))
                    ->password()
                    ->revealable()
                    ->same('password')
                    ->required(fn($get) => $get('is_protected'))
                    ->maxLength(255),
            ])
            ->action(function (array $data, array $arguments) {
                $parentFolder = $arguments['folder'];

                $data['parent_id'] = $parentFolder->id;
                $data['user_id'] = auth()->user()->id ?? null;
                $data['user_type'] = auth()->user() ? get_class(auth()->user()) : null;

                // Inherit model context from parent folder if exists
                if ($parentFolder->model_type) {
                    $data['model_type'] = $parentFolder->model_type;
                    $data['model_id'] = $parentFolder->model_id;
                }

                Folder::create($data);

                Notification::make()
                    ->title(trans('filament-media-manager::messages.folders.notifications.subfolder_created'))
                    ->success()
                    ->send();
            })
            ->modalWidth('md')
            ->button();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            url('/') => 'Dashboard',
        ];

        // Build nested folder breadcrumb trail
        if (request()->has('parent_id')) {
            $parentId = request()->get('parent_id');
            $parents = $this->getParentTrail($parentId);

            foreach ($parents as $parent) {
                $breadcrumbs[FolderResource::getUrl('index', ['parent_id' => $parent->id])] = $parent->name;
            }
        } else {
            $breadcrumbs[FolderResource::getUrl('index')] = trans('filament-media-manager::messages.folders.title');
        }

        return $breadcrumbs;
    }

    /**
     * Get parent folder trail for breadcrumbs
     */
    protected function getParentTrail($parentId): array
    {
        $trail = [];
        $folder = Folder::find($parentId);

        while ($folder) {
            array_unshift($trail, $folder);
            $folder = $folder->parent;
        }

        return $trail;
    }

    protected function shouldRequirePassword(array $record): bool
    {
        return $record['is_protected'] ?? false;
    }

    protected function getPasswordForm(array $record): ?array
    {
        if ($this->shouldRequirePassword($record)) {
            return [
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255),
            ];
        }
        return null;
    }

    protected function handleFolderAction(array $record, array $data)
    {
        if ($this->shouldRequirePassword($record)) {
            if (!isset($data['password']) || $data['password'] !== $record['password']) {
                Notification::make()
                    ->title('Password is incorrect')
                    ->danger()
                    ->send();

                return;
            }
            session()->put('folder_password', $data['password']);
        }

        return $this->redirectToCorrectLocation($record);
    }

    protected function redirectToCorrectLocation(array $record)
    {
        $folderName = $record['name'];
        $folderId = $record['id'];

        // Check if this folder has any subfolders
        $hasSubfolders = Folder::where('parent_id', $folderId)->exists();

        // If nested folders are enabled OR folder has subfolders, navigate into it
        if ($this->isSubfolderEnabled() || $hasSubfolders) {
            return redirect(
                FolderResource::getUrl('index', [
                    'parent_id' => $folderId,
                    'parent_name' => $folderName,
                ])
            );
        }

        // Media root (jika folder tidak berasosiasi ke model)
        if (!$record['model_type']) {
            return redirect(
                FolderResource::getUrl('media', ['folderName' => $folderName])
            );
        }

        // Folder level berdasarkan model_type saja
        if (!$record['model_id'] && !$record['collection']) {
            return redirect(
                FolderResource::getUrl('index', [
                    'model_type' => $record['model_type'],
                ])
            );
        }

        // Folder level berdasarkan model_type dan collection
        if (!$record['model_id']) {
            return redirect(
                FolderResource::getUrl('index', [
                    'model_type' => $record['model_type'],
                    'collection' => $record['collection'],
                ])
            );
        }

        // Media folder spesifik
        return redirect(
            FolderResource::getUrl('media', ['folderName' => $folderName])
        );
    }
}
