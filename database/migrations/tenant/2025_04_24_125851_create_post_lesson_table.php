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
    public function up(): void {
        Schema::create('post_lesson', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedBigInteger('created_by')->nullable(); // from lessons table
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('post')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('post_lesson');
    }
};
