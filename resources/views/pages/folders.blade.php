
@php
    $currentParent = $this->currentParent ?? null;
    $parentId = request()->get('parent_id');
    $viewMode = session('folder_view_mode', 'grid'); // grid or list
@endphp

@push('styles')
<style>
/* Inline Google Drive inspired styles for immediate loading */
.gdrive-folder-card {
    min-height: 160px;
    transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
}

.gdrive-folder-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
    max-height: 2.8em;
}

.view-toggle-btn {
    padding: 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    color: #6b7280;
}

.view-toggle-btn:hover {
    background-color: #f3f4f6;
    color: #374151;
}

.view-toggle-btn.active {
    background-color: #3b82f6;
    color: white;
}

.folder-grid-item, .folder-list-item {
    transition: all 0.2s ease;
}

.folder-grid-item:focus, .folder-list-item:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.folders-grid, .folders-list { animation: fadeIn 0.3s ease-in-out; }

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .view-toggle-btn:hover {
        background-color: #374151;
        color: #d1d5db;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Mode Toggle
    const viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    viewToggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewMode = this.getAttribute('data-view');

            viewToggleButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            if (viewMode === 'grid') {
                gridView?.classList.remove('hidden');
                listView?.classList.add('hidden');
            } else {
                gridView?.classList.add('hidden');
                listView?.classList.remove('hidden');
            }
        });
    });

    // Search functionality
    const searchInput = document.querySelector('input[type="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.toLowerCase();

            searchTimeout = setTimeout(() => {
                const folderItems = document.querySelectorAll('.folder-grid-item, .folder-list-item');
                folderItems.forEach(item => {
                    const name = item.querySelector('h3')?.textContent.toLowerCase() || '';
                    const desc = item.querySelector('p')?.textContent.toLowerCase() || '';

                    if (name.includes(query) || desc.includes(query)) {
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
@endpush

<!-- Google Drive Style Header -->
<div class="gdrive-header bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
    <!-- Breadcrumb Navigation -->
    @if($currentParent || $parentId)
        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2">
                @if($currentParent && $currentParent->parent)
                    <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $currentParent->parent->id]) }}"
                       class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to {{ $currentParent->parent->name }}
                    </a>
                @elseif($currentParent)
                    <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index') }}"
                       class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Root
                    </a>
                @endif

                @if($currentParent)
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                        </svg>
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $currentParent->name }}</span>
                        @if($currentParent->path)
                            <span class="ml-2 text-xs">{{ $currentParent->path }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Toolbar -->
    <div class="p-4 flex items-center justify-between">
        <!-- Left Side: Search and Filters -->
        <div class="flex items-center space-x-4">
            <!-- Search Box -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="search" placeholder="Search folders..."
                       class="block w-64 pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Sort Dropdown -->
            <div class="relative">
                <select class="appearance-none bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 pr-8 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="name">Sort by Name</option>
                    <option value="date">Sort by Date</option>
                    <option value="size">Sort by Size</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Right Side: View Toggle and Actions -->
        <div class="flex items-center space-x-3">
            <!-- View Mode Toggle -->
            <div class="flex items-center bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button class="view-toggle-btn {{ $viewMode === 'grid' ? 'active' : '' }}" data-view="grid">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button class="view-toggle-btn {{ $viewMode === 'list' ? 'active' : '' }}" data-view="list">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <!-- Select All -->
            <button class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                Select all
            </button>
        </div>
    </div>
</div>

<!-- Folders Grid/List Container -->
<div class="gdrive-content p-6">
    <!-- Grid View -->
    <div id="grid-view" class="folders-grid {{ $viewMode === 'grid' ? '' : 'hidden' }}">
        @if(count($records) > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($records as $item)
                    @php
                        $hasChildren = $item->folders()->exists();
                        $url = $hasChildren
                            ? \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $item->id])
                            : \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('media', ['folderName' => $item->name]);
                    @endphp

                    <a href="{{ $url }}" class="folder-grid-item block">
                        @include('filament-media-manager::pages.partials.folder-card', ['item' => $item])
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No folders found</h3>
                <p class="text-gray-500 dark:text-gray-400">Create your first folder to get started</p>
            </div>
        @endif
    </div>

    <!-- List View -->
    <div id="list-view" class="folders-list {{ $viewMode === 'list' ? '' : 'hidden' }}">
        @if(count($records) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- List Header -->
                <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <div class="col-span-6">Name</div>
                        <div class="col-span-2">Type</div>
                        <div class="col-span-2">Items</div>
                        <div class="col-span-2">Modified</div>
                    </div>
                </div>

                <!-- List Items -->
                @foreach($records as $item)
                    @php
                        $hasChildren = $item->folders()->exists();
                        $url = $hasChildren
                            ? \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $item->id])
                            : \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('media', ['folderName' => $item->name]);
                    @endphp

                    <a href="{{ $url }}" class="folder-list-item block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-600 last:border-b-0 transition-colors">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            <!-- Name with Icon -->
                            <div class="col-span-6 flex items-center">
                                <input type="checkbox" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded mr-3">
                                <svg class="w-6 h-6 mr-3" style="color: {{ $item->color ?? '#3B82F6' }}" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"/>
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $item->name }}</h3>
                                    @if($item->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $item->description }}</p>
                                    @endif
                                </div>
                                @if($item->is_protected)
                                    <svg class="w-4 h-4 text-amber-500 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>

                            <!-- Type -->
                            <div class="col-span-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $hasChildren ? 'Folder' : 'Media Folder' }}
                                </span>
                            </div>

                            <!-- Items Count -->
                            <div class="col-span-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($hasChildren)
                                        {{ $item->folders()->count() }} folders
                                    @else
                                        {{ $item->media()->count() }} files
                                    @endif
                                </span>
                            </div>

                            <!-- Modified Date -->
                            <div class="col-span-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item->updated_at?->diffForHumans() ?? 'Recently' }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No folders found</h3>
                <p class="text-gray-500 dark:text-gray-400">Create your first folder to get started</p>
            </div>
        @endif
    </div>
</div>
