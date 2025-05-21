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
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('is_chat_enabled')->default(false);
            $table->boolean('is_feed_enabled')->default(false);
            $table->unsignedBigInteger('influencer_id')->nullable();
            $table->foreign('influencer_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('is_chat_enabled');
            $table->dropColumn('is_feed_enabled');
            $table->dropColumn('influencer_id');
        });
    }
};
