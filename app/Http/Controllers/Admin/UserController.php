<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:owner,cashier'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        User::create($validated);

        return back()->with('success', 'User created successfully.');
    }

    /**
     * Update an existing user's name, email, role and (optionally) password.
     */
    public function update(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot edit your own account from here.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:owner,cashier'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Enable or disable a user account.
     */
    public function updateStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,disabled'],
        ]);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot disable your own account.']);
        }

        if ($validated['status'] === 'disabled'
            && $user->isOwner()
            && User::where('role', 'owner')->where('status', 'active')->count() <= 1) {
            return back()->withErrors(['user' => 'You cannot disable the last active owner account.']);
        }

        $user->update(['status' => $validated['status']]);

        $message = $validated['status'] === 'active' ? 'User enabled successfully.' : 'User disabled successfully.';

        return back()->with('success', $message);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}