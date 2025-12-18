<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->double('price_quarter', 8, 2)->nullable()->after('price');
            $table->double('price_year', 8, 2)->nullable()->after('price_quarter');
        });

        DB::statement("ALTER TABLE plans MODIFY durationtype VARCHAR(255) NULL");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'price_quarter',
                'price_year'
            ]);
        });

        DB::statement("ALTER TABLE plans MODIFY durationtype VARCHAR(255) NOT NULL");

    }
};
