<?php

namespace Juniyasyos\FilamentMediaManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class FilamentMediaManagerInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-media-manager:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Filament Media Manager package and publish assets.';

    public function handle()
    {
        $this->info('🔄 Generating migrations from stubs...');

        // Path ke folder stub migrations - support both vendor and local package
        $stubsPath = base_path('vendor/juniyasyos/filament-media-manager/stubs/migrations');

        // Check if package is installed locally
        if (!File::exists($stubsPath)) {
            $stubsPath = base_path('packages/juniyasyos/filament-media-manager/stubs/migrations');
        }

        $migrationPath = database_path('migrations');

        if (!File::exists($stubsPath)) {
            $this->error("❌ Stub migrations not found at: $stubsPath");
            return;
        }

        // Ambil dan urutkan stub berdasarkan nama
        $files = collect(File::glob("$stubsPath/*.stub"))->sort()->all();

        foreach ($files as $file) {
            $basename = basename($file, '.stub');
            $newFilename = now()->format('Y_m_d_His') . "_{$basename}.php";
            $newFilePath = "$migrationPath/$newFilename";

            // Cek apakah migration ini sudah ada
            if (File::exists($newFilePath)) {
                $this->warn("⚠️ Migration already exists: $newFilename");
                continue;
            }

            // Salin stub ke dalam folder migrations
            File::copy($file, $newFilePath);
            $this->info("✅ Created Migration: $newFilename");

            // Tunggu 1 detik agar timestamp tidak sama
            sleep(1);
        }

        // Jalankan migrate
        $this->info('🔄 Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        // Bersihkan cache optimize
        $this->info('🧹 Clearing cache...');
        Artisan::call('optimize:clear');
        $this->info(Artisan::output());

        $this->info('🎉 Filament Media Manager installed successfully.');
    }
}
