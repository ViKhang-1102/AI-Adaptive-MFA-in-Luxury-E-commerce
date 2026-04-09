@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white p-8 rounded-2xl shadow-soft border border-neutral-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-primary font-serif">Register</h1>
            <p class="text-neutral-500 text-sm mt-2">Join our luxury e-commerce community</p>
        </div>

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="latitude">
            <input type="hidden" name="longitude">

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

        <button type="submit" class="w-full bg-primary text-white shadow-soft transition-all duration-300 hover:shadow-hover hover:-translate-y-0.5 py-3 rounded-xl hover:bg-primary-light mb-6 font-bold">
            Create Luxury Account
        </button>
    </form>

    <p class="text-center text-sm text-neutral-600">
        Already have an account? <a href="{{ route('login') }}" class="text-gold-dark font-bold hover:underline">Login here</a>
    </p>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Trigger geolocation immediately on register page
        if (typeof capturePreciseLocation === 'function') {
            capturePreciseLocation(false);
        }
    });
</script>
@endpush
@endsection
