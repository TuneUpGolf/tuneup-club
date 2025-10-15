<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Facades\Tenancy;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get all tenants
        $tenants = Tenancy::allTenants();

        foreach ($tenants as $tenant) {
            // Switch to tenant connection
            Tenancy::initialize($tenant);

            // Make sure the tenant table exists
            if (Schema::hasTable('lessons')) {
                Schema::table('lessons', function (Blueprint $table) {
                    if (!Schema::hasColumn('lessons', 'column_order')) {
                        $table->integer('column_order')->default(1)->after('id');
                    }
                });

                // Set column_order = id for existing rows (or increment)
                DB::table('lessons')->update(['column_order' => DB::raw('id')]);
            }

            Tenancy::end(); // End tenant context
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lessons', function (Blueprint $table) {
            //
        });
    }
};
