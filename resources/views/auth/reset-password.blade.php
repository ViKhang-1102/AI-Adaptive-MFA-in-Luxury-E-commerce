@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-md-lg shadow-sm">
    <h1 class="text-2xl font-bold mb-6 text-center text-primary font-serif">Reset Password</h1>
    <p class="text-sm text-neutral-600 mb-6 text-center">Enter the OTP sent to your email and your new password.</p>

    <form action="{{ route('password.update') }}" method="POST">
        @csrf

        <div class="mb-4 text-center">
            <label class="block text-neutral-700 font-bold mb-2">6-Digit OTP</label>
            <input type="text" name="otp" 
                class="w-full px-4 py-3 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-center text-2xl font-mono tracking-widest"
                maxlength="6" placeholder="000000" required>
            @error('otp')
                <span class="text-red-600 text-sm font-bold">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">New Password</label>
            <input type="password" name="password" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                required>
            @error('password')
                <span class="text-red-600 text-sm font-bold">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-neutral-700 font-bold mb-2">Confirm New Password</label>
            <input type="password" name="password_confirmation" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                required>
        </div>

        <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md-lg hover:bg-primary-light hover:-translate-y-0.5 mb-4">
            Reset Password
        </button>
    </form>
</div>
@endsection
