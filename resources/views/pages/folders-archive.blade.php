@php
$parentId = request()->get('parent_id');
$parentFolder = $parentId ? \Juniyasyos\FilamentMediaManager\Models\Folder::find($parentId) : null;
@endphp

<div class="space-y-4">
    @if($parentFolder)
    <div class="mb-4 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <x-heroicon-o-folder class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $parentFolder->name }}
                    </h3>
                    @if($parentFolder->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $parentFolder->description }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
        @foreach($records as $item)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            {{ ($this->folderAction($item))(['record' => $item]) }}
        </div>
        @endforeach
    </div>
</div>