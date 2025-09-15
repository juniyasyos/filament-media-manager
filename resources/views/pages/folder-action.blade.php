@php
    $isDisabled = method_exists($action, 'isDisabled') ? $action->isDisabled() : false;
    $url = method_exists($action, 'getUrl') ? $action->getUrl() : null;
    $shouldPostToUrl = method_exists($action, 'shouldPostToUrl') ? $action->shouldPostToUrl() : false;
    $wireClick = method_exists($action, 'getLivewireClickHandler') ? $action->getLivewireClickHandler() : null;
    $alpineClick = method_exists($action, 'getAlpineClickHandler') ? $action->getAlpineClickHandler() : null;
@endphp

@if ($url && ! $shouldPostToUrl)
    <a href="{{ $url }}" @if($alpineClick) x-on:click="{{ $alpineClick }}" @endif target="{{ method_exists($action,'shouldOpenUrlInNewTab') && $action->shouldOpenUrlInNewTab() ? '_blank' : null }}"
       class="block w-full h-full border border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-lg transition-all duration-200 bg-white dark:bg-gray-800"
       @if($isDisabled) aria-disabled="true" @endif>
        @include('filament-media-manager::pages.partials.folder-card', ['item' => $item])
    </a>
@else
    <button type="button"
            @if($wireClick) wire:click="{{ $wireClick }}" @endif
            @if($alpineClick) x-on:click="{{ $alpineClick }}" @endif
            @if($isDisabled) disabled @endif
            class="block w-full h-full border border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-300 dark:hover:border-blue-600 hover:shadow-lg transition-all duration-200 bg-white dark:bg-gray-800">
        @include('filament-media-manager::pages.partials.folder-card', ['item' => $item])
    </button>
@endif
