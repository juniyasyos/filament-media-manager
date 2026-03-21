<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('folders', 'is_public')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropColumn('is_public');
            });
        }

        if (Schema::hasColumn('folders', 'has_user_access')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropColumn('has_user_access');
            });
        }

        if (Schema::hasColumn('folders', 'user_id')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('folders', 'user_type')) {
            Schema::table('folders', function (Blueprint $table) {
                $table->dropColumn('user_type');
            });
        }
    }
};
