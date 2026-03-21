@php
$targetUrl = null;
$hasSubfolders = $item->folders()->exists();

if (filament('filament-media-manager')->allowSubFolders || $hasSubfolders) {
// navigate to folder view using UUID route key
$targetUrl = \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('view', ['folder' => $item]);
} else {
// navigate to folder media using UUID route key
$targetUrl = \Juniyasyos\FilamentMediaManager\Resources\MediaResource::getUrl('index', ['folder' => $item]);
}
@endphp

<div class="folder-card"
    style="position: relative; display: block; width: 100%; height:100%;"
    onmouseenter="this.querySelector('.actions').style.opacity='1'"
    onmouseleave="this.querySelector('.actions').style.opacity='0'">

    <a
        href="{{ $targetUrl }}"
        class="folder-card-link"
        style="
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            min-height: 180px;
            text-align: left;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: inherit;
            text-decoration: none;
        "
        onmouseover="this.style.boxShadow='0 8px 20px rgba(0,0,0,0.15)'"
        onmouseout="this.style.boxShadow='none'">

        <div style="display: flex; flex-direction: column; align-items: center; gap: 16px; flex:1;">
            <!-- ICON -->
            <div class="folder-icon-{{ $item->id }}"
                style="display:flex; align-items:center; justify-content:center;">
                @if ($item->icon)
                <x-icon name="{{ $item->icon }}" style="width:32px;height:32px;" />
                @endif
            </div>

            <!-- TEXT -->
            <div style="text-align: center; margin: 8px 0;">

                <!-- TITLE -->
                <div style="display:flex; align-items:center; justify-content:center; gap:8px;">
                    <h1 style="font-size:18px; font-weight:700; margin:0;" class="title">
                        {{ $item->name }}
                    </h1>

                    @if($item->folders()->count() > 0)
                    <span style="
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            padding:2px 8px;
                            font-size:12px;
                            font-weight:700;
                            color:white;
                            background:#3b82f6;
                            border-radius:999px;
                        ">
                        {{ $item->folders()->count() }}
                    </span>
                    @endif
                </div>

                <!-- META -->
                <div style="margin-top:6px; font-size:13px;" class="meta">
                    <span>
                        {{ $item->created_at->diffForHumans() }}
                    </span>

                    @if($item->parent)
                    <span class="parent" style="margin-left:6px;">
                        • in {{ $item->parent->name }}
                    </span>
                    @endif
                </div>

            </div>
        </div>
    </a>

    <!-- ACTION -->
    <div class="actions"
        style="
            position:absolute;
            top:8px;
            right:8px;
            z-index: 60;
            opacity:0;
            transition:opacity 0.2s ease;
            display:flex;
            gap:4px;
        "
        onclick="event.stopPropagation()">
        <a href="{{ \Juniyasyos\FilamentMediaManager\Resources\FolderResource::getUrl('view', ['folder' => $item]) }}"
            style="display:inline-flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; background:#10b981; border-radius:999px; padding:4px 8px; text-decoration:none;">
            Buka Folder
        </a>
    </div>

</div>


<script>
    (function() {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        const wrappers = document.querySelectorAll('.folder-card');

        wrappers.forEach(wrapper => {
            const btn = wrapper.querySelector('button');
            if (!btn) return;

            // 🎨 Dark mode
            if (isDark) {
                btn.style.background = '#1f2937';
                btn.style.borderColor = '#374151';
                btn.style.color = '#f9fafb';

                btn.querySelectorAll('.meta').forEach(el => el.style.color = '#d1d5db');
                btn.querySelectorAll('.parent').forEach(el => el.style.color = '#9ca3af');
            } else {
                btn.style.background = '#ffffff';
                btn.style.borderColor = '#e5e7eb';
                btn.style.color = '#111827';

                btn.querySelectorAll('.meta').forEach(el => el.style.color = '#6b7280');
                btn.querySelectorAll('.parent').forEach(el => el.style.color = '#9ca3af');
            }
        });

        // 🔥 Equal height khusus folder-card
        function equalHeight() {
            let maxHeight = 0;

            wrappers.forEach(wrapper => {
                const btn = wrapper.querySelector('button');
                if (!btn) return;

                btn.style.height = 'auto';
                if (btn.offsetHeight > maxHeight) {
                    maxHeight = btn.offsetHeight;
                }
            });

            wrappers.forEach(wrapper => {
                const btn = wrapper.querySelector('button');
                if (!btn) return;

                btn.style.height = maxHeight + 'px';
            });
        }

        window.addEventListener('load', equalHeight);
        window.addEventListener('resize', equalHeight);
    })();
</script>