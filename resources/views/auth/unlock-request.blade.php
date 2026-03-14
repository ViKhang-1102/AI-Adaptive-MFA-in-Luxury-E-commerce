@extends('layouts.app')

@section('title', 'Unlock Account')

@section('content')
<div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-md-lg shadow-sm">
    <div class="flex flex-col items-center mb-6">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-center text-primary font-serif">Unlock Account</h1>
        <p class="text-sm text-neutral-600 text-center mt-2">Enter your email and we'll send you an OTP to unlock your account.</p>
    </div>

    <form action="{{ route('unlock.email') }}" method="POST">
        @csrf

        <div class="mb-6">
            <label class="block text-neutral-700 font-bold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                required>
            @error('email')
                <span class="text-red-600 text-sm font-bold">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md-lg hover:bg-primary-light hover:-translate-y-0.5 mb-4">
            Send Unlock Code
        </button>
    </form>

    <p class="text-center text-neutral-600">
        Account not locked? <a href="{{ route('login') }}" class="text-primary hover:underline">Back to Login</a>
    </p>
</div>
@endsection
