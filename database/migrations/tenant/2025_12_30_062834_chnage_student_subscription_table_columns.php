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
          DB::statement("
            ALTER TABLE client_subscription_details
            MODIFY invoice_id VARCHAR(255),
            MODIFY payment_intent_id VARCHAR(255)
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            ALTER TABLE client_subscription_details
            MODIFY invoice_id VARCHAR(191),
            MODIFY payment_intent_id VARCHAR(191)
        ");
    }
};
