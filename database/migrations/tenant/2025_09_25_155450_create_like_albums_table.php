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
        if (!Schema::hasTable('like_albums')) {
            // Create the table if it doesn't exist
            Schema::create('like_albums', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('album_id');
                $table->unsignedBigInteger('instructor_id')->nullable()->default(null);
                $table->unsignedBigInteger('student_id')->nullable()->default(null);
                $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
                $table->timestamps();
            });
        } else {
            // Update the table if it already exists
            Schema::table('like_albums', function (Blueprint $table) {
                if (!Schema::hasColumn('like_albums', 'album_id')) {
                    $table->unsignedBigInteger('album_id')->after('id');
                    $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
                }

                if (!Schema::hasColumn('like_albums', 'instructor_id')) {
                    $table->unsignedBigInteger('instructor_id')->nullable()->default(null)->after('album_id');
                    $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
                }

                if (!Schema::hasColumn('like_albums', 'student_id')) {
                    $table->unsignedBigInteger('student_id')->nullable()->default(null)->after('instructor_id');
                }

                if (!Schema::hasColumn('like_albums', 'created_at') || !Schema::hasColumn('like_albums', 'updated_at')) {
                    $table->timestamps();
                }
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
        Schema::dropIfExists('like_albums');
    }
};
