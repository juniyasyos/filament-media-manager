<x-filament-panels::page>
    <div
        x-data="{ view: 'grid' }"
        class="space-y-6">

        {{-- Header + View Toggle --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                📁 Folder Explorer
            </h2>

            <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                <button
                    @click="view = 'grid'"
                    :class="view === 'grid' ? 'bg-white dark:bg-gray-700 shadow' : ''"
                    class="p-2 rounded-md">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                </button>

                <button
                    @click="view = 'list'"
                    :class="view === 'list' ? 'bg-white dark:bg-gray-700 shadow' : ''"
                    class="p-2 rounded-md">
                    <x-heroicon-o-bars-3 class="w-5 h-5" />
                </button>
            </div>
        </div>

        {{-- ================= SUBFOLDERS ================= --}}
        @if($folder->folders->count() > 0)
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                📂 Subfolders ({{ $folder->folders->count() }})
            </h3>

            {{-- GRID --}}
            <div
                x-show="view === 'grid'"
                x-transition
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($folder->folders as $subfolder)
                <div
                    wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                    class="group p-3 bg-white dark:bg-gray-800 rounded-xl border hover:border-primary-500 shadow-sm hover:shadow transition cursor-pointer">
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="w-14 h-14 rounded-lg flex items-center justify-center text-white mb-2"
                            style="background-color: {{ $subfolder->color ?? '#f3c623' }}">
                            @if($subfolder->icon)
                            <x-icon name="{{ $subfolder->icon }}" class="w-7 h-7" />
                            @else
                            <x-heroicon-o-folder class="w-7 h-7" />
                            @endif
                        </div>

                        <p class="text-sm font-semibold truncate w-full">
                            {{ $subfolder->name }}
                        </p>

                        <span class="text-xs text-gray-500">
                            {{ $subfolder->folders->count() }} folders · {{ $subfolder->media->count() }} files
                        </span>

                        @if($subfolder->is_protected)
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-gray-400 mt-1" />
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- LIST --}}
            <div
                x-show="view === 'list'"
                x-transition
                class="divide-y divide-gray-200 dark:divide-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-800 border">
                @foreach($folder->folders as $subfolder)
                <div
                    wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                    class="flex items-center gap-4 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                    <div
                        class="w-10 h-10 rounded-md flex items-center justify-center text-white"
                        style="background-color: {{ $subfolder->color ?? '#f3c623' }}">
                        <x-heroicon-o-folder class="w-5 h-5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="font-medium truncate">
                            {{ $subfolder->name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $subfolder->folders->count() }} folders · {{ $subfolder->media->count() }} files
                        </p>
                    </div>

                    <x-heroicon-o-chevron-right class="w-4 h-4 text-gray-400" />
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ================= FILES ================= --}}
        @if($folder->media->count() > 0)
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                📄 Files ({{ $folder->media->count() }})
            </h3>

            {{-- GRID --}}
            <div
                x-show="view === 'grid'"
                x-transition
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($folder->media as $media)
                <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border hover:shadow transition">
                    <div class="aspect-square mb-2">
                        @if(Str::startsWith($media->mime_type, 'image/'))
                        <img
                            src="{{ $media->getUrl() }}"
                            class="w-full h-full object-cover rounded-lg">
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <x-heroicon-o-document class="w-8 h-8 text-gray-500" />
                        </div>
                        @endif
                    </div>

                    <p class="text-sm truncate font-medium">
                        {{ $media->file_name }}
                    </p>

                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                        <span>{{ Str::upper($media->extension) }}</span>
                        <span>{{ number_format($media->size / 1024, 1) }} KB</span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- LIST --}}
            <div
                x-show="view === 'list'"
                x-transition
                class="divide-y divide-gray-200 dark:divide-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-800 border">
                @foreach($folder->media as $media)
                <div class="flex items-center gap-4 p-3">
                    <x-heroicon-o-document class="w-6 h-6 text-gray-500" />

                    <div class="flex-1 min-w-0">
                        <p class="truncate font-medium">
                            {{ $media->file_name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ number_format($media->size / 1024, 1) }} KB · {{ $media->created_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ $media->getUrl() }}" target="_blank">
                            <x-heroicon-o-eye class="w-5 h-5 text-primary-500" />
                        </a>

                        <a href="{{ $media->getUrl() }}" download>
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-gray-500" />
                        </a>

                        <button
                            wire:click="deleteMedia({{ $media->id }})"
                            wire:confirm="Are you sure?">
                            <x-heroicon-o-trash class="w-5 h-5 text-red-500" />
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ================= EMPTY STATE ================= --}}
        @if($folder->folders->count() === 0 && $folder->media->count() === 0)
        <div class="text-center py-12">
            <x-heroicon-o-folder-open class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                Folder kosong
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Buat subfolder atau upload file untuk memulai
            </p>
        </div>
        @endif

    </div>
</x-filament-panels::page>