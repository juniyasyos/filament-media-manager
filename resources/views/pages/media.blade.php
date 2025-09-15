@php
    // Optimize by loading folder with relationships to avoid N+1 queries
    $currentFolder = \Juniyasyos\FilamentMediaManager\Models\Folder::with(['folders.media'])->find($this->folder_id);

    $folders = filament('filament-media-manager')->allowSubFolders
        ? ($currentFolder?->folders ?? collect())
        : collect();
@endphp

@if (isset($records) || count($folders) > 0)
    <div class="gdrive-media-grid">
        @if (isset($records))
            @foreach ($records as $item)
                @if ($item instanceof \Juniyasyos\FilamentMediaManager\Models\Folder)
                    {{ $this->folderAction($item)(['record' => $item]) }}
                @else
                    <x-filament::modal width="3xl" slide-over>
                        <x-slot name="trigger" class="w-full h-full">
                            <div class="gdrive-media-card">
                                <div class="media-preview">
                                    @if (str($item->mime_type)->contains('image'))
                                        <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="media-image" />
                                    @elseif(str($item->mime_type)->contains('video'))
                                        <video src="{{ $item->getUrl() }}" class="media-video"></video>
                                    @elseif(str($item->mime_type)->contains('audio'))
                                        <x-icon name="heroicon-o-musical-note" class="media-icon audio-icon" />
                                    @else
                                        @php
                                            // Cache media types to avoid repeated calls
                                            static $cachedTypes = null;
                                            if ($cachedTypes === null) {
                                                $cachedTypes = \Juniyasyos\FilamentMediaManager\Facade\FilamentMediaManager::getTypes();
                                            }

                                            $hasPreview = false;
                                            $type = null;

                                            foreach ($cachedTypes as $getType) {
                                                if (str($item->file_name)->contains($getType->exstantion)) {
                                                    $hasPreview = $getType->preview;
                                                    $type = $getType;
                                                    break; // Exit early when found
                                                }
                                            }
                                        @endphp
                                        @if ($hasPreview && $type)
                                            <x-icon :name="$type->icon" class="media-icon file-icon" />
                                        @else
                                            <x-icon name="heroicon-o-document" class="media-icon document-icon" />
                                        @endif
                                    @endif
                                </div>
                                <div class="media-info">
                                    <div class="media-header">
                                        <h1 class="media-title">
                                            {{ $item->hasCustomProperty('title') ? (!empty($item->getCustomProperty('title')) ? $item->getCustomProperty('title') : $item->name) : $item->name }}
                                        </h1>
                                    </div>

                                    @if ($item->hasCustomProperty('description') && !empty($item->getCustomProperty('description')))
                                        <div class="media-description-section">
                                            <h2 class="media-description-title">Description</h2>
                                            <p class="media-description">
                                                {{ $item->getCustomProperty('description') }}
                                            </p>
                                        </div>
                                    @endif

                                    <div class="media-date">
                                        <p class="media-timestamp">
                                            {{ $item->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </x-slot>

                        <x-slot name="heading">
                            {{ $item->uuid }}
                        </x-slot>

                        <x-slot name="description">
                            {{ $item->file_name }}
                        </x-slot>

                        <div class="media-modal-content">
                            <div class="media-fullsize-container">

                                @if (str($item->mime_type)->contains('image'))
                                    <a href="{{ $item->getUrl() }}" target="_blank" class="media-fullsize-link">
                                        <img src="{{ $item->getUrl() }}" alt="{{ $item->name }}" class="media-fullsize-image" />
                                    </a>
                                @elseif(str($item->mime_type)->contains('video'))
                                    <a href="{{ $item->getUrl() }}" target="_blank" class="media-fullsize-link">
                                        <video class="media-fullsize-video" controls>
                                            <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                        </video>
                                    </a>
                                @elseif(str($item->mime_type)->contains('audio'))
                                    <a href="{{ $item->getUrl() }}" target="_blank" class="media-fullsize-link">
                                        <audio class="media-fullsize-audio" controls>
                                            <source src="{{ $item->getUrl() }}" type="{{ $item->mime_type }}">
                                        </audio>
                                    </a>
                                @else
                                    @php
                                        // Use cached types for modal as well
                                        static $modalCachedTypes = null;
                                        if ($modalCachedTypes === null) {
                                            $modalCachedTypes = \Juniyasyos\FilamentMediaManager\Facade\FilamentMediaManager::getTypes();
                                        }

                                        $hasPreview = false;
                                        foreach ($modalCachedTypes as $type) {
                                            if (str($item->file_name)->contains($type->exstantion)) {
                                                $hasPreview = $type->preview;
                                                break; // Exit early when found
                                            }
                                        }
                                    @endphp
                                    @if ($hasPreview)
                                        @include($hasPreview, ['media' => $item])
                                    @else
                                        <a href="{{ $item->getUrl() }}" target="_blank" class="media-fullsize-link">
                                            @if ($type)
                                                <x-icon :name="$type->icon" class="media-fullsize-icon" />
                                            @else
                                                <x-icon name="heroicon-o-document" class="media-fullsize-icon" />
                                            @endif
                                        </a>
                                    @endif
                                @endif
                                <div class="media-metadata">
                                    @if ($item->model)
                                        <div class="metadata-item">
                                            <h3 class="metadata-label">
                                                {{ trans('filament-media-manager::messages.media.meta.model') }}
                                            </h3>
                                            <p class="metadata-value">
                                                {{ str($item->model_type)->afterLast('\\')->title() }}[ID:{{ $item->model?->id }}]
                                            </p>
                                        </div>
                                    @endif
                                    <div class="metadata-item">
                                        <h3 class="metadata-label">
                                            {{ trans('filament-media-manager::messages.media.meta.file-name') }}
                                        </h3>
                                        <p class="metadata-value">
                                            {{ $item->file_name }}
                                        </p>
                                    </div>
                                    <div class="metadata-item">
                                        <h3 class="metadata-label">
                                            {{ trans('filament-media-manager::messages.media.meta.type') }}
                                        </h3>
                                        <p class="metadata-value">
                                            {{ $item->mime_type }}
                                        </p>
                                    </div>
                                    <div class="metadata-item">
                                        <h3 class="metadata-label">
                                            {{ trans('filament-media-manager::messages.media.meta.size') }}
                                        </h3>
                                        <p class="metadata-value">
                                            {{ $item->humanReadableSize }}
                                        </p>
                                    </div>
                                    <div class="metadata-item">
                                        <h3 class="metadata-label">
                                            {{ trans('filament-media-manager::messages.media.meta.disk') }}
                                        </h3>
                                        <p class="metadata-value">
                                            {{ $item->disk }}
                                        </p>
                                    </div>
                                    @if ($item->custom_properties)
                                        @foreach ($item->custom_properties as $key => $value)
                                            @if ($value)
                                                <div class="metadata-item">
                                                    <h3 class="metadata-label">{{ str($key)->title() }}</h3>
                                                    <p class="metadata-value">
                                                        {{ $value }}
                                                    </p>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        @php
                            // Optimize user access check by caching config values
                            static $allowUserAccess = null;
                            if ($allowUserAccess === null) {
                                $allowUserAccess = filament('filament-media-manager')->allowUserAccess;
                            }
                        @endphp

                        @if ($allowUserAccess && !empty($currentFolder->user_id))
                            @if ($currentFolder->user_id === auth()->user()->id && $currentFolder->user_type === get_class(auth()->user()))
                                <x-slot name="footer">
                                    {{ ($this->deleteMedia)(['record' => $item]) }}
                                </x-slot>
                            @endif
                        @else
                            <x-slot name="footer">
                                {{ ($this->deleteMedia)(['record' => $item]) }}
                            </x-slot>
                        @endif

                    </x-filament::modal>
                @endif
            @endforeach
        @endif
        @if (filament('filament-media-manager')->allowSubFolders)
            @foreach ($folders as $folder)
                {{ $this->folderAction($folder)(['record' => $folder]) }}
            @endforeach
        @endif
    </div>
@else
    <div class="gdrive-empty-state">
        <div class="empty-state-content">
            <div class="empty-state-icon">
                <x-filament::icon icon="heroicon-o-x-mark" class="empty-icon" />
            </div>

            <h2 class="empty-state-title">
                {{ trans('filament-media-manager::messages.empty.title') }}
            </h2>
        </div>
    </div>
@endif
