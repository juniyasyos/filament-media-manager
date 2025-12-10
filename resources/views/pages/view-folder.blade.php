<x-filament-panels::page>
    {{-- Subfolders Section --}}
    @if($folder->folders->count() > 0)
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            📂 Subfolders ({{ $folder->folders->count() }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($folder->folders as $subfolder)
            <div wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                class="group relative p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-all cursor-pointer border-2 border-transparent hover:border-primary-500">
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-16 rounded-lg flex items-center justify-center text-white mb-3"
                        style="background-color: {{ $subfolder->color ?? '#f3c623' }}">
                        @if($subfolder->icon)
                        <x-icon name="{{ $subfolder->icon }}" class="w-10 h-10" />
                        @else
                        <x-heroicon-o-folder class="w-10 h-10" />
                        @endif
                    </div>
                    <h4 class="font-semibold text-gray-900 dark:text-white truncate w-full">
                        {{ $subfolder->name }}
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ $subfolder->folders->count() }} folders • {{ $subfolder->media->count() }} files
                    </p>
                    @if($subfolder->is_protected)
                    <span class="absolute top-2 right-2">
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-gray-500" />
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Media Files Section --}}
    @if($folder->media->count() > 0)
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            📄 Files ({{ $folder->media->count() }})
        </h3>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Size
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Modified
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($folder->media as $media)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if(Str::startsWith($media->mime_type, 'image/'))
                                    <img class="h-10 w-10 rounded object-cover" src="{{ $media->getUrl() }}" alt="{{ $media->file_name }}">
                                    @else
                                    <div class="h-10 w-10 rounded bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <x-heroicon-o-document class="w-6 h-6 text-gray-500" />
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $media->file_name }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($media->size / 1024, 2) }} KB
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ Str::upper($media->extension) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $media->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ $media->getUrl() }}"
                                    target="_blank"
                                    class="text-primary-600 hover:text-primary-900 dark:text-primary-400">
                                    <x-heroicon-o-eye class="w-5 h-5" />
                                </a>
                                <a href="{{ $media->getUrl() }}"
                                    download
                                    class="text-gray-600 hover:text-gray-900 dark:text-gray-400">
                                    <x-heroicon-o-arrow-down-tray class="w-5 h-5" />
                                </a>
                                <button wire:click="deleteMedia({{ $media->id }})"
                                    wire:confirm="Are you sure you want to delete this file?"
                                    class="text-red-600 hover:text-red-900 dark:text-red-400">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($folder->folders->count() === 0 && $folder->media->count() === 0)
    <div class="text-center py-12">
        <x-heroicon-o-folder-open class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">This folder is empty</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Get started by creating a subfolder or uploading files.
        </p>
    </div>
    @endif
</x-filament-panels::page>