<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource;
use Livewire\Attributes\Computed;

class FoldersManager extends Page
{
    protected static string $resource = FolderResource::class;

    public ?Folder $currentParent = null;
    public string $viewMode = 'grid';
    public array $selectedItems = [];

    public function getView(): string
    {
        return 'filament-media-manager::pages.folders-manager';
    }

    public function mount(): void
    {
        $this->viewMode = session('folder_view_mode', 'grid');

        // Load current parent folder if specified
        $parentId = request()->get('parent_id');
        if ($parentId && $parentId !== 'root') {
            $this->currentParent = Folder::find($parentId);
        }
    }

    #[Computed]
    public function getRecordsProperty()
    {
        $query = Folder::query();

        // Handle hierarchy navigation
        $parentId = request()->get('parent_id');

        if ($parentId !== null) {
            // Show children of specific parent
            $query->where('parent_id', $parentId === 'root' ? null : $parentId);
        } else {
            // Show root level folders (parent_id is null)
            $query->where('parent_id', null)
                ->where(function ($query) {
                    $query->where('model_id', null)
                        ->where('collection', null)
                        ->orWhere('model_type', null);
                });
        }

        return $query->get();
    }

    public function setViewMode($mode)
    {
        $this->viewMode = $mode;
        session(['folder_view_mode' => $mode]);
        $this->selectedItems = [];
    }

    public function toggleSelection($folderId)
    {
        if (in_array($folderId, $this->selectedItems)) {
            $this->selectedItems = array_filter($this->selectedItems, fn($id) => $id !== $folderId);
        } else {
            $this->selectedItems[] = $folderId;
        }
    }

    public function selectAll()
    {
        $allFolderIds = $this->getRecordsProperty()->pluck('id')->map(fn($id) => (string)$id)->toArray();

        if (count($this->selectedItems) === count($allFolderIds)) {
            $this->selectedItems = [];
        } else {
            $this->selectedItems = $allFolderIds;
        }
    }

    public function clearSelection()
    {
        $this->selectedItems = [];
    }

    public function bulkDelete()
    {
        if (empty($this->selectedItems)) return;

        $folders = Folder::whereIn('id', $this->selectedItems)->get();

        foreach ($folders as $folder) {
            $folder->delete();
        }

        Notification::make()
            ->title('Folders deleted successfully')
            ->success()
            ->send();

        $this->selectedItems = [];
    }

    public function bulkMove()
    {
        if (empty($this->selectedItems)) return;

        // TODO: Implement bulk move logic
        Notification::make()
            ->title(count($this->selectedItems) . ' folders would be moved')
            ->info()
            ->send();
    }

    public function deleteFolder($folderId)
    {
        $folder = Folder::find($folderId);
        if ($folder) {
            $folder->delete();
            Notification::make()
                ->title('Folder deleted successfully')
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Folder')
                ->icon('heroicon-o-folder-plus')
                ->visible(false)
                ->form([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Textarea::make('description')
                        ->maxLength(500),
                    Forms\Components\ColorPicker::make('color')
                        ->default('#3B82F6'),
                    Forms\Components\Toggle::make('is_protected')
                        ->default(false),
                ])
                ->mutateFormDataUsing(function (array $data) {
                    if ($this->currentParent && !isset($data['parent_id'])) {
                        $data['parent_id'] = $this->currentParent->id;
                    }
                    return $data;
                }),
        ];
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            url('/') => 'Dashboard',
        ];

        if ($this->currentParent) {
            // Add root folders link
            $breadcrumbs[static::getResource()::getUrl('folders-manager')] = 'Folders';

            // Add each ancestor
            foreach ($this->currentParent->getAncestors() as $ancestor) {
                $breadcrumbs[static::getResource()::getUrl('folders-manager', ['parent_id' => $ancestor->id])] = $ancestor->name;
            }

            // Add current parent
            $breadcrumbs[] = $this->currentParent->name;
        } else {
            $breadcrumbs[] = 'Folders';
        }

        return $breadcrumbs;
    }
}
