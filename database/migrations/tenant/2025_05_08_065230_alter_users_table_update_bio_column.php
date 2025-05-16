<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function () {
            DB::statement("ALTER TABLE users MODIFY bio LONGTEXT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function () {
            DB::statement("ALTER TABLE users MODIFY bio VARCHAR(255) NULL");
        });
    }
};
