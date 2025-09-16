
@php
    // Optimize by caching frequently accessed data
    $currentParent = $this->currentParent ?? null;
    $parentId = request()->get('parent_id');
    $viewMode = session('folder_view_mode', 'grid'); // grid or list

    // Pre-load parent relationship to avoid N+1 queries
    $parentWithRelations = $currentParent?->load('parent');
@endphp

<div class="gdrive-content">
    <!-- Header Section -->
    <div class="gdrive-header">
        <!-- Breadcrumb Navigation -->
        @if($currentParent || $parentId)
            <div class="breadcrumb-section">
                <div class="breadcrumb-content">
                    @if($parentWithRelations && $parentWithRelations->parent)
                        <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $parentWithRelations->parent->id]) }}" class="back-button">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="back-icon" />
                            Back to {{ $parentWithRelations->parent->name }}
                        </a>
                    @elseif($currentParent)
                        <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index') }}" class="back-button">
                            <x-filament::icon icon="heroicon-o-chevron-left" class="back-icon" />
                            Back to Root
                        </a>
                    @endif

                    @if($currentParent)
                        <div class="current-folder">
                            <x-filament::icon icon="heroicon-o-folder" class="current-folder-icon" />
                            <span class="current-folder-name">{{ $currentParent->name }}</span>
                            @if($currentParent->path)
                                <span class="current-folder-path">{{ $currentParent->path }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Toolbar -->
        <div class="toolbar">
            <!-- Selection Actions Bar (Hidden by default) -->
            <div class="selection-actions-bar" id="selection-actions" style="display: none;">
                <div class="selection-info">
                    <span id="selected-count">0</span> selected
                </div>
                <div class="selection-actions">
                    <button class="action-btn action-btn-danger" id="bulk-delete-btn">
                        <x-filament::icon icon="heroicon-o-trash" class="action-icon" />
                        Delete
                    </button>
                    <button class="action-btn action-btn-primary" id="bulk-edit-btn">
                        <x-filament::icon icon="heroicon-o-pencil" class="action-icon" />
                        Edit
                    </button>
                    <button class="action-btn action-btn-secondary" id="bulk-move-btn">
                        <x-filament::icon icon="heroicon-o-arrow-right" class="action-icon" />
                        Move
                    </button>
                    <button class="action-btn action-btn-ghost" id="clear-selection-btn">
                        <x-filament::icon icon="heroicon-o-x-mark" class="action-icon" />
                        Clear
                    </button>
                </div>
            </div>

            <!-- Default Toolbar (Shown when no selection) -->
            <div class="default-toolbar" id="default-toolbar">
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
                <!-- Select All Checkbox -->
                <div class="select-all-container">
                    <input type="checkbox" class="mm-checkbox" id="select-all-checkbox">
                    <label for="select-all-checkbox" class="select-all-label">Select all</label>
                </div>

                <!-- View Mode Toggle -->
                <div class="view-toggle-container">
                    <button class="view-toggle-btn {{ $viewMode === 'grid' ? 'active' : '' }}" data-view="grid" id="grid-view-btn">
                        <x-filament::icon icon="heroicon-o-squares-2x2" />
                    </button>
                    <button class="view-toggle-btn {{ $viewMode === 'list' ? 'active' : '' }}" data-view="list" id="list-view-btn">
                        <x-filament::icon icon="heroicon-o-bars-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="gdrive-main-content">
        <!-- Grid View -->
        <div id="grid-view" class="folders-grid {{ $viewMode === 'grid' ? '' : 'hidden' }}">
            @if(count($records) > 0)
                <div class="folder-grid">
                    @foreach($records as $item)
                        @php
                            $hasChildren = $item->folders()->exists();
                            $url = $hasChildren
                                ? \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $item->id])
                                : \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('media', ['folderName' => $item->name]);
                        @endphp

                        <div class="folder-item-wrapper selectable-item" data-folder-id="{{ $item->id }}" data-folder-name="{{ strtolower($item->name) }}">
                            <!-- Selection Checkbox -->
                            <div class="folder-selection">
                                <input type="checkbox" class="folder-checkbox mm-checkbox" data-folder-id="{{ $item->id }}">
                            </div>

                            <!-- Three-dot Menu -->
                            <div class="folder-actions-menu">
                                <button class="three-dot-btn" data-folder-id="{{ $item->id }}">
                                    <x-filament::icon icon="heroicon-o-ellipsis-vertical" class="three-dot-icon" />
                                </button>
                                <div class="dropdown-menu" id="menu-{{ $item->id }}">
                                    <button class="dropdown-item edit-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-pencil" class="dropdown-icon" />
                                        Edit
                                    </button>
                                    <button class="dropdown-item delete-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-trash" class="dropdown-icon" />
                                        Delete
                                    </button>
                                    <button class="dropdown-item move-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-arrow-right" class="dropdown-icon" />
                                        Move
                                    </button>
                                    <button class="dropdown-item duplicate-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-document-duplicate" class="dropdown-icon" />
                                        Duplicate
                                    </button>
                                </div>
                            </div>

                            <!-- Folder Link -->
                            <a href="{{ $url }}" class="folder-link">
                                @include('filament-media-manager::pages.partials.folder-card', ['item' => $item])
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
        <div id="list-view" class="folders-list {{ $viewMode === 'list' ? '' : 'hidden' }}">
            @if(count($records) > 0)
                <div class="folder-list">
                    <!-- List Header -->
                    <div class="list-header">
                        <div class="list-col">Name</div>
                        <div class="list-col">Type</div>
                        <div class="list-col">Items</div>
                        <div class="list-col">Modified</div>
                    </div>

                    <!-- List Items -->
                    @foreach($records as $item)
                        @php
                            $hasChildren = $item->folders()->exists();
                            $url = $hasChildren
                                ? \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $item->id])
                                : \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('media', ['folderName' => $item->name]);
                        @endphp

                        <div class="list-item-wrapper selectable-item" data-folder-id="{{ $item->id }}" data-folder-name="{{ strtolower($item->name) }}">
                            <!-- Three-dot Menu -->
                            <div class="list-actions-menu">
                                <button class="three-dot-btn" data-folder-id="{{ $item->id }}">
                                    <x-filament::icon icon="heroicon-o-ellipsis-vertical" class="three-dot-icon" />
                                </button>
                                <div class="dropdown-menu" id="list-menu-{{ $item->id }}">
                                    <button class="dropdown-item edit-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-pencil" class="dropdown-icon" />
                                        Edit
                                    </button>
                                    <button class="dropdown-item delete-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-trash" class="dropdown-icon" />
                                        Delete
                                    </button>
                                    <button class="dropdown-item move-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-arrow-right" class="dropdown-icon" />
                                        Move
                                    </button>
                                    <button class="dropdown-item duplicate-action" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon icon="heroicon-o-document-duplicate" class="dropdown-icon" />
                                        Duplicate
                                    </button>
                                </div>
                            </div>

                            <!-- List Row Link -->
                            <a href="{{ $url }}" class="list-row-link">
                                <!-- Name with Icon -->
                                <div class="list-col">
                                    <div class="list-item-info">
                                        <input type="checkbox" class="folder-checkbox mm-checkbox" data-folder-id="{{ $item->id }}">
                                        <x-filament::icon :icon="$item->icon ?? 'heroicon-o-folder'"
                                                          class="list-folder-icon"
                                                          style="color: {{ $item->color ?? '#3B82F6' }}" />
                                        <div class="item-details">
                                            <h3 class="item-name">{{ $item->name }}</h3>
                                            @if($item->description)
                                                <p class="item-description">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                        @if($item->is_protected)
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
                                        @if($hasChildren)
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Selection System
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const folderCheckboxes = document.querySelectorAll('.folder-checkbox');
    const selectionActionsBar = document.getElementById('selection-actions');
    const defaultToolbar = document.getElementById('default-toolbar');
    const selectedCountSpan = document.getElementById('selected-count');

    let selectedItems = new Set();

    // Update selection UI
    function updateSelectionUI() {
        const count = selectedItems.size;
        selectedCountSpan.textContent = count;

        if (count > 0) {
            selectionActionsBar.style.display = 'flex';
            defaultToolbar.style.display = 'none';
        } else {
            selectionActionsBar.style.display = 'none';
            defaultToolbar.style.display = 'flex';
        }

        // Update visual selection state
        document.querySelectorAll('.selectable-item').forEach(item => {
            const folderId = item.dataset.folderId;
            if (selectedItems.has(folderId)) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });

        // Update select all checkbox
        selectAllCheckbox.indeterminate = count > 0 && count < folderCheckboxes.length;
        selectAllCheckbox.checked = count === folderCheckboxes.length;
    }

    // Handle individual checkbox changes
    folderCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            const folderId = this.dataset.folderId;

            if (this.checked) {
                selectedItems.add(folderId);
            } else {
                selectedItems.delete(folderId);
            }

            updateSelectionUI();
        });
    });

    // Handle select all checkbox
    selectAllCheckbox.addEventListener('change', function() {
        selectedItems.clear();

        if (this.checked) {
            folderCheckboxes.forEach(checkbox => {
                selectedItems.add(checkbox.dataset.folderId);
                checkbox.checked = true;
            });
        } else {
            folderCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        updateSelectionUI();
    });

    // Clear selection
    document.getElementById('clear-selection-btn').addEventListener('click', function() {
        selectedItems.clear();
        folderCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectionUI();
    });

    // Bulk Actions
    document.getElementById('bulk-delete-btn').addEventListener('click', function() {
        if (selectedItems.size > 0) {
            if (confirm(`Are you sure you want to delete ${selectedItems.size} folder(s)?`)) {
                console.log('Bulk delete:', Array.from(selectedItems));
                // Implement bulk delete logic here
            }
        }
    });

    document.getElementById('bulk-edit-btn').addEventListener('click', function() {
        if (selectedItems.size > 0) {
            console.log('Bulk edit:', Array.from(selectedItems));
            // Implement bulk edit logic here
        }
    });

    document.getElementById('bulk-move-btn').addEventListener('click', function() {
        if (selectedItems.size > 0) {
            console.log('Bulk move:', Array.from(selectedItems));
            // Implement bulk move logic here
        }
    });

    // Three-dot Menu System
    const dropdownMenus = document.querySelectorAll('.dropdown-menu');
    const threeDotBtns = document.querySelectorAll('.three-dot-btn');

    // Close all dropdowns
    function closeAllDropdowns() {
        dropdownMenus.forEach(menu => {
            menu.classList.remove('show');
        });
        threeDotBtns.forEach(btn => {
            btn.classList.remove('active');
        });
    }

    // Handle three-dot button clicks
    threeDotBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const folderId = this.dataset.folderId;
            const menu = document.getElementById(`menu-${folderId}`) || document.getElementById(`list-menu-${folderId}`);

            // Close other menus
            closeAllDropdowns();

            // Toggle current menu
            if (menu) {
                menu.classList.add('show');
                this.classList.add('active');
            }
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.folder-actions-menu') && !e.target.closest('.list-actions-menu')) {
            closeAllDropdowns();
        }
    });

    // Handle dropdown actions
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const folderId = this.dataset.folderId;
            const action = this.classList.contains('edit-action') ? 'edit' :
                          this.classList.contains('delete-action') ? 'delete' :
                          this.classList.contains('move-action') ? 'move' :
                          this.classList.contains('duplicate-action') ? 'duplicate' : '';

            console.log(`Action: ${action} for folder: ${folderId}`);

            // Implement specific actions
            switch(action) {
                case 'edit':
                    // Implement edit logic
                    break;
                case 'delete':
                    if (confirm('Are you sure you want to delete this folder?')) {
                        // Implement delete logic
                    }
                    break;
                case 'move':
                    // Implement move logic
                    break;
                case 'duplicate':
                    // Implement duplicate logic
                    break;
            }

            closeAllDropdowns();
        });
    });

    // View Mode Toggle
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    if (gridBtn && listBtn && gridView && listView) {
        gridBtn.addEventListener('click', function() {
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');
            gridView.classList.remove('hidden');
            listView.classList.add('hidden');
            // Clear selection when switching views
            selectedItems.clear();
            updateSelectionUI();
        });

        listBtn.addEventListener('click', function() {
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');
            // Clear selection when switching views
            selectedItems.clear();
            updateSelectionUI();
        });
    }

    // Search functionality (if enabled)
    const searchInput = document.getElementById('folder-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.toLowerCase();

            searchTimeout = setTimeout(() => {
                const folderItems = document.querySelectorAll('[data-folder-name]');
                folderItems.forEach(item => {
                    const name = item.getAttribute('data-folder-name');
                    if (name.includes(query)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }, 300);
        });
    }

    // Prevent folder link clicks when interacting with controls
    document.querySelectorAll('.folder-link, .list-row-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Prevent navigation if clicking on checkbox or three-dot menu
            if (e.target.closest('.folder-selection') ||
                e.target.closest('.folder-actions-menu') ||
                e.target.closest('.list-actions-menu') ||
                e.target.closest('.folder-checkbox')) {
                e.preventDefault();
            }
        });
    });
});
</script>
