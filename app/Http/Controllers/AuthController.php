<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If user exists, just update their google_id if missing and log them in
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'last_login' => now(),
                ]);
            } else {
                // Determine random role or assume customer?
                // For a marketplace, maybe we assume customer first
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => null, // No password for Google users
                    'role' => 'customer', // Default role
                    'is_active' => true,
                    'last_login' => now(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            Auth::login($user);
            return redirect()->intended(route('home'))->with('success', 'Logged in with Google successfully!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth Error', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Google Authentication Failed.');
        }
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->with('error', 'Invalid credentials');
        }

        if (!$user->is_active) {
            return back()->with('error', 'Your account has been deactivated');
        }

        Auth::login($user);
        $user->update(['last_login' => now()]);

        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => 'required|in:customer,seller',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        // Create wallet for user
        EWallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'total_received' => 0,
            'total_spent' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Account created successfully');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully');
    }
}
