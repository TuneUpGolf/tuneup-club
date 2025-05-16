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
        if (!Schema::hasColumn('lessons', 'is_package_lesson')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->tinyInteger('is_package_lesson')->default(1);
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
        if (Schema::hasColumn('lessons', 'is_package_lesson')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->dropColumn('is_package_lesson');
            });
        }
    }
};
