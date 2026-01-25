@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">My Profile</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="bg-white p-6 rounded-lg shadow h-fit">
            <h3 class="font-bold mb-4">Profile Menu</h3>
            <div class="space-y-2">
                <button onclick="showSection('info')" class="w-full text-left px-4 py-2 rounded bg-blue-600 text-white">
                    Personal Information
                </button>
                <button onclick="showSection('password')" class="w-full text-left px-4 py-2 rounded hover:bg-gray-100">
                    Change Password
                </button>
                @if(auth()->user()->isCustomer())
                <a href="{{ route('addresses.index') }}" class="block px-4 py-2 rounded hover:bg-gray-100">
                    My Addresses
                </a>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div id="info-section" class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">Personal Information</h2>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-bold mb-2">Avatar</label>
                        @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-24 h-24 rounded mb-2">
                        @else
                        <i class="fas fa-user-circle text-6xl text-gray-300"></i>
                        @endif
                        <input type="file" name="avatar" accept="image/*" class="block mt-2 px-3 py-2 border rounded">
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" 
                            class="w-full px-4 py-2 border rounded" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Email</label>
                        <input type="email" value="{{ auth()->user()->email }}" 
                            class="w-full px-4 py-2 border rounded bg-gray-100" disabled>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Phone</label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone }}" 
                            class="w-full px-4 py-2 border rounded">
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Address</label>
                        <textarea name="address" rows="3" 
                            class="w-full px-4 py-2 border rounded">{{ auth()->user()->address }}</textarea>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Bio</label>
                        <textarea name="bio" rows="3" maxlength="500"
                            class="w-full px-4 py-2 border rounded">{{ auth()->user()->bio }}</textarea>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div id="password-section" class="bg-white p-6 rounded-lg shadow hidden">
                <h2 class="text-xl font-bold mb-4">Change Password</h2>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block font-bold mb-2">Current Password</label>
                        <input type="password" name="current_password" 
                            class="w-full px-4 py-2 border rounded" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">New Password</label>
                        <input type="password" name="password" 
                            class="w-full px-4 py-2 border rounded" required>
                    </div>

                    <div>
                        <label class="block font-bold mb-2">Confirm Password</label>
                        <input type="password" name="password_confirmation" 
                            class="w-full px-4 py-2 border rounded" required>
                    </div>

                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(section) {
    document.getElementById('info-section').classList.toggle('hidden', section !== 'info');
    document.getElementById('password-section').classList.toggle('hidden', section !== 'password');
}
</script>
@endsection
