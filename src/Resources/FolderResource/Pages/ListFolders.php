<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource;

class ListFolders extends ManageRecords
{
    protected static string $resource = FolderResource::class;

    public ?Folder $currentParent = null;

    public function mount(): void
    {
        session()->forget(['folder_id', 'folder_password']);

        // Load current parent folder if specified
        $parentId = request()->get('parent_id');
        if ($parentId && $parentId !== 'root') {
            $this->currentParent = Folder::find($parentId);
        }
    }

    public function folderAction(?Folder $item = null)
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(fn(array $arguments) => $this->shouldRequirePassword($arguments['record']))
            ->form(fn(array $arguments) => $this->getPasswordForm($arguments['record']))
            ->action(fn(array $arguments, array $data) => $this->handleFolderAction($arguments['record'], $data))
            ->view('filament-media-manager::pages.folder-action', ['item' => $item]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn() => hexa()->can('folder.create'))
                ->label('New Folder')
                ->icon('heroicon-o-folder-plus')
                ->mutateFormDataUsing(function (array $data) {
                    // Set parent_id if we're in a subfolder
                    if ($this->currentParent && !isset($data['parent_id'])) {
                        $data['parent_id'] = $this->currentParent->id;
                    }
                    return $data;
                })
                ->after(function () {
                    // Refresh the page after creation to show the new folder
                    $this->redirect(request()->fullUrl());
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            url('/') => 'Dashboard',
        ];

        if ($this->currentParent) {
            // Build breadcrumb trail for hierarchy
            $ancestors = $this->currentParent->getAncestors();

            // Add root folders link
            $breadcrumbs[FolderResource::getUrl('index')] = 'Folders';

            // Add each ancestor
            foreach ($ancestors as $ancestor) {
                $breadcrumbs[FolderResource::getUrl('index', ['parent_id' => $ancestor->id])] = $ancestor->name;
            }

            // Add current parent
            $breadcrumbs[] = $this->currentParent->name;
        } else {
            $breadcrumbs[] = 'Folders';
        }

        return $breadcrumbs;
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
        $folder = Folder::find($record['id']);

        // Check if this folder has children - if yes, navigate into it
        if ($folder && $folder->folders()->exists()) {
            return redirect(
                FolderResource::getUrl('index', ['parent_id' => $folder->id])
            );
        }

        // If no children, this is probably a media folder - navigate to media
        $folderName = $record['name'];
        return redirect(
            FolderResource::getUrl('media', ['folderName' => $folderName])
        );
    }
}
