<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class BillingAuthController extends Controller
{
    public function showLogin()
    {
        $cashiers = User::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('auth.billing-login', ['cashiers' => $cashiers]);
    }

    /**
     * Quick cashier login: pick your name, enter your 4-digit PIN.
     */
    public function loginWithPin(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'pin' => ['required', 'digits:4'],
        ]);

        $throttleKey = 'pin-login:' . $validated['user_id'] . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'pin' => "Too many attempts. Please try again in {$seconds} seconds.",
            ])->withInput(['user_id' => $validated['user_id']]);
        }

        $user = User::find($validated['user_id']);

        if (! $user || ! $user->isActive() || ! $user->hasPin() || ! Hash::check($validated['pin'], $user->pin)) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withErrors([
                'pin' => 'Incorrect PIN. Please try again.',
            ])->withInput(['user_id' => $validated['user_id']]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/billing');
    }

    /**
     * Fallback login for accounts that don't have a PIN set up yet.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/billing');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/billing/login');
    }
}