<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_albums', function (Blueprint $table) {
            // Drop the old foreign key first
            $table->dropForeign(['student_id']);

            // Add the new foreign key referencing followers table
            $table->foreign('student_id')
                ->references('id')
                ->on('followers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_albums', function (Blueprint $table) {
            // Revert back to the old reference (users table)
            $table->dropForeign(['student_id']);

            $table->foreign('student_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
