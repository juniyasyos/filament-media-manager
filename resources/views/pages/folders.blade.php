@php
    // Optimize by caching frequently accessed data
    $currentParent = $this->currentParent ?? null;
    $parentId = request()->get('parent_id');
    $viewMode = session('folder_view_mode', 'grid'); // grid or list

    // Pre-load parent relationship to avoid N+1 queries
    $parentWithRelations = $currentParent?->load('parent');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/filament-media-manager/css/gdrive-style.css') }}">
@endpush

<script>
    /**
     * Inline Alpine.js Folder Manager Component (for immediate availability)
     */
    window.folderManager = function() {
        return {
            // State
            selectedItems: new Set(),
            viewMode: '{{ $viewMode }}',
            totalItems: {{ count($records) }},
            selectAll: false,
            folderSelected: false,
            searchQuery: '',

            // Initialize
            init() {
                console.log('Folder Manager initialized with viewMode:', this.viewMode);
                this.updateTotalItems();
            },

            // Selection Methods
            toggleFolder(folderId) {
                if (this.selectedItems.has(folderId)) {
                    this.selectedItems.delete(folderId);
                } else {
                    this.selectedItems.add(folderId);
                }
                this.updateSelectAllState();
            },

            toggleSelectAll() {
                const totalFolders = document.querySelectorAll('[data-folder-id]').length;

                if (this.selectedItems.size === 0) {
                    // Select all items
                    document.querySelectorAll('[data-folder-id]').forEach(element => {
                        const folderId = element.getAttribute('data-folder-id');
                        this.selectedItems.add(folderId);
                    });
                    this.selectAll = true;
                } else if (this.selectedItems.size === totalFolders) {
                    // Deselect all items
                    this.selectedItems.clear();
                    this.selectAll = false;
                } else {
                    // Partial selection - select remaining items
                    document.querySelectorAll('[data-folder-id]').forEach(element => {
                        const folderId = element.getAttribute('data-folder-id');
                        this.selectedItems.add(folderId);
                    });
                    this.selectAll = true;
                }
            },

            updateSelectAllState() {
                const totalFolders = document.querySelectorAll('[data-folder-id]').length;
                this.selectAll = this.selectedItems.size === totalFolders && totalFolders > 0;
            },

            // Advanced UX helpers
            get selectAllState() {
                const totalFolders = document.querySelectorAll('[data-folder-id]').length;
                if (this.selectedItems.size === 0) return 'none';
                if (this.selectedItems.size === totalFolders) return 'all';
                return 'partial';
            },

            get selectAllLabel() {
                const state = this.selectAllState;
                const count = this.selectedItems.size;
                const total = document.querySelectorAll('[data-folder-id]').length;

                switch (state) {
                    case 'none':
                        return 'Select all';
                    case 'partial':
                        return `Select all (${count}/${total})`;
                    case 'all':
                        return 'Deselect all';
                    default:
                        return 'Select all';
                }
            },

            clearSelection() {
                this.selectedItems.clear();
                this.selectAll = false;
            },

            updateTotalItems() {
                this.totalItems = document.querySelectorAll('[data-folder-id]').length;
            },

            // View Mode Methods
            setViewMode(mode) {
                this.viewMode = mode;
                this.clearSelection();
            },

            // Action Methods
            bulkDelete() {
                if (this.selectedItems.size === 0) return;

                if (confirm(`Are you sure you want to delete ${this.selectedItems.size} folder(s)?`)) {
                    console.log('Bulk delete:', Array.from(this.selectedItems));
                    this.performBulkAction('delete', Array.from(this.selectedItems));
                }
            },

            bulkEdit() {
                if (this.selectedItems.size === 0) return;
                console.log('Bulk edit:', Array.from(this.selectedItems));
                this.performBulkAction('edit', Array.from(this.selectedItems));
            },

            bulkMove() {
                if (this.selectedItems.size === 0) return;
                console.log('Bulk move:', Array.from(this.selectedItems));
                this.performBulkAction('move', Array.from(this.selectedItems));
            },

            // Individual Action Methods
            editFolder(folderId) {
                console.log('Edit folder:', folderId);
            },

            deleteFolder(folderId) {
                if (confirm('Are you sure you want to delete this folder?')) {
                    console.log('Delete folder:', folderId);
                    this.performAction('delete', folderId);
                }
            },

            moveFolder(folderId) {
                console.log('Move folder:', folderId);
                this.performAction('move', folderId);
            },

            duplicateFolder(folderId) {
                console.log('Duplicate folder:', folderId);
                this.performAction('duplicate', folderId);
            },

            // Helper Methods
            performBulkAction(action, folderIds) {
                console.log(`Performing bulk ${action} on:`, folderIds);
            },

            performAction(action, folderId) {
                console.log(`Performing ${action} on folder:`, folderId);
            },

            handleFolderClick(event) {
                if (event.target.closest('.folder-selection') ||
                    event.target.closest('.folder-actions-menu') ||
                    event.target.closest('.list-actions-menu') ||
                    event.target.closest('.folder-checkbox')) {
                    event.preventDefault();
                    return false;
                }
                return true;
            },

            // Search Methods
            filterFolders() {
                const query = this.searchQuery.toLowerCase();
                document.querySelectorAll('[data-folder-name]').forEach(item => {
                    const name = item.getAttribute('data-folder-name');
                    if (name.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }
        }
    };
</script>

<div class="gdrive-content" x-data="folderManager()" x-init="init()">
    <!-- Header Section -->
    <div class="gdrive-header">
        <!-- Breadcrumb Navigation -->
        @if ($currentParent || $parentId)
            <div class="breadcrumb-section">
                <div class="breadcrumb-content">
                    @if ($parentWithRelations && $parentWithRelations->parent)
                        <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $parentWithRelations->parent->id]) }}"
                            class="back-button">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="back-icon" />
                            Back to {{ $parentWithRelations->parent->name }}
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
                            @if ($currentParent->path)
                                <span class="current-folder-path">{{ $currentParent->path }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Toolbar -->
        <div class="toolbar">
            <!-- Selection Actions Bar -->
            <div class="selection-actions-bar" x-show="selectedItems.size > 0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="selection-info">
                    <span x-text="selectedItems.size"></span> selected
                </div>
                <div class="selection-actions">
                    <button class="action-btn action-btn-danger" @click="bulkDelete()"
                        :disabled="selectedItems.size === 0">
                        <x-filament::icon icon="heroicon-o-trash" class="action-icon" />
                        Delete
                    </button>
                    <button class="action-btn action-btn-primary" @click="bulkEdit()"
                        :disabled="selectedItems.size === 0">
                        <x-filament::icon icon="heroicon-o-pencil" class="action-icon" />
                        Edit
                    </button>
                    <button class="action-btn action-btn-secondary" @click="bulkMove()"
                        :disabled="selectedItems.size === 0">
                        <x-filament::icon icon="heroicon-o-arrow-right" class="action-icon" />
                        Move
                    </button>
                    <button class="action-btn action-btn-ghost" @click="clearSelection()">
                        <x-filament::icon icon="heroicon-o-x-mark" class="action-icon" />
                        Clear
                    </button>
                </div>
            </div>

            <!-- Default Toolbar -->
            <div class="default-toolbar" x-show="selectedItems.size === 0">
                <!-- Left Side: Search and Sort -->
                <div class="toolbar-left">
                    <!-- Search Box -->
                    {{-- <div class="search-container">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="search-icon" />
                    <input type="search" placeholder="Search folders..." class="mm-search-input" id="folder-search">
                </div> --}}

                    <!-- Sort Dropdown -->
                    {{-- <div class="sort-container">
                    <select class="mm-select">
                        <option value="name">Sort by Name</option>
                        <option value="date">Sort by Date</option>
                        <option value="size">Sort by Size</option>
                    </select>
                    <x-filament::icon icon="heroicon-o-chevron-down" class="sort-icon" />
                </div> --}}
                </div>

                <!-- Right Side: View Toggle -->
                <div class="toolbar-right">
                    <!-- Advanced Select All Container -->
                    <div class="select-all-container advanced-select"
                        :class="{
                            'select-none': selectAllState === 'none',
                            'select-partial': selectAllState === 'partial',
                            'select-all': selectAllState === 'all'
                        }">
                        <div class="select-all-wrapper">
                            <input type="checkbox" class="mm-checkbox select-all-checkbox"
                                :checked="selectAllState === 'all'" :indeterminate="selectAllState === 'partial'"
                                @change="toggleSelectAll()" id="select-all-checkbox"
                                style="appearance:none;-webkit-appearance:none;-moz-appearance:none;">
                            <div class="select-all-visual">
                                <!-- None State Icon -->
                                <div class="select-icon select-none-icon" x-show="selectAllState === 'none'">
                                    <x-filament::icon icon="heroicon-o-square-2-stack" class="w-4 h-4" />
                                </div>
                                <!-- Partial State Icon -->
                                <div class="select-icon select-partial-icon" x-show="selectAllState === 'partial'">
                                    <x-filament::icon icon="heroicon-o-minus" class="w-4 h-4" />
                                </div>
                                <!-- All State Icon -->
                                <div class="select-icon select-all-icon" x-show="selectAllState === 'all'">
                                    <x-filament::icon icon="heroicon-o-check" class="w-4 h-4" />
                                </div>
                            </div>
                            <label class="select-all-label" for="select-all-checkbox" x-text="selectAllLabel"></label>
                        </div>

                        <!-- Selection Counter Badge -->
                        <div class="selection-counter" x-show="selectedItems.size > 0" x-transition>
                            <span class="counter-text" x-text="selectedItems.size"></span>
                        </div>
                    </div>

                    <!-- Enhanced View Mode Toggle -->
                    <div class="view-toggle-container enhanced-toggle">
                        <div class="toggle-wrapper">
                            <button class="view-toggle-btn grid-btn"
                                :class="{
                                    'active': viewMode === 'grid',
                                    'inactive': viewMode !== 'grid'
                                }"
                                @click="setViewMode('grid')" :aria-pressed="viewMode === 'grid'">
                                <div class="btn-content">
                                    <x-filament::icon icon="heroicon-o-squares-2x2" class="toggle-icon" />
                                    <span class="toggle-label">Grid</span>
                                </div>
                                <div class="btn-indicator" x-show="viewMode === 'grid'"></div>
                            </button>
                            <button class="view-toggle-btn list-btn"
                                :class="{
                                    'active': viewMode === 'list',
                                    'inactive': viewMode !== 'list'
                                }"
                                @click="setViewMode('list')" :aria-pressed="viewMode === 'list'">
                                <div class="btn-content">
                                    <x-filament::icon icon="heroicon-o-bars-3" class="toggle-icon" />
                                    <span class="toggle-label">List</span>
                                </div>
                                <div class="btn-indicator" x-show="viewMode === 'list'"></div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="gdrive-main-content">
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
                                        x-model="folderSelected" @change="toggleFolder('{{ $item->id }}')"
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
                                                x-model="folderSelected"
                                                @change="toggleFolder('{{ $item->id }}')"
                                                :checked="selectedItems.has('{{ $item->id }}')" @click.stop>
                                            <x-filament::icon :icon="$item->icon ?? 'heroicon-o-folder'" class="list-folder-icon"
                                                style="color: {{ $item->color ?? '#3B82F6' }}" />
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
