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
         DB::table('mail_templates')->insert([
            'mailable' => 'App\\Mail\\Admin\\ClientSubscriptionToInstructorMail',
            'subject' => 'New Subscription Purchased',
            'html_template' => '
        <p>
            Hi <strong>{{ instructor_name }}</strong>, 
            <strong>{{ student_name }}</strong> has purchased the following subscription:
        </p>

        <p>
            <strong>{{ plan_name }}</strong>.
        </p>

        <p>
            Thanks,
        </p>
    ',
            'text_template' => 'Hi {{ instructor_name }}, {{ student_name }} has purchased the following subscription: {{ plan_name }}.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instructor_mail_in_mail_templates', function (Blueprint $table) {
            //
        });
    }
};
