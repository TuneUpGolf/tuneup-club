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
        Schema::table('purchasevideos', function () {
            DB::statement("ALTER TABLE purchasevideos MODIFY note LONGTEXT NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchasevideos', function () {
            DB::statement("ALTER TABLE purchasevideos MODIFY bio VARCHAR(255) NULL");
        });
    }
};
