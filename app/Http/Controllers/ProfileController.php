<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'identity_image' => 'nullable|image|max:4096',
            'bio' => 'nullable|string|max:500',
            'paypal_email' => 'nullable|email|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        if ($request->hasFile('identity_image')) {
            $path = $request->file('identity_image')->store('identities', 'public');
            $validated['identity_image'] = $path;
        }

        // Only allow sellers to set paypal_email, but accept the field safely
        /** @var User $current */
        $current = Auth::user();
        if (!$current || !$current->isSeller()) {
            unset($validated['paypal_email']);
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|confirmed|min:6|different:current_password',
        ]);

        /** @var User $currentUser */
        $currentUser = Auth::user();

        // -------------------------
        // NEW: Adaptive MFA for Password Change
        // -------------------------
        if (!\Illuminate\Support\Facades\Session::get('mfa_verified')) {
            $enableAiMfa = env('ENABLE_AI_MFA', true);
            if ($enableAiMfa) {
                // Determine if device is new (from session)
                $deviceIsNew = !\Illuminate\Support\Facades\Session::has('device_verified');
                if ($deviceIsNew) {
                    $riskService = app(\App\Services\RiskAssessmentService::class);
                    // Pass a dummy amount for password change to trigger risk score.
                    $riskResult = $riskService->analyze($currentUser, 0);

                    if ($riskResult) {
                        $suggestion = $riskResult['suggestion'] ?? 'allow';
                        $score = $riskResult['risk_score'] ?? 0;
                        $level = $riskResult['level'] ?? 'low';
                    } else {
                        $suggestion = 'otp';
                        $score = 50.0;
                        $level = 'medium';
                        $riskResult = [
                            'explanation' => [
                                'score_breakdown' => ['Risk scoring unavailable; defaulting to MFA risk score.'],
                                'input' => ['amount' => 0],
                            ],
                        ];
                    }
                } else {
                    // Not a new device, no MFA needed in Adaptive mode
                    $suggestion = 'allow';
                }
            } else {
                // Static MFA - Non AI branch (Always OTP for every password change if AI is disabled)
                $suggestion = 'otp';
                $score = 50.0;
                $level = 'medium';
                $riskResult = [
                    'explanation' => [
                        'score_breakdown' => ['Static (no-AI) MFA mode in use; password change requires OTP.'],
                        'input' => ['amount' => 0],
                    ],
                ];
            }

            if (isset($suggestion) && $suggestion !== 'allow') {
                // Create Security Audit Record
                $audit = \App\Models\SecurityAudit::create([
                    'user_id' => $currentUser->id,
                    'action' => 'password_change',
                    'amount' => 0,
                    'risk_score' => $score ?? 0,
                    'level' => $level ?? 'low',
                    'suggestion' => $suggestion,
                    'result' => ($suggestion === 'allow' ? 'success' : ($suggestion === 'block' ? 'blocked' : 'pending')),
                    'metadata' => [
                        'ai_enabled' => $enableAiMfa,
                        'device_is_new' => $deviceIsNew ?? true,
                        'risk_explanation' => $riskResult['explanation'] ?? null,
                        'engine_input' => [
                            'amount' => 0,
                            'device_is_new' => $deviceIsNew ?? true,
                        ],
                    ]
                ]);

                \Illuminate\Support\Facades\Session::put('pending_audit_id', $audit->id);

                if ($suggestion === 'faceid' || $suggestion === 'otp' || ($score ?? 0) >= 30) {
                    $otp = rand(100000, 999999);
                    \Illuminate\Support\Facades\Session::put('expected_otp', $otp);
                    
                    // Enable resume
                    \Illuminate\Support\Facades\Session::put('intended_action_url', url()->previous());
                    \Illuminate\Support\Facades\Session::put('pending_password_change', $validated);

                    \Illuminate\Support\Facades\Log::channel('single')->info("MFA Requested for User [{$currentUser->id}]. OTP Code: [{$otp}]");
                    
                    \Illuminate\Support\Facades\Mail::to($currentUser->email)->send(new \App\Mail\MfaOtpMail($otp));

                    \Illuminate\Support\Facades\Session::flash('ai_warning', "Security check required before changing password.");

                    return redirect()->route('otp.verify');
                }
            }
        }
        
        // Clean up flag
        \Illuminate\Support\Facades\Session::forget('mfa_verified');

        // Check if there are pending passsword parameters from session
        if (\Illuminate\Support\Facades\Session::has('pending_password_change')) {
            $validated = \Illuminate\Support\Facades\Session::pull('pending_password_change');
        }

        $currentUser->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully');
    }

    public function addresses()
    {
        /** @var User $addrUser */
        $addrUser = Auth::user();
        $addresses = $addrUser->addresses;
        return view('addresses.index', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            CustomerAddress::where('customer_id', Auth::id())->update(['is_default' => false]);
        }

        CustomerAddress::create([
            'customer_id' => Auth::id(),
            ...$validated,
            'is_default' => $isDefault,
        ]);

        return back()->with('success', 'Address added successfully');
    }

    public function editAddress(CustomerAddress $address)
    {
        if ($address->customer_id !== Auth::id()) {
            abort(403);
        }

        return view('addresses.edit', compact('address'));
    }

    public function updateAddress(Request $request, CustomerAddress $address)
    {
        if ($address->customer_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault && !$address->is_default) {
            CustomerAddress::where('customer_id', Auth::id())->update(['is_default' => false]);
        }

        $address->update(array_merge($validated, ['is_default' => $isDefault]));

        return back()->with('success', 'Address updated successfully');
    }

    public function destroyAddress(CustomerAddress $address)
    {
        if ($address->customer_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully');
    }
}
