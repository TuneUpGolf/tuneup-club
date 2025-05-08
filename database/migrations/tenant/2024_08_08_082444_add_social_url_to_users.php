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
        Schema::table('followers', function (Blueprint $table) {
            $table->string('social_url_ig')->nullable()->default(NULL);
            $table->string('social_url_fb')->nullable()->default(NULL);
            $table->string('social_url_x')->nullable()->default(NULL);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('social_url_ig');
            $table->dropColumn('social_url_fb');
            $table->dropColumn('social_url_x');
        });
    }
};
