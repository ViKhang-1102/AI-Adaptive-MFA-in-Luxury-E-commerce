@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-md-lg shadow-sm">
    <h1 class="text-2xl font-bold mb-6 text-center text-primary font-serif">Login</h1>

    <form action="{{ route('login') }}" method="POST">
        @csrf

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

    <div class="mt-4 text-center space-y-2">
        <p class="text-neutral-600">
            Don't have an account? <a href="{{ route('register') }}" class="text-primary hover:underline">Register here</a>
        </p>
        <p class="text-xs text-neutral-500">
            Account locked? <a href="{{ route('unlock.request') }}" class="text-primary hover:underline font-bold">Unlock Account</a>
        </p>
    </div>
</div>
@endsection
