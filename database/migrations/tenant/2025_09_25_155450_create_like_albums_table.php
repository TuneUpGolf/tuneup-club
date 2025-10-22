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
        Schema::table('like_albums', function (Blueprint $table) {
            // Add album_id if missing
            if (!Schema::hasColumn('like_albums', 'album_id')) {
                $table->unsignedBigInteger('album_id')->after('id');
                $table->foreign('album_id')->references('id')->on('albums')->onDelete('cascade');
            }

            // Add instructor_id if missing
            if (!Schema::hasColumn('like_albums', 'instructor_id')) {
                $table->unsignedBigInteger('instructor_id')->nullable()->default(null)->after('album_id');
                $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
            }

            // Add student_id if missing
            if (!Schema::hasColumn('like_albums', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->default(null)->after('instructor_id');
            }

            // Add timestamps if missing
            if (!Schema::hasColumn('like_albums', 'created_at') && !Schema::hasColumn('like_albums', 'updated_at')) {
                $table->timestamps();
            }
        });
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
