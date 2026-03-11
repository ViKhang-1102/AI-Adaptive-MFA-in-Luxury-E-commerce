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
use Illuminate\Support\Facades\Storage;
use App\Services\FaceVerificationService;

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

        // --- Check if FaceID Cache is Missing ---
        $faceService = app(FaceVerificationService::class);
        $faceCacheMissing = !$user->identity_image || !$faceService->hasCachedFaceDescriptor($user->identity_image);

        // --- AI Risk Assessment for Login Anomaly Intervention ---
        $enableAiMfa = env('ENABLE_AI_MFA', true);
        if ($faceCacheMissing || ($enableAiMfa && !Session::get('mfa_verified_for_login')) || (!$enableAiMfa && !Session::get('mfa_verified_for_login'))) {
            if ($enableAiMfa) {
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
            } else {
                // Static MFA - Non AI branch (Always OTP for every login if AI is disabled)
                $suggestion = 'otp';
                $score = 50.0;
                $level = 'medium';
                $riskResult = [
                    'explanation' => [
                        'score_breakdown' => ['Static (no-AI) MFA mode in use; every login requires OTP.'],
                        'input' => ['amount' => 0, 'ip' => request()->ip()],
                    ],
                ];
            }

            // FORCE FACE ID re-enrollment if they have no cache (only if AI is enabled)
            if ($enableAiMfa && $faceCacheMissing) {
                $suggestion = 'faceid';
                $score = max($score, 85.0);
                $level = 'high';
            }

            // Create Security Audit Record
            $audit = SecurityAudit::create([
                'user_id' => $user->id,
                'action' => 'login',
                'amount' => 0,
                'risk_score' => $score,
                'level' => $level,
                'suggestion' => $suggestion,
                'result' => ($suggestion === 'allow' ? 'success' : ($suggestion === 'block' ? 'blocked' : 'pending')),
                'metadata' => [
                    'ip' => request()->ip(),
                    'ai_enabled' => $enableAiMfa,
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

        // Auto re-enroll face identity to keep AI vectors up to date with extreme lighting/HOG upgrades
        if ($enableAiMfa && $user->identity_image) {
            try {
                $fullPath = Storage::disk('public')->path($user->identity_image);
                $python = env('PYTHON_BINARY');
                if (!$python) {
                    $finder = new \Symfony\Component\Process\ExecutableFinder();
                    $python = $finder->find('python') ?: $finder->find('python3') ?: 'python';
                }
                $script = base_path('scripts/face_verify.py');
                if (file_exists($fullPath) && file_exists($script)) {
                    $process = new \Symfony\Component\Process\Process([$python, $script, $fullPath, $fullPath, '--user-id', (string)$user->id, '--enroll']);
                    $process->start(); // Run async to not block login
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Auto FaceID re-enroll failed: ' . $e->getMessage());
            }
        }

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
            'face_data' => 'required|string', // Live scan is now mandatory
        ]);

        // 1. Create User record first (identity_image will be set after storing the photo)
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
            'identity_image' => null,
        ]);

        // 2. Save Physical Identity (.jpg) using user ID to tie cache directly to profile
        $faceData = $validated['face_data'];
        $snapshotData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $faceData));
        $identityPath = 'identity_images/user_' . $user->id . '_' . time() . '.jpg';
        Storage::disk('public')->put($identityPath, $snapshotData);
        $user->identity_image = $identityPath;
        $user->save();

        // 3. Extract Landmarks and Save Digital Identity (.json cache)
        $faceService = app(FaceVerificationService::class);
        $enrollResult = $faceService->verify($faceData, $identityPath, true, $user->id);

        if (!$enrollResult['success']) {
            // Fail registration if AI cannot extract landmarks from the live scan
            $user->delete();
            Storage::disk('public')->delete($identityPath);
            return back()->with('error', 'Biometric extraction failed: ' . $enrollResult['reason'] . '. Please ensure good lighting and look straight at the camera.');
        }

        // Create wallet for user
        EWallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'total_received' => 0,
            'total_spent' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Account created successfully with Digital Identity secured.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully');
    }
}
