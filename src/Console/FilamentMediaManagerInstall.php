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
        $this->info('🔄 Publishing required migrations...');

        // Ensure Spatie media library migration exists before running our migrations
        $mediaMigrationExists = count(File::glob(database_path('migrations/*create_media_table.php'))) > 0;
        if (! $mediaMigrationExists) {
            $this->info('✅ Publishing Spatie Media Library migrations...');
            Artisan::call('vendor:publish', [
                '--provider' => 'Spatie\\MediaLibrary\\MediaLibraryServiceProvider',
                '--tag' => 'medialibrary-migrations',
                '--force' => true,
            ]);
            $this->info(Artisan::output());
        }

        $this->info('🔄 Generating migrations from stubs...');

        // Path ke folder stub migrations - support both vendor dan local package
        $stubsPath = base_path('vendor/juniyasyos/filament-media-manager/stubs/migrations');

        // Check if package is installed locally
        if (! File::exists($stubsPath)) {
            $stubsPath = base_path('packages/juniyasyos/filament-media-manager/stubs/migrations');
        }

        $migrationPath = database_path('migrations');

        if (! File::exists($stubsPath)) {
            $this->error("❌ Stub migrations not found at: $stubsPath");
            return;
        }

        // Ambil dan urutkan stub berdasarkan nama
        $files = collect(File::glob("$stubsPath/*.stub"))->sort()->all();

        // Aktifkan timestamp migrasi berurutan di atas eksisting (sehingga dependensi foreign key valid)
        $existingMigrationTimestamps = collect(File::glob("$migrationPath/*.php"))
            ->map(function ($path) {
                $base = basename($path);
                if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_/', $base, $m)) {
                    return strtotime(str_replace('_', '-', substr($m[1], 0, 10)) . ' ' . substr($m[1], 11, 2) . ':' . substr($m[1], 13, 2) . ':' . substr($m[1], 15, 2));
                }
                return null;
            })
            ->filter()
            ->max();

        $startTimestamp = max($existingMigrationTimestamps ?? 0, now()->getTimestamp());

        foreach ($files as $file) {
            $basename = basename($file, '.stub');

            $existingMigration = collect(File::glob("$migrationPath/*_{$basename}.php"))->isNotEmpty();

            if ($existingMigration) {
                $this->warn("⚠️ Migration already exists: {$basename}.php (skipping)");
                continue;
            }

            $startTimestamp++;
            $newFilename = date('Y_m_d_His', $startTimestamp) . "_{$basename}.php";
            $newFilePath = "$migrationPath/$newFilename";

            // Salin stub ke dalam folder migrations
            File::copy($file, $newFilePath);
            $this->info("✅ Created Migration: $newFilename");
        }

        // Ensure generated migrations are safe to run multiple times (skip if tables/columns already exist)
        $this->makeMigrationsIdempotent($migrationPath);

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

    protected function makeMigrationsIdempotent(string $migrationPath): void
    {
        $patches = [
            // Create table migrations
            [
                'old' => <<<'OLD'
    public function up()
    {
        Schema::create('media_has_models', function (Blueprint $table) {
            $table->id();

            //Morph
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            //Folder
            $table->foreignId('media_id')->constrained('media')->onDelete('cascade');

            $table->timestamps();
        });
    }
OLD,
                'new' => <<<'NEW'
    public function up()
    {
        if (! Schema::hasTable('media_has_models')) {
            Schema::create('media_has_models', function (Blueprint $table) {
                $table->id();

                //Morph
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');

                //Folder
                $table->foreignId('media_id')->constrained('media')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }
NEW,
            ],
            [
                'old' => <<<'OLD'
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();

            //Morph
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            //Folder
            $table->string('name')->index();
            $table->string('collection')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            //Options
            $table->boolean('is_protected')->default(false)->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_hidden')->default(false)->nullable();
            $table->boolean('is_favorite')->default(false)->nullable();

            $table->timestamps();
        });
    }
OLD,
                'new' => <<<'NEW'
    public function up()
    {
        if (! Schema::hasTable('folders')) {
            Schema::create('folders', function (Blueprint $table) {
                $table->id();

                //Morph
                $table->string('model_type')->nullable();
                $table->unsignedBigInteger('model_id')->nullable();

                //Folder
                $table->string('name')->index();
                $table->string('collection')->nullable()->index();
                $table->string('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color')->nullable();

                //Options
                $table->boolean('is_protected')->default(false)->nullable();
                $table->string('password')->nullable();
                $table->boolean('is_hidden')->default(false)->nullable();
                $table->boolean('is_favorite')->default(false)->nullable();

                $table->timestamps();
            });
        }
    }
NEW,
            ],
            [
                'old' => <<<'OLD'
    public function up()
    {
        Schema::create('folder_has_models', function (Blueprint $table) {
            $table->id();

            //Morph
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            //Folder
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');

            $table->timestamps();
        });
    }
OLD,
                'new' => <<<'NEW'
    public function up()
    {
        if (! Schema::hasTable('folder_has_models')) {
            Schema::create('folder_has_models', function (Blueprint $table) {
                $table->id();

                //Morph
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');

                //Folder
                $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }
NEW,
            ],
            // Spatie media table safeguard
            [
                'old' => <<<'OLD'
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
OLD,
                'new' => <<<'NEW'
    public function up()
    {
        if (! Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
NEW,
            ],
            [
                'old' => "            });\n    }\n",
                'new' => "            });\n        }\n    }\n",
            ],
            // Rename drop table mistake
            [
                'old' => "Schema::dropIfExists('media_has_models')",
                'new' => "Schema::dropIfExists('folder_has_models')",
            ],
            // Update folders table: add column guards
            [
                'old' => <<<'OLD'
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->nullable();
            $table->boolean('has_user_access')->default(false)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type')->nullable();
        });
    }
OLD,
                'new' => <<<'NEW'
    public function up()
    {
        if (! Schema::hasColumn('folders', 'is_public')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->boolean('is_public')->default(true)->nullable();
            });
        }

        if (! Schema::hasColumn('folders', 'has_user_access')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->boolean('has_user_access')->default(false)->nullable();
            });
        }

        if (! Schema::hasColumn('folders', 'user_id')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }

        if (! Schema::hasColumn('folders', 'user_type')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->string('user_type')->nullable();
            });
        }
    }
NEW,
            ],
            // Add parent_id column guard
            [
                'old' => <<<'OLD'
    public function up()
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('folders')->onDelete('cascade');
        });
    }
OLD,
                'new' => <<<'NEW'
    public function up()
    {
        if (! Schema::hasColumn('folders', 'parent_id')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('folders')->onDelete('cascade');
            });
        }
    }
NEW,
            ],
        ];

        $files = File::glob("{$migrationPath}/*.php");

        foreach ($files as $file) {
            $content = File::get($file);
            $original = $content;

            foreach ($patches as $patch) {
                if (str_contains($content, $patch['old']) && ! str_contains($content, $patch['new'])) {
                    $content = str_replace($patch['old'], $patch['new'], $content);
                }
            }

            if ($content !== $original) {
                File::put($file, $content);
            }
        }
    }
}
