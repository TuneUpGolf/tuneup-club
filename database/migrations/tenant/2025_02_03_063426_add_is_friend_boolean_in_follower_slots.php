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
        Schema::table('follower_slots', function (Blueprint $table) {
            //
            $table->string('friend_name')->nullable()->after('follower_id');
            $table->boolean('isFriend')->default(false)->after('follower_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('follower_slots', function (Blueprint $table) {
            //
            $table->dropColumn('friend_name');
            $table->dropColumn('isFriend');
        });
    }
};
