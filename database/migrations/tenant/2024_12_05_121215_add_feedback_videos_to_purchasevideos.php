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
        Schema::table('purchasevideos', function (Blueprint $table) {
            $table->string('fdbk_video_url_1')->nullable();
            $table->string('fdbk_video_url_2')->nullable();
            $table->dropColumn('feedback');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchasevideos', function (Blueprint $table) {
            //
        });
    }
};
