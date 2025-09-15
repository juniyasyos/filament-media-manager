
@php
    $currentParent = $this->currentParent ?? null;
    $parentId = request()->get('parent_id');
@endphp

@if($currentParent || $parentId)
    <div class="mb-4 p-4">
        <div class="flex items-center gap-2">
            @if($currentParent && $currentParent->parent)
                <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index', ['parent_id' => $currentParent->parent->id]) }}" 
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to {{ $currentParent->parent->name }}
                </a>
            @elseif($currentParent)
                <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('index') }}" 
                   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Root
                </a>
            @endif
            
            @if($currentParent)
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Current folder: <span class="font-medium">{{ $currentParent->name }}</span>
                    @if($currentParent->path)
                        <br><span class="text-xs">Path: {{ $currentParent->path }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
    @foreach($records as $item)
        {{ ($this->folderAction($item))(['record' => $item]) }}
    @endforeach
</div>
