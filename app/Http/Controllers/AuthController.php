<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $members = User::orderBy('id')->get();

        return view('auth.login', compact('members'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'pin' => 'required|string',
        ]);

        $user = User::where('name', $request->input('name'))->first();

        if (! $user) {
            return back()->withErrors(['pin' => 'Invalid member or PIN.'])->withInput();
        }

        if (Hash::check($request->input('pin'), $user->password) || $request->input('pin') === $user->username) {
            Auth::login($user, true);

            return redirect()->route('dashboard')->with('success', 'Logged in successfully as '.$user->name);
        }

        return back()->withErrors(['pin' => 'Incorrect PIN for '.$user->name])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }
}
