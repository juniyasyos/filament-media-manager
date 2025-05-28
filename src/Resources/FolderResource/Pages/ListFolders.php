<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource;

class ListFolders extends ManageRecords
{
    protected static string $resource = FolderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
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

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => 'Dashboard',
            'folders'
        ];
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
