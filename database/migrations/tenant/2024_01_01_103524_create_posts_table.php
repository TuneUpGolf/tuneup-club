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
        Schema::create('post', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instructor_id')->nullable()->default(null);
            $table->unsignedBigInteger('follower_id')->nullable()->default(null);
            $table->string('title');
            $table->text('short_description');
            $table->longText('description');
            $table->string('file')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('paid')->default(false);
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('isFollowerPost')->default(false);
            $table->string('status')->default('active');
            $table->enum('file_type', ['image', 'video'])->default('image')->nullable();
            $table->timestamps();

            // Foreign key relationships
            $table->foreign('instructor_id')->references('id')->on('users')->nullable();
            $table->foreign('follower_id')->references('id')->on('followers')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
