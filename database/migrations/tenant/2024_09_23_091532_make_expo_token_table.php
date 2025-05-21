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
        Schema::create('expo_token', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('influencer_id')->nullable()->default(null);
            $table->unsignedBigInteger('follower_id')->nullable()->default(null);
            $table->string('token');
            $table->foreign('follower_id')->references('id')->on('followers')->onDelete('cascade');
            $table->foreign('influencer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('expo_token');
    }
};
