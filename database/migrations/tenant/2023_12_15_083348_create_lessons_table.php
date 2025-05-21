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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_name');
            $table->text('lesson_description');
            $table->decimal('lesson_price', 8, 2); // Adjust precision and scale as needed
            $table->string('tenant_id')->nullable();
            $table->unsignedInteger('lesson_quantity');
            $table->unsignedInteger('required_time'); // in minutes or as needed
            $table->unsignedBigInteger('created_by'); // Foreign key to influencer
            $table->string('logo_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->text('detailed_description');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lessons');
    }
};
