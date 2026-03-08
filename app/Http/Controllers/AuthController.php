<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;
use App\Services\RiskAssessmentService;
use App\Models\SecurityAudit;
use Illuminate\Support\Facades\Session;

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

        // --- AI Risk Assessment for Login Anomaly Intervention ---
        $enableAiMfa = env('ENABLE_AI_MFA', true);
        if ($enableAiMfa && !Session::get('mfa_verified_for_login')) {
            $riskService = app(RiskAssessmentService::class);
            // We pass in an arbitrary 'amount' of 0 for log-in assessments
            $riskResult = $riskService->analyze($user, 0);

            if ($riskResult) {
                $suggestion = $riskResult['suggestion'] ?? 'allow';
                $score = $riskResult['risk_score'] ?? 0;
                $level = $riskResult['level'] ?? 'low';
            } else {
                // Fail-secure: default to MFA if API timeouts
                $suggestion = 'otp';
                $score = 50.0;
                $level = 'medium';
                $riskResult = [
                    'explanation' => [
                        'score_breakdown' => ['Risk scoring service unreachable; applying default login MFA score.'],
                        'input' => ['amount' => 0, 'ip' => request()->ip()],
                    ],
                ];
            }

            // Create Security Audit Record
            $audit = SecurityAudit::create([
                'user_id' => $user->id,
                'action' => 'login',
                'amount' => 0,
                'risk_score' => $score,
                'level' => $level,
                'suggestion' => $suggestion,
                'result' => $suggestion === 'allow' ? 'success' : 'pending',
                'metadata' => [
                    'ip' => request()->ip(),
                    'ai_enabled' => true,
                    'risk_explanation' => $riskResult['explanation'] ?? null,
                    'engine_input' => [
                        'amount' => 0,
                        'ip_change_count' => 0,
                        'location' => $riskResult['explanation']['input']['location'] ?? 'Unknown',
                        'device' => substr(md5($request->header('User-Agent')), 0, 16),
                        'device_is_new' => Session::has('device_verified_for_login') ? false : true,
                    ],
                ]
            ]);

            Session::put('pending_audit_id', $audit->id);

            if ($suggestion === 'faceid' || $suggestion === 'otp') {
                $otp = rand(100000, 999999);
                Session::put('expected_otp', $otp);
                // Tag this OTP session specifically for Login resumption
                Session::put('pending_login_user_id', $user->id);

                \Illuminate\Support\Facades\Log::channel('single')->info("Login MFA Requested for User [{$user->id}]. OTP Code: [{$otp}]");
            
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\MfaOtpMail($otp));

                Session::flash('ai_warning', "Unusual login activity detected. For your security, please verify your identity.");

                return redirect()->route('otp.verify');
            }
        }
        
        // Clean up the MFA flag so future distinct logins re-evaluate
        Session::forget('mfa_verified_for_login');

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
            'identity_image' => 'nullable|image|max:4096',
        ]);

        // Handle identity profile image upload (optional)
        if ($request->hasFile('identity_image')) {
            $validated['identity_image'] = $request->file('identity_image')->store('identities', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'identity_image' => $validated['identity_image'] ?? null,
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
