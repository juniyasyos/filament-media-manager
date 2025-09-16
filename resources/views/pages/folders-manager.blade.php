<x-filament-panels::page>
    <div class="gdrive-content">
        <!-- Header Section -->
        <div class="gdrive-header">
            <!-- Breadcrumb Navigation -->
            @if ($this->currentParent)
                <div class="breadcrumb-section">
                    <div class="breadcrumb-content">
                        <a href="{{ static::getResource()::getUrl('folders-manager') }}" class="back-button">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="back-icon" />
                            Back to Root
                        </a>

                        <div class="current-folder">
                            <x-filament::icon icon="heroicon-o-folder" class="current-folder-icon" />
                            <span class="current-folder-name">{{ $this->currentParent->name }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Toolbar -->
            <div class="toolbar">
                <!-- Selection Bar (when items selected) -->
                @if(count($this->selectedItems) > 0)
                <div class="selection-bar-top">
                    <!-- Selection Info -->
                    <div class="selection-info-top">
                        <span class="selection-count-top">{{ count($this->selectedItems) }}</span>
                        <span class="selection-text-top">selected</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="selection-actions-top">
                        <button wire:click="selectAll" class="top-btn btn-select-all" title="Toggle Select All">
                            <x-filament::icon icon="heroicon-o-check-circle" class="btn-icon" />
                            <span>Select All</span>
                        </button>

                        <button wire:click="bulkMove" class="top-btn btn-move" title="Move">
                            <x-filament::icon icon="heroicon-o-arrow-right" class="btn-icon" />
                            <span>Move</span>
                        </button>

                        <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete the selected folders?"
                                class="top-btn btn-delete" title="Delete">
                            <x-filament::icon icon="heroicon-o-trash" class="btn-icon" />
                            <span>Delete</span>
                        </button>
                    </div>

                    <!-- Clear Selection -->
                    <button wire:click="clearSelection" class="clear-btn-top" title="Clear selection">
                        <x-filament::icon icon="heroicon-o-x-mark" class="clear-icon" />
                    </button>
                </div>
                @else
                <!-- Default Toolbar (when no selection) -->
                <div class="default-toolbar">
                    <!-- Main Actions -->
                    <div class="toolbar-actions">
                        {{ $this->getAction('create') }}

                        <button class="action-btn secondary-btn">
                            <x-filament::icon icon="heroicon-o-arrow-up-tray" class="btn-icon" />
                            <span>Upload</span>
                        </button>
                    </div>

                    <!-- View Controls -->
                    <div class="toolbar-controls">
                        <div class="view-toggle">
                            <button wire:click="setViewMode('grid')"
                                    class="view-toggle-btn {{ $this->viewMode === 'grid' ? 'active' : '' }}"
                                    title="Grid View">
                                <x-filament::icon icon="heroicon-o-squares-2x2" class="toggle-icon" />
                            </button>
                            <button wire:click="setViewMode('list')"
                                    class="view-toggle-btn {{ $this->viewMode === 'list' ? 'active' : '' }}"
                                    title="List View">
                                <x-filament::icon icon="heroicon-o-list-bullet" class="toggle-icon" />
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="gdrive-main-content">
            <!-- Grid View -->
            @if($this->viewMode === 'grid')
            <div class="folders-grid">
                @if ($this->records->count() > 0)
                    <div class="folder-grid">
                        @foreach ($this->records as $item)
                            @php
                                $hasChildren = $item->folders()->exists();
                                $url = $hasChildren
                                    ? static::getResource()::getUrl('folders-manager', ['parent_id' => $item->id])
                                    : static::getResource()::getUrl('folders-manager', ['folderName' => $item->name]);
                            @endphp

                            <div class="folder-item-wrapper selectable-item {{ in_array((string)$item->id, $this->selectedItems) ? 'selected' : '' }}">
                                <!-- Selection Checkbox -->
                                <div class="folder-selection">
                                    <input type="checkbox" class="folder-checkbox mm-checkbox"
                                        wire:click="toggleSelection('{{ $item->id }}')"
                                        {{ in_array((string)$item->id, $this->selectedItems) ? 'checked' : '' }}>
                                </div>

                                <!-- Three-dot Menu -->
                                <div class="folder-actions-menu" x-data="{ showDropdown: false }">
                                    <button class="three-dot-btn" @click="showDropdown = !showDropdown">
                                        <x-filament::icon icon="heroicon-o-ellipsis-vertical" class="three-dot-icon" />
                                    </button>
                                    <div class="dropdown-menu" x-show="showDropdown" @click.outside="showDropdown = false">
                                        <button class="dropdown-item edit-action" @click="showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-pencil" class="dropdown-icon" />
                                            Edit
                                        </button>
                                        <button wire:click="deleteFolder('{{ $item->id }}')"
                                                wire:confirm="Are you sure you want to delete this folder?"
                                                class="dropdown-item delete-action" @click="showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-trash" class="dropdown-icon" />
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <!-- Folder Link -->
                                <a href="{{ $url }}" class="folder-link">
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
            @endif

            <!-- List View -->
            @if($this->viewMode === 'list')
            <div class="folders-list">
                @if ($this->records->count() > 0)
                    <div class="folder-list">
                        <!-- List Header -->
                        <div class="list-header">
                            <div class="list-col">Name</div>
                            <div class="list-col">Type</div>
                            <div class="list-col">Items</div>
                            <div class="list-col">Modified</div>
                        </div>

                        <!-- List Items -->
                        @foreach ($this->records as $item)
                            @php
                                $hasChildren = $item->folders()->exists();
                                $url = $hasChildren
                                    ? static::getResource()::getUrl('folders-manager', ['parent_id' => $item->id])
                                    : static::getResource()::getUrl('folders-manager', ['folderName' => $item->name]);
                            @endphp

                            <div class="list-item-wrapper selectable-item {{ in_array((string)$item->id, $this->selectedItems) ? 'selected' : '' }}">
                                <!-- Three-dot Menu -->
                                <div class="list-actions-menu" x-data="{ showDropdown: false }">
                                    <button class="three-dot-btn" @click="showDropdown = !showDropdown">
                                        <x-filament::icon icon="heroicon-o-ellipsis-vertical" class="three-dot-icon" />
                                    </button>
                                    <div class="dropdown-menu" x-show="showDropdown" @click.outside="showDropdown = false">
                                        <button class="dropdown-item edit-action" @click="showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-pencil" class="dropdown-icon" />
                                            Edit
                                        </button>
                                        <button wire:click="deleteFolder('{{ $item->id }}')"
                                                wire:confirm="Are you sure you want to delete this folder?"
                                                class="dropdown-item delete-action" @click="showDropdown = false">
                                            <x-filament::icon icon="heroicon-o-trash" class="dropdown-icon" />
                                            Delete
                                        </button>
                                    </div>
                                </div>

                                <!-- List Row Link -->
                                <a href="{{ $url }}" class="list-row-link">
                                    <!-- Name with Icon -->
                                    <div class="list-col">
                                        <div class="list-item-info">
                                            <input type="checkbox" class="folder-checkbox mm-checkbox"
                                                wire:click="toggleSelection('{{ $item->id }}')"
                                                {{ in_array((string)$item->id, $this->selectedItems) ? 'checked' : '' }}>
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
                                                <x-filament::icon icon="heroicon-o-lock-closed" class="protection-indicator" />
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
            @endif
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('vendor/filament-media-manager/css/gdrive-style.css') }}">
    @endpush
</x-filament-panels::page>
