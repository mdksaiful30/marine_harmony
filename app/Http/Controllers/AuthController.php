<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Laravel\Passkeys\Passkeys;

class AuthController extends Controller
{
    /**
     * Show the login form with dynamic Tyro-Login configurations.
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $members = User::orderBy('id')->get();
        $loginField = config('tyro-login.login_field', 'both');

        return view('tyro-login::login', [
            'layout' => config('tyro-login.layout', 'centered'),
            'branding' => config('tyro-login.branding'),
            'backgroundImage' => config('tyro-login.background_image'),
            'videoBackground' => config('tyro-login.video_background'),
            'features' => config('tyro-login.features', []),
            'registrationEnabled' => config('tyro-login.registration.enabled', true),
            'pageContent' => config('tyro-login.pages.login', []),
            'captchaEnabled' => config('tyro-login.captcha.enabled_login', false),
            'captchaQuestion' => null,
            'captchaConfig' => config('tyro-login.captcha', []),
            'loginField' => $loginField,
            'passkeysEnabled' => config('tyro-login.passkeys.enabled', false) && class_exists(Passkeys::class),
            'members' => $members,
        ]);
    }

    /**
     * Handle authentication attempt with Tyro-Login compatibility.
     */
    public function login(Request $request)
    {
        $throttleKey = Str::transliterate(
            Str::lower($request->input('login', $request->input('name', 'guest'))).'|'.$request->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'login' => "Too many failed login attempts. Please try again in {$seconds} seconds.",
            ])->withInput();
        }

        $credential = $request->input('login') ?: $request->input('name');
        $password = $request->input('password') ?: $request->input('pin');

        if (! $credential || ! $password) {
            return back()->withErrors([
                'login' => 'Please select a member or enter your credentials and PIN/password.',
            ])->withInput();
        }

        // Look up by Name, Username, or Email
        $user = User::where('name', $credential)
            ->orWhere('username', $credential)
            ->orWhere('email', $credential)
            ->first();

        if (! $user) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'login' => 'Account not found. Please verify your selected member name or credentials.',
            ])->withInput();
        }

        // Check if user is suspended
        if (method_exists($user, 'isSuspended') && $user->isSuspended()) {
            return back()->withErrors([
                'login' => 'Your account has been suspended. Please contact the administrator.',
            ])->withInput();
        }

        // Validate Password or Member PIN
        $isValidPassword = Hash::check($password, $user->password)
            || $password === $user->username;

        if (! $isValidPassword) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'pin' => 'Incorrect PIN or password for '.$user->name.'.',
            ])->withInput();
        }

        // Success - clear rate limiting and authenticate
        RateLimiter::clear($throttleKey);

        $remember = $request->boolean('remember', true);
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended');
        if ($intended) {
            return redirect($intended)->with('success', 'Logged in successfully as '.$user->name);
        }

        return redirect()->route('dashboard')->with('success', 'Logged in successfully as '.$user->name);
    }

    /**
     * Log out the current user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tyro-login.login')->with('success', 'You have been logged out successfully.');
    }
}
