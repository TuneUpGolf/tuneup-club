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
        DB::table('mail_templates')
            ->where('mailable', 'App\Mail\Admin\PurchaseCompleted')
            ->update([
                'subject' => 'Payment Confirmed - Video Submission Received',
                'html_template' => '
                    <p>Hello {{name}},</p>

                    <p>
                    Your payment has been successfully received for <strong>Lesson: {{lesson_name}}</strong>.
                    </p>

                    <p>
                    Thank you for submitting your video. Please allow some time for the influencer to review your submission and upload feedback. Once available, you will be able to view the feedback through the app.
                    </p>

                    <p>
                    Regards,<br>
                    <strong>Tuneup Management</strong>
                    </p>'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
