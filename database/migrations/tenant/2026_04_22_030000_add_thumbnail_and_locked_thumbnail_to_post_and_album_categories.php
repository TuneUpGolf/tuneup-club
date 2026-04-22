<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post', function (Blueprint $table) {
            if (!Schema::hasColumn('post', 'thumbnail')) {
                $table->string('thumbnail')->nullable();
            }
            if (!Schema::hasColumn('post', 'locked_thumbnail')) {
                $table->string('locked_thumbnail')->nullable();
            }
        });

        Schema::table('album_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('album_categories', 'locked_thumbnail')) {
                $table->string('locked_thumbnail')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('post', function (Blueprint $table) {
            $table->dropColumn(['thumbnail', 'locked_thumbnail']);
        });

        Schema::table('album_categories', function (Blueprint $table) {
            $table->dropColumn('locked_thumbnail');
        });
    }
};
