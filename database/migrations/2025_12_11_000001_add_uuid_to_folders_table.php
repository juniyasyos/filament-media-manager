<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Generate UUID for existing folders
        $folders = DB::table('folders')->whereNull('uuid')->get();
        foreach ($folders as $folder) {
            DB::table('folders')
                ->where('id', $folder->id)
                ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
        }

        // Make UUID unique after data populated
        Schema::table('folders', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->change();
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
