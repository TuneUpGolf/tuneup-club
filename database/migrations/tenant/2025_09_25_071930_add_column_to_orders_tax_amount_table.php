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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->string('tax_amount')->nullable();
            }
            if (!Schema::hasColumn('orders', 'platform_amount')) {
                $table->string('platform_amount')->nullable();
            }
            if (!Schema::hasColumn('orders', 'net_amount')) {
                $table->string('net_amount')->nullable();
            }
        });
    }


    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tax_amount')) {
                $table->dropColumn('tax_amount');
            }
            if (Schema::hasColumn('orders', 'platform_amount')) {
                $table->dropColumn('platform_amount');
            }
            if (Schema::hasColumn('orders', 'net_amount')) {
                $table->dropColumn('net_amount');
            }
        });
    }
};
