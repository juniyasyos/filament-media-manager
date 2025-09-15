@php
    $folderIcon = $item->icon ?? 'heroicon-o-folder';
    $folderColor = $item->color ?? '#3B82F6';
    $isProtected = $item->is_protected ?? false;
    $hasChildren = $item->folders()->exists();
    $mediaCount = $item->media()->count();
    $subFolderCount = $item->folders()->count();
@endphp

<div class="folder-card">
    <div class="folder-icon-container">
        <div class="folder-icon" style="color: {{ $folderColor }}">
            <x-filament::icon :icon="$folderIcon" class="folder-main-icon" />

            @if($isProtected)
                <div class="protection-badge">
                    <x-filament::icon icon="heroicon-o-lock-closed" class="protection-icon" />
                </div>
            @endif

            @if($hasChildren && $subFolderCount > 0)
                <div class="count-badge">{{ $subFolderCount }}</div>
            @endif
        </div>
    </div>

    <div class="folder-info">
        <h3 class="folder-name">{{ $item->name }}</h3>

        <div class="folder-stats">
            @if($hasChildren)
                <span class="stat-item">
                    <x-filament::icon icon="heroicon-o-folder" class="stat-icon" />
                    {{ $subFolderCount }} folder{{ $subFolderCount !== 1 ? 's' : '' }}
                </span>
            @endif

            @if($mediaCount > 0)
                <span class="stat-item">
                    <x-filament::icon icon="heroicon-o-document" class="stat-icon" />
                    {{ $mediaCount }} file{{ $mediaCount !== 1 ? 's' : '' }}
                </span>
            @endif

            @if(!$hasChildren && $mediaCount === 0)
                <span class="stat-item empty">Empty folder</span>
            @endif
        </div>

        @if($item->description)
            <p class="folder-description">{{ $item->description }}</p>
        @endif

        <div class="folder-meta">
            <span>Modified {{ $item->updated_at?->diffForHumans() ?? 'recently' }}</span>
        </div>
    </div>
</div>
