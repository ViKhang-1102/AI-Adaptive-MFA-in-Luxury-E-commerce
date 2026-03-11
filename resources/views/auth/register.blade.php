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

        <div class="mb-6">
            <label class="block text-neutral-700 font-bold mb-2">Luxury Identity Profile (Required)</label>
            
            <div id="face-enroll-container" class="relative rounded-xl overflow-hidden border border-dashed border-neutral-300 bg-neutral-50 p-4 transition-all hover:border-gold">
                <div id="camera-placeholder" class="flex flex-col items-center justify-center py-8">
                    <div class="w-24 h-24 bg-neutral-200 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-camera text-3xl text-neutral-400"></i>
                    </div>
                    <p class="text-sm text-neutral-600 font-medium">Live Webcam Scan Required</p>
                    <p class="text-xs text-neutral-400 mt-1 px-4 text-center">Anti-static photo protection: upload is disabled. Please perform a live scan.</p>
                    
                    <button type="button" id="start-scan-btn" class="mt-4 px-6 py-2 bg-primary text-white text-sm font-bold rounded-full shadow-sm hover:bg-primary-light transition flex items-center gap-2">
                        <i class="fas fa-video"></i> Open Secure Camera
                    </button>
                </div>

                <div id="camera-active" class="hidden flex flex-col items-center">
                    <div class="relative w-48 h-60 overflow-hidden border-2 border-gold rounded-[50%] shadow-lg bg-black mb-4">
                        <video id="register-video" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]"></video>
                        <div id="scan-line" class="absolute left-0 w-full h-[2px] bg-gold/50 shadow-[0_0_10px_#D4AF37] animate-pulse" style="top: 50%"></div>
                    </div>
                    <p id="scan-instruction" class="text-xs font-bold text-primary uppercase tracking-wider mb-4">Look straight and hold still</p>
                    <button type="button" id="capture-btn" class="px-8 py-2 bg-gold text-primary-dark font-bold rounded-full shadow-md hover:bg-yellow-400 transition">
                        Capture Identity
                    </button>
                </div>

                <div id="camera-preview" class="hidden flex flex-col items-center">
                    <div class="relative w-48 h-60 overflow-hidden border-2 border-emerald-500 rounded-[50%] shadow-lg mb-4">
                        <img id="face-preview-img" class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]">
                        <div class="absolute inset-0 flex items-center justify-center bg-emerald-500/20">
                            <i class="fas fa-check-circle text-white text-4xl shadow-sm"></i>
                        </div>
                    </div>
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-4">Biometric Captured</p>
                    <button type="button" id="retake-btn" class="text-xs text-neutral-500 font-bold hover:text-primary transition underline">
                        Retake Scan
                    </button>
                </div>
            </div>

            <input type="hidden" name="face_data" id="face_data_input">
            
            @error('identity_image')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            @error('face_data')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const startBtn = document.getElementById('start-scan-btn');
                const captureBtn = document.getElementById('capture-btn');
                const retakeBtn = document.getElementById('retake-btn');
                const placeholder = document.getElementById('camera-placeholder');
                const active = document.getElementById('camera-active');
                const preview = document.getElementById('camera-preview');
                const video = document.getElementById('register-video');
                const previewImg = document.getElementById('face-preview-img');
                const faceDataInput = document.getElementById('face_data_input');
                const instruction = document.getElementById('scan-instruction');
                
                let stream = null;

                startBtn.addEventListener('click', async () => {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: 'user', width: 640, height: 480 }, 
                            audio: false 
                        });
                        video.srcObject = stream;
                        placeholder.classList.add('hidden');
                        active.classList.remove('hidden');
                    } catch (err) {
                        alert("Camera access denied. FaceID is mandatory for registration.");
                    }
                });

                captureBtn.addEventListener('click', () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);
                    
                    const data = canvas.toDataURL('image/jpeg', 0.9);
                    faceDataInput.value = data;
                    previewImg.src = data;
                    
                    // Stop camera
                    if (stream) stream.getTracks().forEach(t => t.stop());
                    
                    active.classList.add('hidden');
                    preview.classList.remove('hidden');
                });

                retakeBtn.addEventListener('click', () => {
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                    faceDataInput.value = '';
                });
            });
        </script>

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
</div>
@endsection
