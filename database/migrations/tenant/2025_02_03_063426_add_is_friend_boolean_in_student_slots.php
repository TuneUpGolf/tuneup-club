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
        Schema::table('student_slots', function (Blueprint $table) {
            //
            $table->string('friend_name')->nullable()->after('student_id');
            $table->boolean('isFriend')->default(false)->after('student_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_slots', function (Blueprint $table) {
            //
            $table->dropColumn('friend_name');
            $table->dropColumn('isFriend');
        });
    }
};
