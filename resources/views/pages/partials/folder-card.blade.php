@php
    $folderColor = $item->color ?? '#3B82F6'; // Default blue color
    $hasSubfolders = $item->folders()->exists();
    $itemCount = $item->folders()->count();
@endphp

<div class="flex flex-col items-center gap-3 p-4 w-full h-full min-h-[120px] hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors">
    <!-- Folder Icon with consistent styling -->
    <div class="relative">
        <div class="folder-icon-{{ $item->id }}"></div>

        <!-- Folder content indicator -->
        @if($hasSubfolders && $itemCount > 0)
            <div class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                {{ $itemCount }}
            </div>
        @endif

        <!-- Lock icon for protected folders -->
        @if($item->is_protected)
            <div class="absolute -bottom-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
        @endif
    </div>

    <!-- Folder Name -->
    <div class="text-center">
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate max-w-[120px]" title="{{ $item->name }}">
            {{ $item->name }}
        </h3>

        <!-- Folder Info -->
        <div class="flex flex-col items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
            @if($item->description)
                <p class="truncate max-w-[120px]" title="{{ $item->description }}">
                    {{ $item->description }}
                </p>
            @endif

            @if($hasSubfolders)
                <span>{{ $itemCount }} {{ $itemCount === 1 ? 'folder' : 'folders' }}</span>
            @else
                <span>Media folder</span>
            @endif

            @if($item->depth > 0)
                <span class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full mt-1">
                    Level {{ $item->depth }}
                </span>
            @endif
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .folder-icon-{{ $item->id }} {
                width: 80px;
                height: 60px;
                background-color: {{ $folderColor }};
                border-radius: 8px;
                position: relative;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                transition: all 0.2s ease;
            }

            .folder-icon-{{ $item->id }}:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .folder-icon-{{ $item->id }}::before {
                content: "";
                width: 30px;
                height: 8px;
                background-color: {{ $folderColor }};
                border-radius: 4px 4px 0 0;
                position: absolute;
                top: -8px;
                left: 8px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            }

            .folder-icon-{{ $item->id }}::after {
                content: "📁";
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 20px;
                opacity: 0.8;
            }
        </style>
    @endpush
@endonce
