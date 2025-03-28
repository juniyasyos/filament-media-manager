<?php

namespace Juniyasyos\FilamentMediaManager\Traits;

use Juniyasyos\FilamentMediaManager\Models\Folder;

trait InteractsWithMediaFolders
{
    public function folders()
    {
        return $this->morphToMany(config('filament-media-manager.model.folder'), 'model', 'folder_has_models', 'model_id', 'folder_id');
    }

    public function myFolders()
    {
        return $this->morphMany(config('filament-media-manager.model.folder'), 'user');
    }
}
