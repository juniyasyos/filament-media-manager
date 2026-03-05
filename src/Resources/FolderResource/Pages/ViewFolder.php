<?php

namespace Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages;

use Filament\Actions;
use Filament\Actions\DeleteAction;
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
    public $allMedia = [];

    public function mount(Folder $folder): void
    {
        $this->folder = $folder->load(['folders', 'media', 'parent']);

        // Auto cleanup orphaned files/folders
        try {
            $cleanupResult = $this->folder->runFullCleanup();
            if ($cleanupResult['media_deleted'] > 0) {
                \Filament\Notifications\Notification::make()
                    ->title("Cleanup: {$cleanupResult['media_deleted']} orphaned file(s) removed")
                    ->info()
                    ->send();

                // Reload folder after cleanup
                $this->folder->refresh();
            }
        } catch (\Exception $e) {
            \Log::error("Folder cleanup error: " . $e->getMessage());
        }

        // Load ALL media files with this collection_name, regardless of model type
        $this->allMedia = $this->folder->getAllMediaByCollection();
        // dd([
        //     'folder_name' => $this->folder->name,
        //     'folder_uuid' => $this->folder->uuid,
        //     'total_subfolders' => $this->folder->folders()->count(),
        //     'total_files' => $this->folder->getMedia()->count(),
        //     'folder' => $this->folder,
        // ]);
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
            // Back to parent folder action
            $actions[] = Actions\Action::make('back')
                ->label(trans('filament-media-manager::messages.folders.actions.back'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FolderResource::getUrl('view', ['folder' => $this->folder->parent]));

            // Delete action with redirect to parent folder after deletion
            $actions[] = DeleteAction::make()
                ->label('Delete Folder')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete Folder')
                ->record($this->folder)
                ->successRedirectUrl(FolderResource::getUrl('view', ['folder' => $this->folder->parent]));
        } else {
            // Back to root folders action
            $actions[] = Actions\Action::make('back_to_root')
                ->label('Back to Root')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FolderResource::getUrl('index'));

            // Delete action with redirect to root folders after deletion
            $actions[] = DeleteAction::make()
                ->label('Delete Folder')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete Folder')
                ->record($this->folder)
                ->successRedirectUrl(FolderResource::getUrl('index'));
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
                $debugInfo = [];

                foreach ($data['files'] as $filePath) {
                    try {
                        // Get full path from public disk
                        $fullPath = storage_path('app/public/' . $filePath);

                        $debugInfo[] = [
                            'file_input_path' => $filePath,
                            'full_path' => $fullPath,
                            'file_exists' => file_exists($fullPath),
                            'basename' => basename($filePath),
                            'folder_id' => $this->folder->id,
                            'folder_uuid' => $this->folder->uuid,
                            'folder_collection' => $this->folder->collection,
                            'media_disk' => $mediaDisk,
                        ];

                        if (file_exists($fullPath)) {
                            // Add media to folder dengan collection yang benar
                            $media = $this->folder
                                ->addMedia($fullPath)
                                ->usingFileName(basename($filePath))
                                ->toMediaCollection($this->folder->collection, $mediaDisk);

                            $debugInfo[] = [
                                'upload_status' => 'SUCCESS',
                                'media_id' => $media->id,
                                'media_file_name' => $media->file_name,
                                'media_collection_name' => $media->collection_name,
                            ];

                            $uploadCount++;

                            // Delete temporary file
                            @unlink($fullPath);
                        } else {
                            $debugInfo[] = [
                                'upload_status' => 'FILE_NOT_FOUND',
                                'message' => 'File tidak ditemukan di path: ' . $fullPath,
                            ];
                        }
                    } catch (\Exception $e) {
                        $debugInfo[] = [
                            'upload_status' => 'ERROR',
                            'error_message' => $e->getMessage(),
                            'error_file' => $e->getFile(),
                            'error_line' => $e->getLine(),
                        ];
                        \Log::error('Upload failed: ' . $e->getMessage());
                    }
                }

                // Check media in database
                $mediaInDb = $this->folder->media()->get();

                // dd([
                //     'upload_summary' => [
                //         'total_files_processed' => count($data['files']),
                //         'successful_uploads' => $uploadCount,
                //         'folder_info' => [
                //             'id' => $this->folder->id,
                //             'uuid' => $this->folder->uuid,
                //             'name' => $this->folder->name,
                //             'collection' => $this->folder->collection,
                //         ],
                //     ],
                //     'upload_details' => $debugInfo,
                //     'media_in_database' => $mediaInDb,
                //     'folder_media_count' => $this->folder->media()->count(),
                //     'folder_getMedia_count' => $this->folder->getMedia()->count(),
                // ]);
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
