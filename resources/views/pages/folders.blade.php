@php
    // Simplified data preparation
    $currentParent = $this->currentParent ?? null;
    $parentId = request()->get('parent_id');
    $viewMode = session('folder_view_mode', 'grid');
    $records = method_exists($this, 'getRecords') ? $this->getRecords() : collect();
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/filament-media-manager/css/gdrive-style.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('vendor/filament-media-manager/js/folder-manager.js') }}"></script>
@endpush

<div class="gdrive-content" x-data="folderManager()" x-init="init()" data-view-mode="{{ $viewMode }}">
    <!-- Header Section -->
    <div class="gdrive-header">
        <!-- Breadcrumb Navigation -->
        @if ($currentParent || $parentId)
            <div class="breadcrumb-section">
                <div class="breadcrumb-content">
                    @if ($currentParent && $currentParent->parent_id)
                        <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $currentParent->parent_id]) }}"
                            class="back-button">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="back-icon" />
                            Back to Parent
                        </a>
                    @elseif($currentParent)
                        <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index') }}"
                            class="back-button">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="back-icon" />
                            Back to Root
                        </a>
                    @endif

                    @if ($currentParent)
                        <div class="current-folder">
                            <x-filament::icon icon="heroicon-o-folder" class="current-folder-icon" />
                            <span class="current-folder-name">{{ $currentParent->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Toolbar -->
        <div class="toolbar">
            <!-- Selection Bar (when items selected) -->
            <div class="selection-bar-top" x-show="selectedItems.size > 0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0">

                <!-- Selection Info -->
                <div class="selection-info-top">
                    <span class="selection-count-top" x-text="selectedItems.size"></span>
                    <span class="selection-text-top">selected</span>
                </div>

                <!-- Action Buttons -->
                <div class="selection-actions-top">
                    <button class="top-btn btn-select-all" @click="toggleSelectAll()" title="Toggle Select All">
                        <x-filament::icon icon="heroicon-o-check-circle" class="btn-icon" />
                        <span>Select All</span>
                    </button>

                    <button class="top-btn btn-edit" @click="bulkEdit()" title="Edit">
                        <x-filament::icon icon="heroicon-o-pencil" class="btn-icon" />
                        <span>Edit</span>
                    </button>

                    <button class="top-btn btn-move" @click="bulkMove()" title="Move">
                        <x-filament::icon icon="heroicon-o-arrow-right" class="btn-icon" />
                        <span>Move</span>
                    </button>

                    <button class="top-btn btn-delete" @click="bulkDelete()" title="Delete">
                        <x-filament::icon icon="heroicon-o-trash" class="btn-icon" />
                        <span>Delete</span>
                    </button>
                </div>

                <!-- Clear Selection -->
                <button class="clear-btn-top" @click="clearSelection()" title="Clear selection">
                    <x-filament::icon icon="heroicon-o-x-mark" class="clear-icon" />
                </button>
            </div>

            <!-- Default Toolbar (when no selection) -->
            <div class="default-toolbar" x-show="selectedItems.size === 0">
                <!-- Main Actions -->
                <div class="toolbar-actions">
                    <button class="action-btn primary-btn">
                        <x-filament::icon icon="heroicon-o-folder-plus" class="btn-icon" />
                        <span>New Folder</span>
                    </button>

                    <button class="action-btn secondary-btn">
                        <x-filament::icon icon="heroicon-o-arrow-up-tray" class="btn-icon" />
                        <span>Upload</span>
                    </button>
                </div>

                <!-- View Controls -->
                <div class="toolbar-controls">
                    <div class="view-toggle">
                        <button class="view-toggle-btn" :class="{ 'active': viewMode === 'grid' }"
                                @click="setViewMode('grid')" title="Grid View">
                            <x-filament::icon icon="heroicon-o-squares-2x2" class="toggle-icon" />
                        </button>
                        <button class="view-toggle-btn" :class="{ 'active': viewMode === 'list' }"
                                @click="setViewMode('list')" title="List View">
                            <x-filament::icon icon="heroicon-o-list-bullet" class="toggle-icon" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="gdrive-main-content">
            @if(config('app.debug'))
            <!-- Debug Info (only in debug mode) -->
            <div class="text-xs bg-gray-100 p-2 mb-4 rounded">
                Records: {{ count($records ?? []) }} | View: {{ $viewMode }}
            </div>
            @endif

            <!-- Grid View -->
            <div class="folders-grid" x-show="viewMode === 'grid'">
                @if (count($records) > 0)
                    <div class="folder-grid">
                        @foreach ($records as $item)
                            @php
                                $hasChildren = $item->folders()->exists();
                                $url = $hasChildren
                                    ? \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', [
                                        'parent_id' => $item->id,
                                    ])
                                    : \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('media', [
                                        'folderName' => $item->name,
                                    ]);
                            @endphp

                            <div class="folder-item-wrapper selectable-item" data-folder-id="{{ $item->id }}"
                                data-folder-name="{{ strtolower($item->name) }}"
                                :class="{ 'selected': selectedItems.has('{{ $item->id }}') }"
                                x-data="{ showDropdown: false }">

                                <!-- Selection Checkbox -->
                                <div class="folder-selection">
                                    <input type="checkbox" class="folder-checkbox mm-checkbox"
                                        @change="toggleFolder('{{ $item->id }}')"
                                        :checked="selectedItems.has('{{ $item->id }}')">
                                </div>

                                <!-- Three-dot Menu -->
                                <div class="folder-actions-menu">
                                    <button class="three-dot-btn" @click="showDropdown = !showDropdown"
                                        :class="{ 'active': showDropdown }">
                                        <x-filament::icon icon="heroicon-o-ellipsis-vertical"
                                            class="three-dot-icon" />
                                    </button>
                                    <div class="dropdown-menu" x-show="showDropdown"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        @click.outside="showDropdown = false">
                                        <button class="dropdown-item edit-action"
                                            @click="editFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-pencil" class="dropdown-icon" />
                                            Edit
                                        </button>
                                        <button class="dropdown-item delete-action"
                                            @click="deleteFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-trash" class="dropdown-icon" />
                                            Delete
                                        </button>
                                        <button class="dropdown-item move-action"
                                            @click="moveFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-arrow-right" class="dropdown-icon" />
                                            Move
                                        </button>
                                        <button class="dropdown-item duplicate-action"
                                            @click="duplicateFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-document-duplicate"
                                                class="dropdown-icon" />
                                            Duplicate
                                        </button>
                                    </div>
                                </div>

                                <!-- Folder Link -->
                                <a href="{{ $url }}" class="folder-link"
                                    @click="handleFolderClick($event)">
                                    @include('filament-media-manager::pages.partials.folder-card', [
                                        'item' => $item,
                                    ])
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <x-filament::icon icon="heroicon-o-folder-plus" class="empty-icon" />
                        <h3 class="empty-title">No folders found</h3>
                        <p class="empty-description">Create your first folder to get started</p>
                    </div>
                @endif
            </div>

            <!-- List View -->
            <div class="folders-list" x-show="viewMode === 'list'">
                @if (count($records) > 0)
                    <div class="folder-list">
                        <!-- List Header -->
                        <div class="list-header">
                            <div class="list-col">Name</div>
                            <div class="list-col">Type</div>
                            <div class="list-col">Items</div>
                            <div class="list-col">Modified</div>
                        </div>

                        <!-- List Items -->
                        @foreach ($records as $item)
                            @php
                                $hasChildren = $item->folders()->exists();
                                $url = $hasChildren
                                    ? \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', [
                                        'parent_id' => $item->id,
                                    ])
                                    : \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('media', [
                                        'folderName' => $item->name,
                                    ]);
                            @endphp

                            <div class="list-item-wrapper selectable-item" data-folder-id="{{ $item->id }}"
                                data-folder-name="{{ strtolower($item->name) }}"
                                :class="{ 'selected': selectedItems.has('{{ $item->id }}') }"
                                x-data="{ showDropdown: false }">

                                <!-- Three-dot Menu -->
                                <div class="list-actions-menu">
                                    <button class="three-dot-btn" @click="showDropdown = !showDropdown"
                                        :class="{ 'active': showDropdown }">
                                        <x-filament::icon icon="heroicon-o-ellipsis-vertical"
                                            class="three-dot-icon" />
                                    </button>
                                    <div class="dropdown-menu" x-show="showDropdown"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        @click.outside="showDropdown = false">
                                        <button class="dropdown-item edit-action"
                                            @click="editFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-pencil" class="dropdown-icon" />
                                            Edit
                                        </button>
                                        <button class="dropdown-item delete-action"
                                            @click="deleteFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-trash" class="dropdown-icon" />
                                            Delete
                                        </button>
                                        <button class="dropdown-item move-action"
                                            @click="moveFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-arrow-right" class="dropdown-icon" />
                                            Move
                                        </button>
                                        <button class="dropdown-item duplicate-action"
                                            @click="duplicateFolder('{{ $item->id }}'); showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-document-duplicate"
                                                class="dropdown-icon" />
                                            Duplicate
                                        </button>
                                    </div>
                                </div>

                                <!-- List Row Link -->
                                <a href="{{ $url }}" class="list-row-link"
                                    @click="handleFolderClick($event)">
                                    <!-- Name with Icon -->
                                    <div class="list-col">
                                        <div class="list-item-info">
                                            <input type="checkbox" class="folder-checkbox mm-checkbox"
                                                @change="toggleFolder('{{ $item->id }}')"
                                                :checked="selectedItems.has('{{ $item->id }}')" @click.stop>
                                            <x-filament::icon :icon="$item->icon ?? 'heroicon-o-folder'"
                                                class="list-folder-icon"
                                                :style="$item->color ? 'color: ' . $item->color : ''" />
                                            <div class="item-details">
                                                <h3 class="item-name">{{ $item->name }}</h3>
                                                @if ($item->description)
                                                    <p class="item-description">{{ $item->description }}</p>
                                                @endif
                                            </div>
                                            @if ($item->is_protected)
                                                <x-filament::icon icon="heroicon-o-lock-closed"
                                                    class="protection-indicator" />
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Type -->
                                    <div class="list-col">
                                        <span class="item-type">
                                            {{ $hasChildren ? 'Folder' : 'Media Folder' }}
                                        </span>
                                    </div>

                                    <!-- Items Count -->
                                    <div class="list-col">
                                        <span class="item-count">
                                            @if ($hasChildren)
                                                {{ $item->folders()->count() }} folders
                                            @else
                                                {{ $item->media()->count() }} files
                                            @endif
                                        </span>
                                    </div>

                                    <!-- Modified Date -->
                                    <div class="list-col">
                                        <span class="item-date">
                                            {{ $item->updated_at?->diffForHumans() ?? 'Recently' }}
                                        </span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <x-filament::icon icon="heroicon-o-folder-plus" class="empty-icon" />
                        <h3 class="empty-title">No folders found</h3>
                        <p class="empty-description">Create your first folder to get started</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
