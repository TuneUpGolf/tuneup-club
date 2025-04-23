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
            $table->unsignedBigInteger('influencer_id')->nullable()->default(null);
            $table->unsignedBigInteger('student_id')->nullable()->default(null);
            $table->string('title');
            $table->text('short_description');
            $table->longText('description');
            $table->string('file')->nullable();
            $table->string('slug')->nullable();
            $table->boolean('paid')->default(false);
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('isStudentPost')->default(false);
            $table->string('status')->default('active');
            $table->enum('file_type', ['image', 'video'])->default('image')->nullable();
            $table->timestamps();

            // Foreign key relationships
            $table->foreign('influencer_id')->references('id')->on('users')->nullable();
            $table->foreign('student_id')->references('id')->on('students')->nullable();
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
