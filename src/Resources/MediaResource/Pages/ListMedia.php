<?php

namespace Juniyasyos\FilamentMediaManager\Resources\MediaResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Models\Media;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateMediaAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\CreateSubFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\DeleteFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\Actions\EditCurrentFolderAction;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource as FolderResourceFilament;
use Juniyasyos\FilamentMediaManager\Resources\MediaResource;

class ListMedia extends ManageRecords
{
    protected static string $resource = MediaResource::class;

    public ?string $folderName = null;

    public ?int $folder_id = null;

    public ?Folder $folder = null;

    public function mount(): void
    {
        parent::mount();

        $this->folderName = request()->route('folderName');
        $this->loadFolder();
        $this->validateFolderAccess();
        session()->put('folder_id', $this->folder->id);
    }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => 'Dashboard',
            FolderResourceFilament::getUrl('index') => 'folders',
            null => $this->folderName ?? null,
        ];
    }

    protected function loadFolder(): void
    {
        if (! $this->folderName) {
            abort(404, 'Folder name is required');
        }

        $this->folder = Folder::where('name', $this->folderName)->first();
        $this->folder_id = $this->folder->id;

        if (! $this->folder) {
            abort(404, 'Folder not found');
        }
    }

    protected function validateFolderAccess(): void
    {
        if ($this->folder->is_protected && ! session()->has('folder_password')) {
            abort(403, 'Access to this folder is restricted');
        }
    }

    public function getTitle(): string|Htmlable
    {
        return $this->folder->name ?? 'Media';
    }

    protected function getHeaderActions(): array
    {
        $folder = $this->folder;

        $isOwner = config('filament-media-manager.allow_user_access', false)
            && ! empty($folder->user_id)
            && $folder->user_id === Auth::id()
            && $folder->user_type === get_class(Auth::user());

        $isAllowed = $isOwner || ! filament(config('filament-media-manager.allow_user_access', false));

        if (! $isAllowed) {
            return [];
        }

        return [
            CreateMediaAction::make($folder->id)
                ->visible(fn () => hexa()->can('media.create')),
            CreateSubFolderAction::make($folder->id)
                ->visible(fn () => hexa()->can('folder.create')),
            DeleteFolderAction::make($folder->id)
                ->visible(fn () => hexa()->can('folder.delete')),
            EditCurrentFolderAction::make($folder->id)
                ->visible(fn () => hexa()->can('folder.update')),
        ];
    }

    public function folderAction(?Folder $item = null): Actions\Action
    {
        return Actions\Action::make('folderAction')
            ->requiresConfirmation(fn (array $args) => $args['record']['is_protected'] ?? false)
            ->form(fn (array $args) => $this->getPasswordForm($args['record']))
            ->action(fn (array $args, array $data) => $this->handleFolderRedirect($args['record'], $data))
            ->view('filament-media-manager::pages.folder-action', ['item' => $item]);
    }

    protected function getPasswordForm(array $record): ?array
    {
        return $record['is_protected']
            ? [
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255),
            ]
            : null;
    }

    protected function handleFolderRedirect(array $record, array $data)
    {
        if ($record['is_protected'] && ($record['password'] !== ($data['password'] ?? null))) {
            return $this->notifyWrongPassword();
        }

        if ($record['is_protected']) {
            session()->put('folder_password', $data['password']);
        }

        return $this->redirectBasedOnRecord($record);
    }

    protected function notifyWrongPassword()
    {
        Notification::make()
            ->title('Password is incorrect')
            ->danger()
            ->send();

        return null;
    }

    protected function redirectBasedOnRecord(array $record)
    {
        $panel = filament()->getCurrentPanel()->getId();
        $routePrefix = "filament.$panel.resources";

        if (! $record['model_type']) {
            return redirect()->route("$routePrefix.media.index", ['folder_id' => $record['id']]);
        }

        if (! $record['model_id']) {
            $params = ['model_type' => $record['model_type']];
            if (! empty($record['collection'])) {
                $params['collection'] = $record['collection'];
            }

            return redirect()->route("$routePrefix.folders.index", $params);
        }

        return redirect()->route("$routePrefix.media.index", ['folder_id' => $record['id']]);
    }

    public function deleteMedia(): Actions\Action
    {
        return Actions\Action::make('deleteMedia')
            ->label(trans('filament-media-manager::messages.media.meta.delete-media'))
            ->icon('heroicon-s-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn () => hexa()->can('media.delete'))
            ->action(fn (array $arguments) => $this->handleDeleteMedia($arguments['record']['id'] ?? null));
    }

    protected function handleDeleteMedia(?int $id): void
    {
        if (! $id) {
            return;
        }

        $media = Media::find($id);

        if ($media) {
            $media->delete();

            Notification::make()
                ->title(trans('filament-media-manager::messages.media.notifications.delete-folder'))
                ->success()
                ->send();
        }
    }
}
