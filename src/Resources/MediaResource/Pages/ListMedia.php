<?php

namespace Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages;

use Filament\Actions;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Resources\Pages\ManageRecords;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Models\Media;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateMediaAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateSubFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\DeleteFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\EditCurrentFolderAction;

class ListMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    public ?int $folder_id = null;
    public ?Folder $folder = null;

    public function mount(): void
    {
        parent::mount();

        $this->folder_id = request()->get('folder_id');

        if (!$this->folder_id) {
            abort(404, 'Folder ID is required');
        }

        $this->folder = Folder::find($this->folder_id);

        if (!$this->folder) {
            abort(404, 'Folder not found');
        }

        if ($this->folder->is_protected && !session()->has('folder_password')) {
            abort(403, 'Access to this folder is restricted');
        }

        session()->put('folder_id', $this->folder_id);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->folder->name ?? 'Media';
    }

    protected function getHeaderActions(): array
    {
        $folder = config('filament-media-manager.model.folder')::find($this->folder_id);

        $isOwner = filament(config('filament-media-manager.allow_user_access', false))
            && !empty($folder->user_id)
            && $folder->user_id === Auth::user()->id()
            && $folder->user_type === get_class(Auth::user());

        return $isOwner || !filament(config('filament-media-manager.allow_user_access', false))
            ? [
                CreateMediaAction::make($this->folder_id),
                CreateSubFolderAction::make($this->folder_id),
                DeleteFolderAction::make($this->folder_id),
                EditCurrentFolderAction::make($this->folder_id),
            ]
            : [];
    }

    public function folderAction(?Folder $item = null): Actions\Action
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(fn(array $args) => $args['record']['is_protected'] ?? false)
            ->form(fn(array $args) => $this->getPasswordForm($args['record']))
            ->action(fn(array $args, array $data) => $this->handleFolderRedirect($args['record'], $data))
            ->view('filament-media-manager::pages.folder-action', ['item' => $item]);
    }


    protected function getPasswordForm(array $record): ?array
    {
        return $record['is_protected'] ? [
            TextInput::make('password')
                ->password()
                ->revealable()
                ->required()
                ->maxLength(255),
        ] : null;
    }

    protected function handleFolderRedirect(array $record, array $data)
    {
        if ($record['is_protected'] && ($record['password'] !== ($data['password'] ?? null))) {
            Notification::make()
                ->title('Password is incorrect')
                ->danger()
                ->send();
            return;
        }

        if ($record['is_protected']) {
            session()->put('folder_password', $data['password']);
        }

        $panel = filament()->getCurrentPanel()->getId();
        $tenant = filament()->getTenant()?->id;
        $params = ['folder_id' => $record['id']];
        $routePrefix = "filament.$panel.resources";

        if (!$record['model_type']) {
            return redirect()->route("$routePrefix.media.index", $params);
        }

        if (!$record['model_id']) {
            $folderParams = ['model_type' => $record['model_type']];
            if (!empty($record['collection'])) {
                $folderParams['collection'] = $record['collection'];
            }
            return redirect()->route("$routePrefix.folders.index", $folderParams);
        }

        return redirect()->route("$routePrefix.media.index", $params);
    }

    public function deleteMedia(): Actions\Action
    {
        return Actions\Action::make('deleteMedia')
            ->label(trans('filament-media-manager::messages.media.meta.delete-media'))
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $media = Media::find($arguments['record']['id']);

                if ($media) {
                    $media->delete();
                    Notification::make()
                        ->title(trans('filament-media-manager::messages.media.notifications.delete-folder'))
                        ->success()
                        ->send();
                }
            });
    }
}
