<?php

namespace Juniyasyos\FilamentMediaManager\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Folder extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'uuid',
        'parent_id',
        'model_type',
        'model_id',
        'name',
        'collection',
        'description',
        'icon',
        'color',
        'is_protected',
        'password',
        'is_hidden',
        'is_favorite',
        'is_public',
        'has_user_access',
        'user_id',
        'user_type',
    ];

    protected $casts = [
        'is_protected' => 'boolean',
        'is_hidden' => 'boolean',
        'is_favorite' => 'boolean',
        'is_public' => 'boolean',
        'has_user_access' => 'boolean'
    ];

    /**
     * Get the route key name for route binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->morphTo();
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'folder_has_models', 'folder_id', 'model_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID on creation
        static::creating(function ($folder) {
            if (empty($folder->uuid)) {
                $folder->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::addGlobalScope('user', function ($query) {
            if (filament('filament-media-manager')->allowUserAccess && auth()->check()) {
                $query->where(function ($q) {
                    $q->where('user_id', auth()->id())
                        ->orWhere('is_public', true)
                        ->orWhere(function ($subQ) {
                            $subQ->where('is_public', false)
                                ->where('has_user_access', true)
                                ->whereHas('users', function ($userQuery) {
                                    $userQuery->where('model_id', auth()->id())
                                        ->where('model_type', get_class(auth()->user()));
                                });
                        });
                });
            }
        });
    }

    public function folders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get all media files with this folder's collection name
     */
    public function getAllMediaByCollection()
    {
        $mediaModel = class_exists('Spatie\MediaLibrary\MediaCollections\Models\Media')
            ? 'Spatie\MediaLibrary\MediaCollections\Models\Media'
            : 'Spatie\Permission\Models\Media';

        return $mediaModel::where('collection_name', $this->collection)->get();
    }

    /**
     * Run full cleanup on folder and its media
     * Only delete orphaned/missing media files, NOT folders
     */
    public function runFullCleanup()
    {
        $deleted = 0;

        // Delete orphaned/missing media files only
        $mediaItems = $this->getMedia();

        foreach ($mediaItems as $media) {
            try {
                $disk = \Storage::disk(config('media-library.disk_name', 's3'));
                $filePath = $media->getPathRelativeToRoot();

                // Check if file exists, if not delete the media record
                if (!$disk->exists($filePath)) {
                    $media->delete();
                    $deleted++;
                }
            } catch (\Exception $e) {
                \Log::error("Media cleanup error: " . $e->getMessage());
            }
        }

        return [
            'media_deleted' => $deleted,
            'folders_deleted' => 0
        ];
    }
}
