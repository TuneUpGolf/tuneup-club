<?php

namespace App\Http\Controllers\Auth;

use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordResetLinkController extends Controller
{
    // use ResetsPasswords;
    public function create()
    {
        $lang = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        return view('auth.forgot-password', compact('lang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if the email exists in the `users` table
        $userExists = \App\Models\User::where('email', $request->email)->exists();

        // Check if the email exists in the `students` table
        $studentExists = \App\Models\Follower::where('email', $request->email)->exists();

        if (!$userExists && !$studentExists) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'No account found with this email address.',
            ]);
        }

        // Determine which broker to use
        $broker = $userExists ? 'users' : 'students';

        // Send the reset link using the appropriate broker
        $status = Password::broker($broker)->sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => __($status),
            ]);
        }

        return back()->with('status', __($status));
    }
}
