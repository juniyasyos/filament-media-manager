<x-filament-panels::page>
    <div x-data="{ view: 'list' }" style="display:flex; flex-direction:column; gap:24px;">

        <!-- HEADER -->
        <div style="display:flex; justify-content:flex-end;">
            <div class="toggle-wrapper" style="display:flex; gap:4px; padding:4px; border-radius:10px; border:1px solid;">

                <button @click="view='grid'"
                    style="padding:8px; border:none; border-radius:6px; cursor:pointer;"
                    :style="view==='grid' ? 'background:#3b82f6;color:white' : ''">
                    <x-heroicon-o-squares-2x2 style="width:20px;height:20px;" />
                </button>

                <button @click="view='list'"
                    style="padding:8px; border:none; border-radius:6px; cursor:pointer;"
                    :style="view==='list' ? 'background:#3b82f6;color:white' : ''">
                    <x-heroicon-o-bars-3 style="width:20px;height:20px;" />
                </button>

            </div>
        </div>

        <!-- ================= SUBFOLDER ================= -->
        @if($folder->folders->count() > 0)
        <div style="display:flex; flex-direction:column; gap:16px;">

            <div style="display:flex; align-items:center; gap:8px;">
                <x-heroicon-o-folder style="width:20px;height:20px;" class="icon" />
                <h3 class="title" style="font-size:12px; font-weight:600;">Subfolders</h3>
                <span class="badge" style="padding:2px 8px; border-radius:999px;">
                    {{ $folder->folders->count() }}
                </span>
            </div>

            <!-- GRID -->
            <div x-show="view==='grid'"
                style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:16px;">

                @foreach($folder->folders as $subfolder)
                <div wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                    class="card"
                    style="padding:16px; border-radius:12px; border:1px solid #e5e7eb; cursor:pointer; transition:0.2s; background:#ffffff; color:#111827;"
                    onmouseover="this.style.boxShadow='0 6px 16px rgba(0,0,0,0.1)'"
                    onmouseout="this.style.boxShadow='none'">

                    <div style="display:flex; flex-direction:column; align-items:center; gap:12px; text-align:center;">

                        <div style="width:64px;height:64px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;background:{{ $subfolder->color ?? '#3b82f6' }}">
                            <x-heroicon-o-folder style="width:28px;height:28px;" />
                        </div>

                        <div>
                            <div class="title" style="font-weight:600;">{{ $subfolder->name }}</div>
                            <div class="meta" style="font-size:12px;">
                                {{ $subfolder->folders->count() }} folders • {{ $subfolder->media->count() }} files
                            </div>
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

            <!-- LIST -->
            <div x-show="view==='list'"
                class="list-box"
                style="border-radius:12px; border:1px solid; overflow:hidden;">

                @foreach($folder->folders as $subfolder)
                <div wire:click="navigateToFolder('{{ $subfolder->uuid }}')"
                    class="list-item"
                    style="display:flex; align-items:center; gap:16px; padding:12px; border-bottom:1px solid #e5e7eb; cursor:pointer; background:#ffffff; color:#111827;">

                    <div style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;background:{{ $subfolder->color ?? '#3b82f6' }}">
                        <x-heroicon-o-folder style="width:20px;height:20px;" />
                    </div>

                    <div style="flex:1;">
                        <div class="title" style="font-weight:600;">{{ $subfolder->name }}</div>
                        <div class="meta" style="font-size:12px;">
                            {{ $subfolder->folders->count() }} folders • {{ $subfolder->media->count() }} files
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

        </div>
        @endif


        <!-- ================= FILES ================= -->
        @if(count($allMedia) > 0)
        <div style="display:flex; flex-direction:column; gap:16px;">

            <div style="display:flex; align-items:center; gap:8px;">
                <x-heroicon-o-document style="width:20px;height:20px;" class="icon" />
                <h3 class="title" style="font-size:12px; font-weight:600;">Files</h3>
                <span class="badge" style="padding:2px 8px; border-radius:999px;">
                    {{ count($allMedia) }}
                </span>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px;">

                @foreach($allMedia as $media)
                <div class="card"
                    style="border-radius:10px; border:1px solid; overflow:hidden;">

                    <div style="height:120px; display:flex; align-items:center; justify-content:center;">
                        @if(Str::startsWith($media->mime_type, 'image/'))
                        <img src="{{ $media->getUrl() }}" style="max-width:100%; max-height:100%;" />
                        @else
                        <x-heroicon-o-document style="width:30px;height:30px;" />
                        @endif
                    </div>

                    <div style="padding:8px;">
                        <div class="title" style="font-size:12px;">{{ $media->file_name }}</div>
                        <div class="meta" style="font-size:11px;">
                            {{ number_format($media->size / 1024, 0) }} KB
                        </div>
                    </div>

                </div>
                @endforeach
            </div>

        </div>
        @endif


        <!-- ================= EMPTY ================= -->
        @if($folder->folders->count() === 0 && count($allMedia) === 0)
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:40px;">

            <x-heroicon-o-folder-open style="width:40px;height:40px;" />

            <h3 class="title" style="margin-top:12px;">Folder is Empty</h3>
            <p class="meta" style="font-size:12px;">
                No files or folders yet
            </p>

        </div>
        @endif

    </div>


    <script>
        function applyTheme() {
            const isDark = document.documentElement.classList.contains('dark');

            document.querySelectorAll('.card').forEach(el => {
                el.style.background = isDark ? '#111827' : '#ffffff';
                el.style.borderColor = isDark ? '#374151' : '#e5e7eb';
                el.style.color = isDark ? '#f9fafb' : '#111827';
            });

            document.querySelectorAll('.list-item').forEach(el => {
                el.style.background = isDark ? '#111827' : '#ffffff';
                el.style.borderBottomColor = isDark ? '#374151' : '#e5e7eb';
                el.style.color = isDark ? '#f9fafb' : '#111827';
            });

            document.querySelectorAll('.title').forEach(el => {
                el.style.color = isDark ? '#f9fafb' : '#111827';
            });

            document.querySelectorAll('.meta').forEach(el => {
                el.style.color = isDark ? '#9ca3af' : '#6b7280';
            });

            document.querySelectorAll('.badge').forEach(el => {
                el.style.background = isDark ? '#374151' : '#f3f4f6';
                el.style.color = isDark ? '#d1d5db' : '#4b5563';
            });

            document.querySelectorAll('.toggle-wrapper').forEach(el => {
                el.style.background = isDark ? '#1f2937' : '#ffffff';
                el.style.borderColor = isDark ? '#374151' : '#e5e7eb';
            });
        }

        // run awal
        applyTheme();

        // observe perubahan class dark
        const observer = new MutationObserver(applyTheme);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });
    </script>

</x-filament-panels::page>