<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Juniyasyos\FilamentMediaManager\Models\Folder;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource;
use TomatoPHP\FilamentIcons\Components\IconPicker;

class ViewFolder extends Page
{
    protected static string $resource = FolderResource::class;

    protected static string $view = 'filament-media-manager::pages.view-folder';

    public ?Folder $folder = null;

    public function mount(Folder $folder): void
    {
        $this->folder = $folder->load(['folders', 'media', 'parent']);
    }

    public function getTitle(): string
    {
        return $this->folder->name ?? 'Folder';
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Back button
        if ($this->folder->parent) {
            $actions[] = Actions\Action::make('back')
                ->label(trans('filament-media-manager::messages.folders.actions.back'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FolderResource::getUrl('view', ['folder' => $this->folder->parent]));
        } else {
            $actions[] = Actions\Action::make('back_to_root')
                ->label('Back to Root')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FolderResource::getUrl('index'));
        }

        // Create subfolder action
        $actions[] = Actions\Action::make('create_subfolder')
            ->label(trans('filament-media-manager::messages.folders.actions.create_subfolder'))
            ->icon('heroicon-o-folder-plus')
            ->color('success')
            ->form([
                Forms\Components\TextInput::make('name')
                    ->label(trans('filament-media-manager::messages.folders.columns.name'))
                    ->required()
                    ->lazy()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('collection', Str::slug($state));
                    })
                    ->maxLength(255),
                Forms\Components\TextInput::make('collection')
                    ->label(trans('filament-media-manager::messages.folders.columns.collection'))
                    ->required()
                    ->readOnly()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(trans('filament-media-manager::messages.folders.columns.description'))
                    ->maxLength(255),
                IconPicker::make('icon')
                    ->label(trans('filament-media-manager::messages.folders.columns.icon')),
                Forms\Components\ColorPicker::make('color')
                    ->label(trans('filament-media-manager::messages.folders.columns.color')),
                Forms\Components\Toggle::make('is_protected')
                    ->label(trans('filament-media-manager::messages.folders.columns.is_protected'))
                    ->live(),
                Forms\Components\TextInput::make('password')
                    ->label(trans('filament-media-manager::messages.folders.columns.password'))
                    ->password()
                    ->revealable()
                    ->visible(fn(Forms\Get $get) => $get('is_protected'))
                    ->required(fn(Forms\Get $get) => $get('is_protected'))
                    ->maxLength(255),
            ])
            ->action(function (array $data) {
                $data['parent_id'] = $this->folder->id;
                $data['user_id'] = auth()->id();
                $data['user_type'] = auth()->check() ? get_class(auth()->user()) : null;

                Folder::create($data);

                Notification::make()
                    ->title('Subfolder created successfully')
                    ->success()
                    ->send();

                // Reload folder with new subfolder
                $this->folder->load('folders');
            })
            ->modalWidth('md');

        // Upload file action
        $actions[] = Actions\Action::make('upload_file')
            ->label('Upload File')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                Forms\Components\FileUpload::make('files')
                    ->label('Files')
                    ->multiple()
                    ->required()
                    ->disk('public') // Simpan temporary di public disk dulu
                    ->directory('temp-uploads')
                    ->maxSize(10240) // 10MB
                    ->visibility('private')
                    ->acceptedFileTypes(['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
            ])
            ->action(function (array $data) {
                $mediaDisk = config('media-library.disk_name', 's3');
                $uploadCount = 0;

                foreach ($data['files'] as $filePath) {
                    try {
                        // Get full path from public disk
                        $fullPath = storage_path('app/public/' . $filePath);

                        if (file_exists($fullPath)) {
                            // Add media to folder dengan collection yang benar
                            $this->folder
                                ->addMedia($fullPath)
                                ->usingFileName(basename($filePath))
                                ->toMediaCollection($this->folder->collection, $mediaDisk);

                            $uploadCount++;

                            // Delete temporary file
                            @unlink($fullPath);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Upload failed: ' . $e->getMessage());
                    }
                }

                if ($uploadCount > 0) {
                    Notification::make()
                        ->title("{$uploadCount} file(s) uploaded successfully to S3")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Failed to upload files')
                        ->danger()
                        ->send();
                }

                // Reload media
                $this->folder->load('media');
            })
            ->modalWidth('md');

        return $actions;
    }

    public function getBreadcrumbs(): array
    {
        $trail = [url('/') => 'Dashboard'];

        // Build breadcrumb from parent chain
        $parents = [];
        $current = $this->folder;

        while ($current) {
            array_unshift($parents, $current);
            $current = $current->parent;
        }

        // Add root folders link
        $trail[FolderResource::getUrl('index')] = trans('filament-media-manager::messages.folders.title');

        // Add parent folders
        foreach ($parents as $parent) {
            $trail[FolderResource::getUrl('view', ['folder' => $parent])] = $parent->name;
        }

        return $trail;
    }

    public function navigateToFolder(string $folderUuid): void
    {
        $folder = Folder::where('uuid', $folderUuid)->firstOrFail();

        $this->redirect(FolderResource::getUrl('view', ['folder' => $folder]));
    }

    public function deleteMedia(int $mediaId): void
    {
        $media = $this->folder->media()->find($mediaId);

        if ($media) {
            $fileName = $media->file_name;
            $filePath = $media->getPathRelativeToRoot();

            // Delete media (akan otomatis hapus file di S3)
            $media->delete();

            // Verifikasi file terhapus dari S3
            $disk = \Storage::disk(config('media-library.disk_name', 's3'));
            $fileExists = $disk->exists($filePath);

            if ($fileExists) {
                // Jika masih ada, force delete
                $disk->delete($filePath);
                \Log::warning("File {$filePath} masih ada setelah media delete, di-force delete manual");
            }

            Notification::make()
                ->title("File '{$fileName}' deleted successfully")
                ->success()
                ->send();

            // Reload media list
            $this->folder->load('media');
        } else {
            Notification::make()
                ->title('File not found')
                ->danger()
                ->send();
        }
    }
}
