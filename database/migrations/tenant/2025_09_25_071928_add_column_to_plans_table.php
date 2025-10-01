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
        Schema::table('plans', function (Blueprint $table) {
            if (!Schema::hasColumn('plans', 'stripe_product_id')) {
                $table->string('stripe_product_id')->nullable()->after('tenant_id');
            }

            if (!Schema::hasColumn('plans', 'stripe_price_id')) {
                $table->string('stripe_price_id')->nullable()->after('stripe_product_id');
            }
        });
    }


    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'stripe_product_id')) {
                $table->dropColumn('stripe_product_id');
            }
            if (Schema::hasColumn('plans', 'stripe_price_id')) {
                $table->dropColumn('stripe_price_id');
            }
        });
    }
};
