<?php

namespace Juniyasyos\FilamentMediaManager\Traits;

trait HasActiveIcon
{
    public static function getActiveNavigationIcon(): ?string
    {
        $icon = parent::getNavigationIcon() ?? '';

        return str($icon)
            ->replace('heroicon-o-', 'heroicon-s-')
            ->toString();
    }
}
