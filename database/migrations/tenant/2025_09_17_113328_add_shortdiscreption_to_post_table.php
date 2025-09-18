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
        if (Schema::hasColumn('post', 'description')) {
            Schema::table('post', function (Blueprint $table) {
                $table->longText('description')->nullable()->change();
            });
        }

        if (!Schema::hasColumn('post', 'short_description')) {
            Schema::table('post', function (Blueprint $table) {
                $table->longText('short_description')->nullable();
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
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'short_description')) {
                $table->dropColumn('short_description');
            }

            // Restore 'lesson_description' to NOT NULL if needed
            $table->longText('lesson_description')->nullable(false)->change();
        });
    }
};
