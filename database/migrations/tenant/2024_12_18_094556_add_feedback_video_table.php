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
        Schema::create('feedback_content', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_video_id');
            $table->string('url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->enum('type', ['image', 'video'])->default('video');
            $table->foreign('purchase_video_id')->references('id')->on('purchasevideos');
            $table->timestamps();
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
    }
};
