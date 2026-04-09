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
                        <i class="fas fa-user-shield text-2xl text-gold"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-bold text-white uppercase tracking-widest mb-2">
                        Luxury Security Enrollment
                    </h3>
                    <p class="text-primary-300 text-sm font-light">
                        FaceID Digital Identity Setup
                    </p>
                </div>
            </div>

            <!-- Body Section -->
            <div class="p-8">
                <p class="text-center text-neutral-600 text-sm mb-8 leading-relaxed">
                    Welcome! To secure your account and enable high-value transactions, please enroll your biometric identity.
                </p>

                @include('partials.face-scanner', [
                    'id' => 'enrollment-scanner',
                    'title' => 'FaceID Enrollment',
                    'message' => 'Please scan your face to enroll.',
                    'isEnrollment' => true,
                    'submitUrl' => route('face.enrollment.submit'),
                ])

                <p class="text-xs text-neutral-500 text-center flex items-center justify-center gap-2 mt-4">
                    <i class="fas fa-lock text-gold"></i> Your biometric data is encrypted and used only for security verification.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
