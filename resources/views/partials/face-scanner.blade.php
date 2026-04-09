@props([
    'id' => 'face-scanner',
    'title' => 'Identity Verification',
    'message' => 'Please scan your face to continue.',
    'status' => 'Click \'Start\' to allow camera access.',
    'tip' => 'Keep your face straight, about 30cm from the camera, with good lighting.',
    'isEnrollment' => false,
    'onSuccess' => 'handleFaceSuccess',
    'onError' => 'handleFaceError',
    'submitUrl' => '',
    'csrfToken' => csrf_token(),
])

<div id="{{ $id }}-container" class="relative rounded-xl overflow-hidden border border-gold mb-6 bg-primary-dark shadow-2xl">
    <!-- Low-light overlay -->
    <div id="{{ $id }}-light-warning" class="hidden absolute inset-0 z-30 flex items-center justify-center bg-black/70 text-white text-center px-6">
        <div class="max-w-sm">
            <div class="text-lg font-bold mb-2">Environment too dark</div>
            <div class="text-sm">Increase lighting or move closer to the screen so FaceID can recognize you accurately.</div>
        </div>
    </div>

    <!-- Oval mask overlay -->
    <div class="absolute inset-0 z-10 pointer-events-none" style="background: radial-gradient(ellipse 55% 70% at 50% 50%, transparent 40%, #0A192F 100%); mix-blend-mode: normal;"></div>
    
    <div class="relative flex items-center justify-center pt-8 pb-4">
        <!-- Oval Camera Frame -->
        <div class="relative w-56 h-72 overflow-hidden border border-dashed border-gold rounded-[50%] shadow-[0_0_40px_rgba(212,175,55,0.15)] bg-black/50">
            <video id="{{ $id }}-video" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" style="filter: contrast(1.1) brightness(1.2);"></video>
            
            <!-- Scan line animation -->
            <div id="{{ $id }}-scan-line" class="absolute left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-gold to-transparent shadow-[0_0_15px_#D4AF37] z-20 animate-scan-vertical"></div>
        </div>
    </div>
    
    <!-- Smart Instructions Hub -->
    <div class="px-6 pb-6 text-center relative z-20">
        <h4 id="{{ $id }}-title" class="text-xl font-bold text-gold mb-2 tracking-wide uppercase">
            {{ $title }}
        </h4>
        <div id="{{ $id }}-message-box" class="inline-flex flex-col items-center gap-2 bg-primary/95 border border-gold/30 py-3 px-6 rounded-xl shadow-lg mb-3">
            <button type="button" id="{{ $id }}-start-btn" class="py-2 px-6 bg-gold text-primary-dark font-bold rounded-full shadow-md hover:bg-yellow-400 transition flex items-center justify-center gap-2 animate-pulse">
                <i class="fas fa-video"></i> {{ $isEnrollment ? 'Start Identity Scan' : 'Start FaceID Scan' }}
            </button>
            <div id="{{ $id }}-status-box" class="hidden flex items-center gap-2 mt-2">
                <i id="{{ $id }}-icon" class="fas fa-expand text-gold"></i>
                <p id="{{ $id }}-instruction" class="text-sm font-semibold text-white">Initializing camera...</p>
            </div>
        </div>
        <p id="{{ $id }}-detail" class="text-xs text-neutral-300 h-8">{{ $status }}</p>
        <p id="{{ $id }}-tip" class="text-xs text-neutral-300 mt-2">{{ $tip }}</p>
    </div>
    
    <!-- Progress Bar -->
    <div id="{{ $id }}-progress-container" class="absolute bottom-0 left-0 w-full h-1.5 bg-neutral-800 hidden">
        <div id="{{ $id }}-progress" class="h-full bg-gold w-0 transition-all duration-300 ease-out shadow-[0_0_10px_#D4AF37]"></div>
    </div>
</div>

<div class="mt-6 flex flex-col items-center gap-3">
    @isset($riskScore)
    <div class="text-center text-xs text-neutral-500">
        <span class="font-semibold uppercase tracking-widest opacity-60">Risk Score:</span> 
        <span class="text-gold font-bold ml-1">{{ number_format($riskScore, 1) }} / 100</span>
    </div>
    @endisset
    
    <span class="text-[10px] text-neutral-400 font-bold uppercase tracking-[0.2em] flex items-center justify-center gap-2 opacity-80">
        <i class="fas fa-lock text-gold"></i> Secured by LuxGuard
    </span>
</div>

<style>
    @keyframes scan_vertical {
        0% { top: 0%; opacity: 0; }
        20% { opacity: 1; }
        80% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    .animate-scan-vertical {
        animation: scan_vertical 2.5s ease-in-out infinite;
    }
</style>

<script>
    (function() {
        const scannerId = "{{ $id }}";
        const video = document.getElementById(scannerId + '-video');
        const startBtn = document.getElementById(scannerId + '-start-btn');
        const statusBox = document.getElementById(scannerId + '-status-box');
        const instruction = document.getElementById(scannerId + '-instruction');
        const detail = document.getElementById(scannerId + '-detail');
        const progressContainer = document.getElementById(scannerId + '-progress-container');
        const progress = document.getElementById(scannerId + '-progress');
        const submitUrl = "{{ $submitUrl }}";
        const csrfToken = "{{ $csrfToken }}";

        let stream = null;
        let isProcessing = false;

        startBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: 640, height: 480 }, 
                    audio: false 
                });
                video.srcObject = stream;
                
                startBtn.classList.add('hidden');
                statusBox.classList.remove('hidden');
                instruction.innerText = "Position your face in the frame";
                detail.innerText = "Analyzing environment...";
                
                // Wait for video to be ready
                video.onloadedmetadata = () => {
                    setTimeout(captureAndVerify, 2000);
                };
            } catch (err) {
                console.error("Camera error:", err);
                alert("Camera access denied or not available.");
            }
        });

        async function captureAndVerify() {
            if (isProcessing) return;
            isProcessing = true;
            
            progressContainer.classList.remove('hidden');
            instruction.innerText = "Scanning...";
            detail.innerText = "Hold still for biometric extraction";
            
            // Progress animation
            let p = 0;
            const interval = setInterval(() => {
                p += 5;
                progress.style.width = p + '%';
                if (p >= 100) clearInterval(interval);
            }, 100);

            // Capture frame
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            const faceData = canvas.toDataURL('image/jpeg', 0.9);

            try {
                const response = await fetch(submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        face_data: faceData,
                        face_verified: 'true' // For compatibility with existing flows
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    instruction.innerText = "Verified!";
                    detail.innerText = "Authentication successful.";
                    progress.classList.replace('bg-gold', 'bg-emerald-500');
                    
                    if (window["{{ $onSuccess }}"]) {
                        window["{{ $onSuccess }}"](result);
                    } else if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } else {
                    throw new Error(result.reason || "Verification failed");
                }
            } catch (err) {
                console.error("Verification error:", err);
                instruction.innerText = "Failed";
                detail.innerText = err.message;
                progress.classList.replace('bg-gold', 'bg-red-500');
                
                if (window["{{ $onError }}"]) {
                    window["{{ $onError }}"](err);
                } else {
                    setTimeout(() => {
                        isProcessing = false;
                        p = 0;
                        progress.style.width = '0%';
                        progress.classList.remove('bg-red-500');
                        progress.classList.add('bg-gold');
                        instruction.innerText = "Try again";
                        detail.innerText = "Please ensure good lighting and look straight.";
                        captureAndVerify(); // Auto retry
                    }, 3000);
                }
            }
        }

        // Cleanup on window unload
        window.addEventListener('beforeunload', () => {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    })();
</script>
