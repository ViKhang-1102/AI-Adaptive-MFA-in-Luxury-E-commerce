<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SecurityAudit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OTPController extends Controller
{
    /**
     * Show the OTP Verification View
     */
    public function showVerifyForm()
    {
        if (!Session::has('expected_otp')) {
            return redirect()->route('home')->with('error', 'No pending transaction requiring MFA.');
        }

        // Determine the subject user for this OTP flow (login interception or already authenticated)
        $user = null;
        if (Session::has('pending_login_user_id')) {
            $user = User::find(Session::get('pending_login_user_id'));
        }
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }

        $identityImagePath = $user?->identity_image;
        $hasIdentityImage = !empty($identityImagePath);

        // Evaluate the risk score from the pending audit (if available)
        $audit = null;
        $riskScore = null;
        $scanRequired = false;
        if (Session::has('pending_audit_id')) {
            $audit = SecurityAudit::find(Session::get('pending_audit_id'));
            $riskScore = $audit?->risk_score;
            // Adaptive logic: require face scan for high risk events
            if ($riskScore !== null && $riskScore >= 70) {
                $scanRequired = true;
            }
        }

        // Face scan should be shown for users who have an identity profile
        $scanEnabled = $hasIdentityImage;
        $scanDurationMs = $scanRequired ? 3200 : ($scanEnabled ? 1200 : 0);

        // If user does not yet have an identity profile, we require an upload first
        $needsIdentityUpload = !$hasIdentityImage;

        return view('auth.verify-otp', [
            'scanEnabled' => $scanEnabled,
            'scanRequired' => $scanRequired,
            'scanDurationMs' => $scanDurationMs,
            'riskScore' => $riskScore,
            'identityImage' => $identityImagePath,
            'needsIdentityUpload' => $needsIdentityUpload,
        ]);
    }

    /**
     * Handle Identity Profile Image upload during OTP verification.
     */
    public function uploadIdentity(Request $request)
    {
        if (!Session::has('expected_otp')) {
            return redirect()->route('home')->with('error', 'No pending transaction requiring MFA.');
        }

        $request->validate([
            'identity_image' => 'required|image|max:4096',
        ]);

        $user = null;
        if (Session::has('pending_login_user_id')) {
            $user = User::find(Session::get('pending_login_user_id'));
        }
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect()->route('home')->with('error', 'Unable to associate identity image with a user.');
        }

        $path = $request->file('identity_image')->store('identities', 'public');
        $user->update(['identity_image' => $path]);

        // Mark that the user now has an identity image in this flow
        Session::put('identity_image_uploaded', true);

        return redirect()->route('otp.verify')->with('success', 'Identity Profile saved. Proceed to verify with OTP.');
    }

    /**
     * Verify the submitted OTP
     */
    public function verify(Request $request)
    {
        $isFaceVerified = $request->input('face_verified') === 'true';

        if (!$isFaceVerified) {
            $request->validate([
                'otp' => 'required|numeric'
            ]);
        }

        $expectedOtp = Session::get('expected_otp');
        $attempts = Session::get('otp_attempts', 0);

        // --- Handle Locked Out State ---
        if ($attempts >= 3) {
            $this->clearOtpSessions();
            if (Session::has('pending_audit_id')) {
                SecurityAudit::where('id', Session::get('pending_audit_id'))->update(['result' => 'blocked']);
            }
            return redirect()->route('home')->with('error', 'Maximum security verification attempts (3) exceeded. Your action has been blocked for your protection.');
        }

        // --- Verify Success ---
        if ($isFaceVerified || (int)$request->otp === (int)$expectedOtp) {
            // Capture pending actions before we clear the session storage
            $pendingLoginUserId = Session::get('pending_login_user_id');
            $pendingCheckoutRequest = Session::get('pending_checkout_request');

            // OTP is correct, clear it
            $this->clearOtpSessions();
            
            // Mark session as verified
            Session::put('mfa_verified', true);
            Session::put('mfa_verified_for_login', true);

            // Mark Audit as success and associate user if this was a login interception
            if (Session::has('pending_audit_id')) {
                $auditUpdate = ['result' => 'success'];
                if ($pendingLoginUserId) {
                    $auditUpdate['user_id'] = $pendingLoginUserId;
                }
                SecurityAudit::where('id', Session::get('pending_audit_id'))->update($auditUpdate);
                Session::forget('pending_audit_id');
            }

            // 1. Was this a Login Interception?
            if ($pendingLoginUserId) {
                $user = User::find($pendingLoginUserId);
                if ($user) {
                    Auth::login($user);
                    $user->update(['last_login' => now()]);
                    
                    $intendedUrl = Session::get('intended_action_url');
                    if ($intendedUrl) {
                        return redirect()->to($intendedUrl)->with('success', 'Identity verified. Welcome back.');
                    }
                    return redirect()->intended(route('home'))->with('success', 'Identity verified. Welcome back.');
                }
            }

            // 2. Was this a Checkout Interception?
            if ($pendingCheckoutRequest) {
                // Re-instantiate the request to send it back to OrderController->store
                $internalRequest = Request::create(route('orders.store'), 'POST', $pendingCheckoutRequest);
                $internalRequest->headers->set('X-CSRF-TOKEN', csrf_token());

                return app(\App\Http\Controllers\OrderController::class)->store($internalRequest);
            }

            // 3. Was this a Password Change Interception?
            $pendingPasswordChange = Session::get('pending_password_change');
            if ($pendingPasswordChange) {
                $internalRequest = Request::create(route('profile.password'), 'POST', $pendingPasswordChange);
                $internalRequest->headers->set('X-CSRF-TOKEN', csrf_token());
                
                return app(\App\Http\Controllers\ProfileController::class)->updatePassword($internalRequest);
            }

            // 4. Fallback to generic intended action URL (for any GET requests intercepted)
            $intendedUrl = Session::get('intended_action_url');
            if ($intendedUrl) {
                return redirect()->to($intendedUrl)->with('success', 'Identity verified. Continuing action...');
            }

            return redirect()->route('home')->with('success', 'Verification complete.');
        }

        // --- Verify Failed ---
        $attempts++;
        Session::put('otp_attempts', $attempts);

        // Mark Audit as failed (Invalid OTP attempt)
        if (Session::has('pending_audit_id')) {
            SecurityAudit::where('id', Session::get('pending_audit_id'))->update(['result' => 'failed']);
        }

        $remaining = 3 - $attempts;
        if ($remaining > 0) {
            return back()->withInput()->with('error', "Invalid OTP code. You have {$remaining} attempt(s) remaining.");
        } else {
            // Block them immediately on the 3rd fail
            $this->clearOtpSessions();
            if (Session::has('pending_audit_id')) {
                SecurityAudit::where('id', Session::get('pending_audit_id'))->update(['result' => 'blocked']);
            }
            return redirect()->route('home')->with('error', 'Maximum security verification attempts exceeded. Action blocked.');
        }
    }

    private function clearOtpSessions()
    {
        Session::forget([
            'expected_otp',
            'otp_attempts',
            'pending_order_id',
            'pending_login_user_id',
            'pending_checkout_request',
            'identity_image_uploaded',
            'intended_action_url',
            'pending_password_change',
        ]);
    }
}
