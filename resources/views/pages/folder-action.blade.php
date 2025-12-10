<div class="relative group">
    <x-filament-actions::action :action="$action" :badge="$getBadge()" :badge-color="$getBadgeColor()" dynamic-component="filament::button"
        :label="$getLabel()" :size="$getSize()" class="fi-ac-icon-btn-action" color="gray">
        <style>
            .folder-icon- {
                    {
                    $item->id
                }
            }

                {
                width: 100px;
                height: 70px;

                background-color: {
                        {
                        $item->color ?? '#f3c623'
                    }
                }

                ;
                border-radius: 5px;
                position: relative;
                margin-top: 20px;
                margin-right: 10px;
                margin-left: 10px;
                margin-bottom: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .folder-icon- {
                    {
                    $item->id
                }
            }

            ::before {
                content: "";
                width: 40px;
                height: 10px;

                background-color: {
                        {
                        $item->color ?? '#f3c623'
                    }
                }

                ;
                border-radius: 5px 5px 0 0;
                position: absolute;
                top: -10px;
                left: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        </style>
        <div class="flex flex-col justify-center items-center gap-4">
            <div class="folder-icon-{{ $item->id }} flex flex-col items-center justify-center">
                @if ($item->icon)
                <x-icon name="{{ $item->icon }}" class="text-white w-8 h-8" />
                @endif
            </div>
            <div class="flex flex-col items-center justify-center my-2">
                <div class="flex items-center gap-2">
                    <h1 class="font-bold text-xl">{{ $item->name }}</h1>
                    @if($item->folders()->count() > 0)
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-blue-500 rounded-full">
                        {{ $item->folders()->count() }}
                    </span>
                    @endif
                </div>

                <div class="flex justify-start mt-1 gap-2">
                    <p class="text-gray-600 dark:text-gray-300 text-sm truncate ...">
                        {{ $item->created_at->diffForHumans() }}
                    </p>
                    @if($item->parent)
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        • in {{ $item->parent->name }}
                    </span>
                    @endif
                </div>

            </div>
        </div>
    </x-filament-actions::action>

    {{-- Folder Actions - Shown on hover --}}
    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-1">
        {{ ($this->createSubfolderAction($item))(['folder' => $item]) }}
    </div>
</div>