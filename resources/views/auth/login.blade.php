@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white p-8 rounded-2xl shadow-soft border border-neutral-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary font-serif">Login</h1>
            <p class="text-neutral-500 text-sm mt-2">Access your luxury dashboard</p>
        </div>

        <form id="login-form" action="{{ route('login') }}" method="POST">
        @csrf
        <input type="hidden" name="latitude" id="login_lat">
        <input type="hidden" name="longitude" id="login_lng">

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                required>
            @error('email')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <label class="text-neutral-700 font-bold">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs text-primary hover:underline">Forgot Password?</a>
            </div>
            <input type="password" name="password" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                required>
            @error('password')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md-lg hover:bg-primary-light hover:-translate-y-0.5 mb-4">
            Login
        </button>
    </form>

    <div class="mt-8 text-center space-y-3">
        <p class="text-sm text-neutral-600">
            Don't have an account? <a href="{{ route('register') }}" class="text-gold-dark font-bold hover:underline">Register here</a>
        </p>
        <p class="text-xs text-neutral-400">
            Account locked? <a href="{{ route('unlock.request') }}" class="text-primary hover:underline font-bold">Unlock Account</a>
        </p>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger geolocation immediately on login page
        if (typeof capturePreciseLocation === 'function') {
            console.log("Login page: Triggering auto-location...");
            capturePreciseLocation(false);
        }
    });
</script>
@endpush
@endsection
