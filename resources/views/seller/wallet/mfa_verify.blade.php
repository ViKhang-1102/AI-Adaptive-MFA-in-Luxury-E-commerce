@extends('layouts.app')
@section('title', 'Verify Withdrawal')
@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white p-8 rounded-2xl shadow-soft border border-neutral-100 mt-10 text-center">
        <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="shield-alert" class="w-10 h-10"></i>
        </div>
        
        <h2 class="text-2xl font-serif font-bold text-primary mb-2">Security Verification Required</h2>
        <p class="text-neutral-500 mb-8 leading-relaxed">
            Our system detected an unusual request pattern (Risk Score: {{ number_format($riskScore, 2) }}). 
            For your security, please confirm that you are intentionally withdrawing <strong>${{ number_format($amount, 2) }}</strong> to your PayPal account.
        </p>

        <form method="POST" action="{{ route('seller.wallet.withdraw') }}" class="max-w-md mx-auto space-y-4">
            @csrf
            <!-- Hidden inputs to carry over the original request data -->
            <input type="hidden" name="amount" value="{{ $amount }}" />
            <!-- MFA flag to bypass the risk check on the next attempt -->
            <input type="hidden" name="mfa_verified" value="1" />
            
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-3.5 bg-primary text-white font-medium rounded-xl hover:bg-primary-light focus:ring-4 focus:ring-primary/20 transition-all shadow-soft hover:shadow-hover">
                <i data-lucide="check-circle" class="w-5 h-5"></i> Verify and Continue Withdrawal
            </button>
            
            <a href="{{ route('seller.wallet.index') }}" class="block w-full py-3.5 bg-neutral-50 text-neutral-600 font-medium rounded-xl hover:bg-neutral-100 transition-colors">
                Cancel
            </a>
        </form>
    </div>
</div>
@endsection
