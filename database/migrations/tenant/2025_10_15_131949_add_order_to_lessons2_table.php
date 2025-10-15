<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        $lessons = DB::table('lessons')->get();
        foreach ($lessons as $index => $lesson) {
            DB::table('lessons')
                ->where('id', $lesson->id)
                ->update(['column_order' => $index + 1]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons2', function (Blueprint $table) {
            //
        });
    }
};
