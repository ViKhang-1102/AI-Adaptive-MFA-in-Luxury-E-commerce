@extends('layouts.app')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-neutral-50 relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-full height-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-primary/5 blur-3xl"></div>
    </div>

    <div class="max-w-md w-full relative z-10">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-neutral-100">
            <!-- Header Section -->
            <div class="bg-primary px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-gold-dark via-gold to-gold-light"></div>
                <div class="absolute inset-0 bg-primary-dark opacity-20 pointer-events-none pattern-dots"></div>
                
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-light shadow-inner mb-6 border border-primary/50">
                        <i class="fas fa-shield-alt text-2xl text-gold"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-bold text-white uppercase tracking-widest mb-2">
                        Luxury Concierge Security Service
                    </h3>
                    <p class="text-primary-300 text-sm font-light">
                        Adaptive AI Engine Intervention
                    </p>
                </div>
            </div>

            <!-- Body Section -->
            <div class="p-8">
                <p class="text-center text-neutral-600 text-sm mb-8 leading-relaxed">
                    For your protection, we need to verify your identity before completing this transaction.
                </p>

                <!-- Alerts -->
                @if(session('success'))
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                        <p class="text-sm text-emerald-800">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('ai_warning'))
                    <div class="bg-amber-50 border-l-4 border-amber-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-robot text-amber-500 mt-1"></i>
                        <p class="text-sm text-amber-800">{{ session('ai_warning') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        <p class="text-sm text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                @if($scanEnabled)
                    <div id="face-scan-wrapper" style="display: {{ $scanRequired ? 'block' : 'none' }};">
                        @include('partials.face-scanner', [
                            'id' => 'otp-scanner',
                            'title' => isset($isEnrollment) && $isEnrollment ? 'FaceID Enrollment' : 'Identity Verification',
                            'isEnrollment' => isset($isEnrollment) && $isEnrollment,
                            'submitUrl' => route('otp.verify.submit'),
                            'onSuccess' => 'handleOtpFaceSuccess',
                            'onError' => 'handleOtpFaceError',
                            'riskScore' => $riskScore ?? null,
                        ])
                    </div>

                    <script>
                        function handleOtpFaceSuccess(result) {
                            if (result.redirect) {
                                window.location.href = result.redirect;
                            } else {
                                window.location.reload();
                            }
                        }
                        function handleOtpFaceError(err) {
                            document.getElementById('backup-otp-btn')?.classList.remove('hidden');
                        }
                        document.addEventListener('DOMContentLoaded', () => {
                            const switchButton = document.getElementById('switch-to-faceid');
                            const scanWrapper = document.getElementById('face-scan-wrapper');
                            const otpForm = document.getElementById('otp-form');

                            if (switchButton && scanWrapper && otpForm) {
                                switchButton.addEventListener('click', () => {
                                    scanWrapper.style.display = 'block';
                                    otpForm.style.display = 'none';
                                    switchButton.style.display = 'none';
                                });
                            }
                        });
                    </script>

                    @if(!$scanRequired)
                        <div class="mb-6 text-center">
                            <button type="button" id="switch-to-faceid" class="text-gold text-sm font-bold hover:text-yellow-400 transition flex items-center justify-center gap-2 mx-auto">
                                <i class="fas fa-id-card"></i> Or Verify with FaceID
                            </button>
                        </div>
                    @else
                        <div class="mb-6 text-center">
                            <button type="button" id="backup-otp-btn" class="hidden text-gold text-sm font-bold hover:text-yellow-400 transition flex items-center justify-center gap-2 mx-auto">
                                <i class="fas fa-key"></i> Use backup OTP code
                            </button>
                        </div>
                    @endif
                @endif

                <form method="POST" action="{{ route('otp.verify.submit') }}" class="space-y-8" id="otp-form" @if($scanEnabled && $scanRequired) style="display: none;" @endif>
                    @csrf
                    <div>
                        <div class="relative">
                            <input id="otp" type="text" 
                                   class="block w-full text-center bg-neutral-50 border border-neutral-300 rounded-lg text-neutral-900 focus:ring-gold focus:border-gold placeholder:text-neutral-300 transition-colors @error('otp') border-red-500 ring-1 ring-red-500 @enderror" 
                                   name="otp" 
                                   @if(!$scanRequired) required autofocus @endif
                                   placeholder="&middot; &middot; &middot; &middot; &middot; &middot;"
                                   maxlength="6"
                                   style="letter-spacing: 0.8em; font-size: 1.75rem; padding: 1rem 0; font-family: monospace;">
                                    
                            @error('otp')
                                <p class="mt-2 text-sm text-red-600 text-center font-medium bg-red-50 py-1.5 rounded" role="alert">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <p class="mt-3 text-center text-xs text-neutral-400">
                            Enter the 6-digit code provided in the email.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-primary hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary uppercase tracking-widest transition-all hover:shadow-md">
                            Verify & Proceed
                        </button>
                        <a href="{{ route('home') }}" class="w-full flex justify-center py-3.5 px-4 border border-neutral-300 rounded-lg shadow-sm text-sm font-bold text-neutral-700 bg-white hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary tracking-widest uppercase transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
