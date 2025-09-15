
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
                <!-- View Mode Toggle -->
                <div class="view-toggle-container">
                    <button class="view-toggle-btn {{ $viewMode === 'grid' ? 'active' : '' }}" data-view="grid" id="grid-view-btn">
                        <x-filament::icon icon="heroicon-o-squares-2x2" />
                    </button>
                    <button class="view-toggle-btn {{ $viewMode === 'list' ? 'active' : '' }}" data-view="list" id="list-view-btn">
                        <x-filament::icon icon="heroicon-o-bars-3" />
                    </button>
                </div>

                <!-- Select All -->
                {{-- <button class="select-all-btn">Select all</button> --}}
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

                        <a href="{{ $url }}" class="folder-link" data-folder-name="{{ strtolower($item->name) }}">
                            @include('filament-media-manager::pages.partials.folder-card', ['item' => $item])
                        </a>
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

                        <a href="{{ $url }}" class="list-row" data-folder-name="{{ strtolower($item->name) }}">
                            <!-- Name with Icon -->
                            <div class="list-col">
                                <div class="list-item-info">
                                    <input type="checkbox" class="mm-checkbox">
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
        });

        listBtn.addEventListener('click', function() {
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');
        });
    }

    // Search functionality
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
});
</script>
