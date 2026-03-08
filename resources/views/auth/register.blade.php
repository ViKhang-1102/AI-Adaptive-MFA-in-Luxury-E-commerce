@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-md-lg shadow-sm">
    <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>

    <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
            @error('name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Luxury Identity Profile (Optional)</label>
            <input type="file" name="identity_image" accept="image/*" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
            <p class="text-xs text-neutral-500 mt-2">Upload a clear headshot for premium security verification and high-value transaction protection.</p>
            @error('identity_image')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
            @error('email')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Select Role</label>
            <select name="role" class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                <option value="">-- Choose Role --</option>
                <option value="customer">Customer</option>
                <option value="seller">Seller</option>
            </select>
            @error('role')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Password</label>
            <input type="password" name="password" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
            @error('password')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-6">
            <label class="block text-neutral-700 font-bold mb-2">Confirm Password</label>
            <input type="password" name="password_confirmation" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
        </div>

        <button type="submit" class="w-full bg-primary text-white shadow-sm-soft transition-all duration-300 hover:shadow-sm-hover hover:-translate-y-0.5 py-2 rounded-md-lg hover:bg-primary-light hover:-translate-y-0.5 mb-4">
            Register
        </button>
    </form>

    <p class="text-center text-neutral-600">
        Already have an account? <a href="{{ route('login') }}" class="text-primary hover:underline">Login here</a>
    </p>
    <div class="mt-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with</span>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Register with Google
            </a>
        </div>
    </div>
</div>
@endsection
