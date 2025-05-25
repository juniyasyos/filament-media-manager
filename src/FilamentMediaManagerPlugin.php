<?php

namespace Juniyasyos\FilamentMediaManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Nwidart\Modules\Module;


class FilamentMediaManagerPlugin implements Plugin
{
    private bool $isActive = false;


    public ?bool $allowSubFolders = false;
    public ?bool $allowUserAccess = false;

    public function getId(): string
    {
        return 'filament-media-manager';
    }

    public function allowSubFolders(bool $condation = true): static
    {
        $this->allowSubFolders = $condation;
        return $this;
    }

    public function allowUserAccess(bool $condation = true): static
    {
        $this->allowUserAccess = $condation;
        return $this;
    }

    public function register(Panel $panel): void
    {
        // Optional: Cek apakah module aktif jika pakai nwidart
        $this->isActive = config('filament-media-manager.filament.active', true)
            && (!class_exists(\Nwidart\Modules\Facades\Module::class)
                || \Nwidart\Modules\Facades\Module::find('FilamentMediaManager')?->isEnabled());

        if ($this->isActive) {
            $panel->resources(config('filament-media-manager.filament.resources', []));
        }
    }


    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
