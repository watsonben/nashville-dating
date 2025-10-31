<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MagicLinkController extends Controller
{
    /**
     * Show the magic link request form.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/MagicLink');
    }

    /**
     * Send a magic link to the user's email.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            // For security, don't reveal if email exists
            return back()->with('status', 'If that email address is in our system, we\'ve sent you a magic link!');
        }

        // Delete any existing tokens for this email
        DB::table('magic_login_tokens')
            ->where('email', $request->email)
            ->delete();

        // Generate a unique token
        $token = Str::random(64);

        // Store the token
        DB::table('magic_login_tokens')->insert([
            'email' => $request->email,
            'token' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);

        // Send the magic link email
        $magicLink = url('/magic-link/verify?token='.$token.'&email='.urlencode($request->email));

        Mail::to($request->email)->send(new \App\Mail\MagicLinkMail($magicLink));

        return back()->with('status', 'If that email address is in our system, we\'ve sent you a magic link!');
    }

    /**
     * Verify the magic link and log the user in.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        // Find the token
        $tokenRecord = DB::table('magic_login_tokens')
            ->where('email', $request->email)
            ->where('token', hash('sha256', $request->token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $tokenRecord) {
            throw ValidationException::withMessages([
                'email' => ['This magic link is invalid or has expired.'],
            ]);
        }

        // Delete the used token
        DB::table('magic_login_tokens')
            ->where('email', $request->email)
            ->delete();

        // Find and log in the user
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
