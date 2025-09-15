<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
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
}
