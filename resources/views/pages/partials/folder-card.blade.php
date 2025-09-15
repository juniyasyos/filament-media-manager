@php
    $folderIcon = $item->icon ?? 'heroicon-o-folder';
    $folderColor = $item->color ?? '#3B82F6';
    $isProtected = $item->is_protected ?? false;
    $hasChildren = $item->folders()->exists();
    $mediaCount = $item->media()->count();
    $subFolderCount = $item->folders()->count();
@endphp

<div class="gdrive-folder-card group relative bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 ease-in-out hover:shadow-lg hover:-translate-y-1 overflow-hidden">
    <!-- Folder Icon Header -->
    <div class="p-4 pb-2 flex items-start justify-between">
        <div class="flex-shrink-0 relative">
            <!-- Custom Folder Icon with Color -->
            <div class="relative">
                <svg class="w-12 h-12 transition-transform duration-200 group-hover:scale-110" style="color: {{ $folderColor }}" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2h-8l-2-2z"/>
                </svg>

                <!-- Protection Badge -->
                @if($isProtected)
                    <div class="absolute -top-1 -right-1 w-5 h-5 bg-amber-100 dark:bg-amber-900 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Menu -->
        <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <button class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Folder Content -->
    <div class="px-4 pb-4">
        <!-- Folder Name -->
        <h3 class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
            {{ $item->name }}
        </h3>

        <!-- Folder Stats -->
        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 space-x-3">
            @if($hasChildren)
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                    {{ $subFolderCount }} folder{{ $subFolderCount !== 1 ? 's' : '' }}
                </span>
            @endif

            @if($mediaCount > 0)
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                    </svg>
                    {{ $mediaCount }} file{{ $mediaCount !== 1 ? 's' : '' }}
                </span>
            @endif

            @if(!$hasChildren && $mediaCount === 0)
                <span class="text-gray-400">Empty</span>
            @endif
        </div>

        <!-- Folder Path/Description -->
        @if($item->description)
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 line-clamp-2">
                {{ $item->description }}
            </p>
        @endif
    </div>

    <!-- Hover Action Bar -->
    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-gray-50 dark:from-gray-800 to-transparent p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-200 ease-in-out">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                Modified {{ $item->updated_at?->diffForHumans() ?? 'recently' }}
            </span>

            <div class="flex items-center space-x-1">
                <!-- Quick Actions -->
                <button class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors" title="Share">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                    </svg>
                </button>

                <button class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 rounded transition-colors" title="Download">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Selection Checkbox (hidden by default, shown on hover or when selected) -->
    <div class="absolute top-3 left-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <input type="checkbox" class="w-4 h-4 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
    </div>
</div>
