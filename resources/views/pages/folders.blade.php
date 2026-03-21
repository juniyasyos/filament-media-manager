@php
$parentId = request()->get('parent_id');
$parentFolder = $parentId ? \Juniyasyos\FilamentMediaManager\Models\Folder::find($parentId) : null;
@endphp

@if($parentFolder)
<div class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
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

<div class="grid-box"
    style="
        display:grid;
        grid-template-columns:3fr;
        gap:16px;
        padding:16px;
     ">

    @foreach($records as $item)
    {{ ($this->folderAction($item))(['record' => $item]) }}
    @endforeach

</div>


<script>
    (function() {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        // parent box
        document.querySelectorAll('.parent-box').forEach(box => {
            if (isDark) {
                box.style.background = '#1f2937';
                box.style.borderColor = '#374151';

                box.querySelectorAll('.title').forEach(el => el.style.color = '#f9fafb');
                box.querySelectorAll('.desc').forEach(el => el.style.color = '#d1d5db');
                box.querySelectorAll('.icon').forEach(el => el.style.color = '#9ca3af');
            } else {
                box.style.background = '#f9fafb';
                box.style.borderColor = '#e5e7eb';

                box.querySelectorAll('.title').forEach(el => el.style.color = '#111827');
                box.querySelectorAll('.desc').forEach(el => el.style.color = '#6b7280');
                box.querySelectorAll('.icon').forEach(el => el.style.color = '#6b7280');
            }
        });

        // responsive grid (md:3)
        function updateGrid() {
            document.querySelectorAll('.grid-box').forEach(grid => {
                if (window.innerWidth >= 768) {
                    grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
                } else {
                    grid.style.gridTemplateColumns = '1fr';
                }
            });
        }

        updateGrid();
        window.addEventListener('resize', updateGrid);
    })();
</script>