<x-filament-panels::page>
    <div
        x-data="{ view: 'grid' }"
        class="space-y-6">

        {{-- Header + View Toggle --}}
        <div class="flex items-center justify-end mb-6">
            <div class="flex items-center gap-2">
                <div class="flex gap-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-1 rounded-lg shadow-sm">
                    <button
                        @click="view = 'grid'"
                        :class="view === 'grid' ? 'bg-primary-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="p-2 rounded-md transition-all duration-200">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                    </button>

                    <button
                        @click="view = 'list'"
                        :class="view === 'list' ? 'bg-primary-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="p-2 rounded-md transition-all duration-200">
                        <x-heroicon-o-bars-3 class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        {{-- ================= SUBFOLDERS ================= --}}
        @if($folder->folders->count() > 0)
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-folder class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                    Subfolders
                </h3>
                <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full">
                    {{ $folder->folders->count() }}
                </span>
            </div>

            {{-- GRID VIEW --}}
            <div
                x-show="view === 'grid'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($folder->folders as $subfolder)
                <div
                    wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                    class="group relative p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer">

                    {{-- Protected Badge --}}
                    @if($subfolder->is_protected)
                    <div class="absolute top-2 right-2 p-1.5 bg-warning-100 dark:bg-warning-900/30 rounded-lg">
                        <x-heroicon-o-lock-closed class="w-3.5 h-3.5 text-warning-600 dark:text-warning-400" />
                    </div>
                    @endif

                    <div class="flex flex-col items-center text-center space-y-3">
                        {{-- Icon --}}
                        <div
                            class="w-16 h-16 rounded-xl flex items-center justify-center text-white shadow-lg transition-transform group-hover:scale-110"
                            style="background: linear-gradient(135deg, {{ $subfolder->color ?? '#3b82f6' }} 0%, {{ $subfolder->color ?? '#2563eb' }} 100%);">
                            @if($subfolder->icon)
                            <x-icon name="{{ $subfolder->icon }}" class="w-8 h-8" />
                            @else
                            <x-heroicon-o-folder class="w-8 h-8" />
                            @endif
                        </div>

                        {{-- Name --}}
                        <div class="w-full space-y-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $subfolder->name }}
                            </p>

                            {{-- Stats --}}
                            <div class="flex items-center justify-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-folder class="w-3.5 h-3.5" />
                                    <span>{{ $subfolder->folders->count() }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-document class="w-3.5 h-3.5" />
                                    <span>{{ $subfolder->media->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- LIST VIEW --}}
            <div
                x-show="view === 'list'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                @foreach($folder->folders as $subfolder)
                <div
                    wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                    class="group flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0">

                    {{-- Icon --}}
                    <div
                        class="w-12 h-12 rounded-lg flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105"
                        style="background: linear-gradient(135deg, {{ $subfolder->color ?? '#3b82f6' }} 0%, {{ $subfolder->color ?? '#2563eb' }} 100%);">
                        @if($subfolder->icon)
                        <x-icon name="{{ $subfolder->icon }}" class="w-6 h-6" />
                        @else
                        <x-heroicon-o-folder class="w-6 h-6" />
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-900 dark:text-white truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                {{ $subfolder->name }}
                            </p>
                            @if($subfolder->is_protected)
                            <x-heroicon-o-lock-closed class="w-4 h-4 text-warning-500 flex-shrink-0" />
                            @endif
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-folder class="w-3.5 h-3.5" />
                                <span>{{ $subfolder->folders->count() }} folders</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-document class="w-3.5 h-3.5" />
                                <span>{{ $subfolder->media->count() }} files</span>
                            </div>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <x-heroicon-o-chevron-right class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors flex-shrink-0" />
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ================= FILES ================= --}}
        @if($folder->media->count() > 0)
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-document class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                    Files
                </h3>
                <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 rounded-full">
                    {{ $folder->media->count() }}
                </span>
            </div>

            {{-- GRID VIEW --}}
            <div
                x-show="view === 'grid'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3">
                @foreach($folder->media as $media)
                <div class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-primary-500 dark:hover:border-primary-500 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">

                    {{-- Thumbnail --}}
                    <div class="relative h-32 bg-gray-50 dark:bg-gray-900">
                        @if(Str::startsWith($media->mime_type, 'image/'))
                        <img
                            src="{{ $media->getUrl() }}"
                            alt="{{ $media->file_name }}"
                            class="w-full h-full object-contain p-1 group-hover:scale-105 transition-transform duration-200">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="text-center space-y-1">
                                @php
                                $extension = Str::upper($media->extension);
                                $iconColor = match($extension) {
                                'PDF' => 'text-red-500',
                                'DOC', 'DOCX' => 'text-blue-500',
                                'XLS', 'XLSX' => 'text-green-500',
                                'PPT', 'PPTX' => 'text-orange-500',
                                'TXT' => 'text-gray-500',
                                default => 'text-purple-500'
                                };
                                @endphp
                                <x-heroicon-o-document class="w-10 h-10 mx-auto {{ $iconColor }}" />
                                <span class="block text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-wide">
                                    {{ $extension }}
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- Actions Overlay --}}
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                            <a
                                href="{{ $media->getUrl() }}"
                                target="_blank"
                                class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors"
                                onclick="event.stopPropagation()">
                                <x-heroicon-o-eye class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                            </a>

                            <a
                                href="{{ $media->getUrl() }}"
                                download
                                class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors"
                                onclick="event.stopPropagation()">
                                <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-gray-700 dark:text-gray-300" />
                            </a>

                            <button
                                wire:click="deleteMedia({{ $media->id }})"
                                wire:confirm="Are you sure you want to delete this file?"
                                class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-lg hover:bg-white dark:hover:bg-gray-800 transition-colors"
                                onclick="event.stopPropagation()">
                                <x-heroicon-o-trash class="w-5 h-5 text-danger-600" />
                            </button>
                        </div>
                    </div>

                    {{-- Info --}}
                    <div class="p-2 space-y-1">
                        <p class="text-xs font-medium text-gray-900 dark:text-white line-clamp-1 leading-tight" title="{{ $media->file_name }}">
                            {{ $media->file_name }}
                        </p>

                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="text-xs font-medium">
                                {{ number_format($media->size / 1024, 0) }}KB
                            </span>
                            <span class="text-xs opacity-75">
                                {{ $media->created_at->format('M j') }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- LIST VIEW --}}
            <div
                x-show="view === 'list'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm">
                @foreach($folder->media as $media)
                <div class="group flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-0">

                    {{-- Thumbnail --}}
                    <div class="flex-shrink-0 w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-900">
                        @if(Str::startsWith($media->mime_type, 'image/'))
                        <img
                            src="{{ $media->getUrl() }}"
                            alt="{{ $media->file_name }}"
                            class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full flex items-center justify-center">
                            <x-heroicon-o-document class="w-6 h-6 text-gray-400" />
                        </div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white truncate">
                            {{ $media->file_name }}
                        </p>
                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-medium uppercase">{{ Str::upper($media->extension) }}</span>
                            <span>{{ number_format($media->size / 1024, 1) }} KB</span>
                            <span>{{ $media->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a
                            href="{{ $media->getUrl() }}"
                            target="_blank"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <x-heroicon-o-eye class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </a>

                        <a
                            href="{{ $media->getUrl() }}"
                            download
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                        </a>

                        <button
                            wire:click="deleteMedia({{ $media->id }})"
                            wire:confirm="Are you sure you want to delete this file?"
                            class="p-2 hover:bg-danger-50 dark:hover:bg-danger-900/20 rounded-lg transition-colors">
                            <x-heroicon-o-trash class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ================= EMPTY STATE ================= --}}
        @if($folder->folders->count() === 0 && $folder->media->count() === 0)
        <div class="flex flex-col items-center justify-center py-16 px-4">
            <div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <x-heroicon-o-folder-open class="w-10 h-10 text-gray-400" />
            </div>

            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Folder is Empty
            </h3>

            <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-sm mb-6">
                This folder doesn't contain any files or subfolders yet. Get started by creating a subfolder or uploading files using the actions above.
            </p>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <x-heroicon-o-folder-plus class="w-4 h-4 text-gray-500" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">Create Subfolder</span>
                </div>

                <div class="flex items-center gap-2 px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4 text-gray-500" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">Upload File</span>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>