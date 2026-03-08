<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\SecurityAudit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\FaceVerificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

        // Determine if we have a cached face descriptor for this enrolled user.
        $faceCacheMissing = false;
        if ($hasIdentityImage) {
            $faceService = app(FaceVerificationService::class);
            if (!$faceService->hasCachedFaceDescriptor($identityImagePath)) {
                $faceCacheMissing = true;
            }
        }

        // --- Enrollment Redirection for Legacy Users ---
        // If logged in but missing digital identity cache, force enrollment
        if (Auth::check() && $hasIdentityImage && $faceCacheMissing) {
            return redirect()->route('face.enrollment.show')->with('info', 'Please update your biometric data for secure transactions.');
        }

        // Evaluate the risk score from the pending audit (if available)
        $audit = null;
        $riskScore = null;
        $scanRequired = false;
        
        if (Session::has('pending_audit_id')) {
            $audit = SecurityAudit::find(Session::get('pending_audit_id'));
            $riskScore = $audit?->risk_score;
            
            // Adaptive logic:
            // 1. Force FaceID if risk is high (>= 70)
            // 2. Force FaceID if user hasn't enrolled yet (identity_image is null)
            // 3. Force FaceID if cached face fingerprints are missing (cache build required)
            if (($riskScore !== null && $riskScore >= 65) || !$hasIdentityImage || $faceCacheMissing) {
                $scanRequired = true;
            }
        } else {
            // Default behavior for generic OTP flows (like login)
            if (!$hasIdentityImage || $faceCacheMissing) {
                $scanRequired = true;
            }
        }

        // Face scan should be shown for users who have an identity profile
        $scanEnabled = $hasIdentityImage;
        $scanDurationMs = $scanRequired ? 3500 : ($scanEnabled ? 1500 : 0);

        // We no longer require manual upload, we use the live scan for enrollment
        // But if user has NO identity image AND it's a high risk, we show enrollment UI
        if (!$hasIdentityImage && $scanRequired) {
            $scanEnabled = true;
        }

        return view('auth.verify-otp', [
            'scanEnabled' => $scanEnabled,
            'scanRequired' => $scanRequired,
            'scanDurationMs' => $scanDurationMs,
            'riskScore' => $riskScore,
            'identityImage' => $identityImagePath,
            'needsIdentityUpload' => false,
            'isEnrollment' => !$hasIdentityImage,
            'faceCacheMissing' => $faceCacheMissing,
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
        $faceData = $request->input('face_data'); // Base64 snapshot from frontend

        // Determine the subject user
        $user = null;
        if (Session::has('pending_login_user_id')) {
            $user = User::find(Session::get('pending_login_user_id'));
        }
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }

        // --- Server-Side Face Verification / Enrollment ---
        if ($isFaceVerified && $faceData && $user) {
            $faceService = app(FaceVerificationService::class);
            
            // Check if we need to force enrollment (new user OR missing cache for legacy user)
            $faceCacheMissing = false;
            if ($user->identity_image) {
                $faceCacheMissing = !$faceService->hasCachedFaceDescriptor($user->identity_image);
            }

            if (!$user->identity_image || $faceCacheMissing) {
                // Enrollment Flow: First time FaceID or Re-enrollment for missing cache
                $snapshotData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $faceData));
                $newIdentityPath = 'identity_images/user_' . $user->id . '_' . time() . '.jpg';
                Storage::disk('public')->put($newIdentityPath, $snapshotData);
                
                // Trích xuất Landmarks ngay lập tức để lưu cache JSON (Digital Identity)
                $enrollResult = $faceService->verify($faceData, $newIdentityPath, true);
                
                if (!$enrollResult['success']) {
                    Storage::disk('public')->delete($newIdentityPath);
                    return back()->with('error', 'Face Enrollment failed: ' . $enrollResult['reason'] . '. Please ensure good lighting and look straight.');
                }

                $user->identity_image = $newIdentityPath;
                $user->save();
                $user->refresh();
                
                Log::info("FaceID Digital Identity Enrolled: " . $user->id, ['path' => $newIdentityPath]);
                Session::flash('success', $faceCacheMissing ? 'Biometric data updated for secure verification.' : 'Biometric identity registered successfully.');
            } else {
                // Comparison Flow: User already has an identity image and cache
                $comparison = $faceService->verify($faceData, $user->identity_image);

                if (!$comparison['success']) {
                    if (Session::has('pending_audit_id')) {
                        SecurityAudit::where('id', Session::get('pending_audit_id'))->update([
                            'result' => 'failed',
                            'metadata->face_verification_error' => $comparison['reason']
                        ]);
                    }
                    return back()->with('error', $comparison['reason']);
                }
            }
        } elseif ($isFaceVerified && !$faceData) {
            // Suspicious: face_verified is true but no image data sent
            return back()->with('error', 'Face data missing. Please try scanning again.');
        }

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
