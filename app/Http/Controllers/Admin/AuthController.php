<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle the login attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->status === 'disabled') {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Your account has been disabled. Please contact the shop owner.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // "Login Session Limit" — keep at most N-1 of this user's OTHER
            // active sessions (the Nth slot is this new login). Since the
            // session driver is 'database', deleting old rows here forces
            // those other logins to be treated as logged-out on their next
            // request — no extra tracking table needed.
            $limit = (int) Setting::get('login_session_limit', 0);
            if ($limit > 0) {
                $currentSessionId = $request->session()->getId();

                $otherSessionIds = DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $currentSessionId)
                    ->orderByDesc('last_activity')
                    ->pluck('id');

                $idsToRemove = $otherSessionIds->slice(max(0, $limit - 1));
                if ($idsToRemove->isNotEmpty()) {
                    DB::table('sessions')->whereIn('id', $idsToRemove)->delete();
                }
            }

            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}