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
        Schema::table('request_domains', function (Blueprint $table) {
            $table->string('country')->default('India')->after('password');
            $table->string('country_code')->nullable()->after('country');
            $table->string('dial_code')->nullable()->after('country_code');
            $table->text('phone')->nullable()->after('dial_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_domains', function (Blueprint $table) {
            //
        });
    }
};
