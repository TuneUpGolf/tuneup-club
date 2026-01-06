<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $template = <<<HTML
<p>&nbsp;Hi <strong>{{name}}</strong>. We are excited to welcome you to TuneUp! You can login with the following information below:</p>

<p>&nbsp;</p>

<p>Domain: <strong>{{link}}</strong></p>

<p>Username: <strong>{{email}}</strong></p>

<p>Password: <strong>{{password}}</strong></p>

<p>&nbsp;</p>

<p>If you have any questions, please reach out to our support team at 
<a href="mailto:matt@tuneup.golf">matt@tuneup.golf</a>.
</p>

<p>Thanks,</p>

<p>&nbsp;</p>
HTML;

        DB::table('mail_templates')
            ->where('mailable', 'App\Mail\Admin\WelcomeMail')
            ->update([
                'html_template' => $template
            ]);
    }

    public function down()
    {
        // Optional: restore previous template if you have it
        DB::table('mail_templates')
            ->where('mailable', 'App\Mail\Admin\WelcomeMail')
            ->update([
                'html_template' => ''
            ]);
    }
};
