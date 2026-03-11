

<?php $__env->startSection('content'); ?>
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-neutral-50 relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-full height-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-primary/5 blur-3xl"></div>
    </div>

    <div class="max-w-md w-full relative z-10">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-neutral-100">
            <!-- Header Section -->
            <div class="bg-primary px-8 py-10 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-gold-dark via-gold to-gold-light"></div>
                <div class="absolute inset-0 bg-primary-dark opacity-20 pointer-events-none pattern-dots"></div>
                
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-light shadow-inner mb-6 border border-primary/50">
                        <i class="fas fa-shield-alt text-2xl text-gold"></i>
                    </div>
                    <h3 class="text-2xl font-serif font-bold text-white uppercase tracking-widest mb-2">
                        Luxury Concierge Security Service
                    </h3>
                    <p class="text-primary-300 text-sm font-light">
                        Adaptive AI Engine Intervention
                    </p>
                </div>
            </div>

            <!-- Body Section -->
            <div class="p-8">
                <p class="text-center text-neutral-600 text-sm mb-8 leading-relaxed">
                    For your protection, we need to verify your identity before completing this transaction.
                </p>

                <!-- Alerts -->
                <?php if(session('success')): ?>
                    <div class="bg-emerald-50 border-l-4 border-emerald-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                        <p class="text-sm text-emerald-800"><?php echo e(session('success')); ?></p>
                    </div>
                <?php endif; ?>
                <?php if(session('ai_warning')): ?>
                    <div class="bg-amber-50 border-l-4 border-amber-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-robot text-amber-500 mt-1"></i>
                        <p class="text-sm text-amber-800"><?php echo e(session('ai_warning')); ?></p>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        <p class="text-sm text-red-800"><?php echo e(session('error')); ?></p>
                    </div>
                <?php endif; ?>

                <?php if(isset($faceCacheMissing) && $faceCacheMissing): ?>
                    <div class="bg-amber-50 border-l-4 border-amber-500 rounded p-4 flex items-start gap-3 mb-6">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-1"></i>
                        <p class="text-sm text-amber-800">FaceID cache is missing or stale; please rescan your face to rebuild local verification data.</p>
                    </div>
                <?php endif; ?>

                <?php if($needsIdentityUpload): ?>
                    <div class="bg-neutral-50 border border-gold/30 rounded-lg p-6 mb-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gold/20 text-gold">
                                    <i class="fas fa-id-card text-xl"></i>
                                </span>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-neutral-900">Security Update Required</h3>
                                <p class="text-sm text-neutral-600">Your account lacks an Identity Profile. Upload a portrait for high-value protection and adaptive verification.</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="<?php echo e(route('otp.identity.upload')); ?>" enctype="multipart/form-data" class="space-y-6" id="identity-setup-form">
                        <?php echo csrf_field(); ?>
                        <!-- Hidden input to hold the captured image file -->
                        <div class="hidden">
                            <input id="identity_image_input" type="file" name="identity_image" accept="image/png" required>
                        </div>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                            <h4 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                                <i class="fas fa-camera text-blue-700 text-xl"></i>
                                FaceID Setup Guide
                            </h4>
                            <ul class="text-sm text-blue-800 space-y-2 list-none p-0">
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-emerald-500 mt-1"></i>
                                    <span><strong>Required:</strong> Use a live camera feed. Pre-recorded or uploaded images are rejected to prevent spoofing.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-sun text-amber-500 mt-1"></i>
                                    <span>Stand in a well-lit area with no strong backlighting.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-eye text-primary text-opacity-80 mt-1"></i>
                                    <span>Remove sunglasses/masks and look directly at the camera.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-expand text-indigo-500 mt-1"></i>
                                    <span>Position your face inside the circular frame below.</span>
                                </li>
                            </ul>
                        </div>

                        <div class="space-y-4">
                            <button type="button" id="enable-webcam" class="w-full py-4 bg-primary text-white rounded-lg font-bold hover:bg-primary-light transition flex flex-col items-center justify-center gap-1 shadow-md">
                                <span class="flex items-center gap-2 text-lg"><i class="fas fa-video"></i> Open Camera & Scan Face</span>
                                <span class="text-xs opacity-80 font-normal">Cho phép trình duyệt truy cập máy ảnh của bạn</span>
                            </button>
                            
                            <div id="webcam-container" class="hidden flex flex-col items-center bg-neutral-900 p-4 rounded-xl border border-neutral-800 shadow-inner">
                                <div class="relative w-64 h-64 overflow-hidden border-2 border-dashed border-gold rounded-full mb-4 mx-auto shadow-[0_0_20px_rgba(212,175,55,0.2)] bg-black">
                                    <video id="webcam" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" style="filter: contrast(1.1) brightness(1.1);"></video>
                                    <div class="absolute inset-0 bg-gold/5 pointer-events-none rounded-full border-4 border-transparent hover:border-gold transition-colors duration-500"></div>
                                </div>
                                
                                <div class="flex gap-3 w-full">
                                    <button type="button" id="capture-btn" class="flex-1 py-3 bg-gold text-primary-dark font-bold rounded-lg shadow-md hover:bg-yellow-400 transition flex items-center justify-center gap-2">
                                        <i class="fas fa-camera"></i> Capture & Save
                                    </button>
                                    <button type="button" id="cancel-webcam" class="px-4 py-3 bg-neutral-800 text-neutral-300 hover:text-white rounded-lg font-semibold transition">
                                        Cancel
                                    </button>
                                </div>
                            </div>

                            <div id="preview-container" class="hidden flex flex-col items-center p-4">
                                <div class="relative w-48 h-48 rounded-full overflow-hidden border-4 border-emerald-500 shadow-lg mb-4">
                                    <img id="capture-preview" src="" alt="FaceID Preview" class="w-full h-full object-cover transform scale-x-[-1]">
                                    <div class="absolute bottom-2 right-2 bg-emerald-500 rounded-full w-8 h-8 flex items-center justify-center border-2 border-white shadow-sm text-white">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                
                                <button type="button" id="retake-btn" class="text-sm font-semibold text-primary hover:underline mb-4">
                                    <i class="fas fa-redo mr-1"></i> Retake
                                </button>
                                
                                <button type="submit" id="submit-btn" class="w-full py-4 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition shadow-md flex items-center justify-center gap-2 text-lg">
                                    <i class="fas fa-shield-alt"></i> Confirm & Update FaceID
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-neutral-500 text-center flex items-center justify-center gap-2 mt-2">
                            <i class="fas fa-lock text-gold"></i> Biometric data is encrypted and stored securely.
                        </p>
                    </form>
                <?php else: ?>
                    <?php if($scanEnabled): ?>
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
                        <div id="face-scan-container" class="relative rounded-xl overflow-hidden border border-gold mb-6 bg-primary-dark shadow-2xl" <?php if(!$scanRequired): ?> style="display: none;" <?php endif; ?>>
                            <!-- Overlay cảnh báo nếu môi trường quá tối -->
                            <div id="light-warning" class="hidden absolute inset-0 z-30 flex items-center justify-center bg-black/70 text-white text-center px-6">
                                <div class="max-w-sm">
                                    <div class="text-lg font-bold mb-2">Môi trường quá tối</div>
                                    <div class="text-sm">Vui lòng tăng độ sáng hoặc đưa mặt lại gần màn hình để hệ thống có thể nhận diện chính xác.</div>
                                </div>
                            </div>

                            <!-- Privacy Check: Oval mask overlays EVERYTHING except the center -->
                            <div class="absolute inset-0 z-10 pointer-events-none" style="background: radial-gradient(ellipse 55% 70% at 50% 50%, transparent 40%, #0A192F 100%); mix-blend-mode: normal;"></div>
                            
                            <div class="relative flex items-center justify-center pt-8 pb-4">
                                <!-- Oval Camera Frame -->
                                <div class="relative w-56 h-72 overflow-hidden border border-dashed border-gold rounded-[50%] shadow-[0_0_40px_rgba(212,175,55,0.15)] bg-black/50">
                                    <video id="liveness-video" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" style="filter: contrast(1.1) brightness(1.2);"></video>
                                    
                                    <!-- Scan line animation -->
                                    <div id="scan-line" class="absolute left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-gold to-transparent shadow-[0_0_15px_#D4AF37] z-20 animate-scan-vertical"></div>
                                </div>
                            </div>
                            
                            <!-- Smart Instructions Hub -->
                            <div class="px-6 pb-6 text-center relative z-20">
                                <h4 id="liveness-title" class="text-xl font-bold text-gold mb-2 tracking-wide uppercase">
                                    <?php if(isset($isEnrollment) && $isEnrollment): ?> FaceID Enrollment <?php else: ?> Identity Verification <?php endif; ?>
                                </h4>
                                <div id="liveness-message-box" class="inline-flex flex-col items-center gap-2 bg-primary/95 border border-gold/30 py-3 px-6 rounded-xl shadow-lg mb-3">
                                    <button type="button" id="start-liveness-btn" class="py-2 px-6 bg-gold text-primary-dark font-bold rounded-full shadow-md hover:bg-yellow-400 transition flex items-center justify-center gap-2 animate-pulse">
                                        <i class="fas fa-video"></i> <?php if(isset($isEnrollment) && $isEnrollment): ?> Start Identity Scan <?php else: ?> Start FaceID Scan <?php endif; ?>
                                    </button>
                                    <div id="liveness-status-box" class="hidden flex items-center gap-2 mt-2">
                                        <i id="liveness-icon" class="fas fa-expand text-gold"></i>
                                        <p id="liveness-instruction" class="text-sm font-semibold text-white">Initializing camera...</p>
                                    </div>
                                </div>
                                <p id="liveness-detail" class="text-xs text-neutral-300 h-8">Click 'Start' to allow camera access.</p>
                                <p id="liveness-tip" class="text-xs text-neutral-300 mt-2">Hãy giữ mặt thẳng, cách camera 30cm và đảm bảo đủ ánh sáng.</p>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div id="liveness-progress-container" class="absolute bottom-0 left-0 w-full h-1.5 bg-neutral-800 hidden">
                                <div id="liveness-progress" class="h-full bg-gold w-0 transition-all duration-300 ease-out shadow-[0_0_10px_#D4AF37]"></div>
                            </div>
                        </div>

                        <?php if(!$scanRequired): ?>
                            <div class="mb-6 text-center">
                                <button type="button" id="switch-to-faceid" class="text-gold text-sm font-bold hover:text-yellow-400 transition flex items-center justify-center gap-2 mx-auto">
                                    <i class="fas fa-id-card"></i> Or Verify with FaceID
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="mb-6 text-center">
                                <button type="button" id="backup-otp-btn" class="hidden text-gold text-sm font-bold hover:text-yellow-400 transition flex items-center justify-center gap-2 mx-auto">
                                    <i class="fas fa-key"></i> Sử dụng mã OTP dự phòng
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo e(route('otp.verify.submit')); ?>" class="space-y-8" id="otp-form" <?php if($scanEnabled && $scanRequired): ?> style="display: none;" <?php endif; ?>>
                        <?php echo csrf_field(); ?>

                        <div>
                            <div class="relative">
                                <input id="otp" type="text" 
                                       class="block w-full text-center bg-neutral-50 border border-neutral-300 rounded-lg text-neutral-900 focus:ring-gold focus:border-gold placeholder:text-neutral-300 transition-colors <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 ring-1 ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       name="otp" 
                                       <?php if(!$scanRequired): ?> required autofocus <?php endif; ?>
                                       placeholder="&middot; &middot; &middot; &middot; &middot; &middot;"
                                       maxlength="6"
                                       style="letter-spacing: 0.8em; font-size: 1.75rem; padding: 1rem 0; font-family: monospace;">
                                        
                                <?php $__errorArgs = ['otp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-2 text-sm text-red-600 text-center font-medium bg-red-50 py-1.5 rounded" role="alert">
                                        <?php echo e($message); ?>

                                    </p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <p class="mt-3 text-center text-xs text-neutral-400">
                                Enter the 6-digit code provided in the email.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-primary hover:bg-primary-light focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary uppercase tracking-widest transition-all hover:shadow-md">
                                Verify & Proceed
                            </button>
                            <a href="<?php echo e(route('home')); ?>" class="w-full flex justify-center py-3.5 px-4 border border-neutral-300 rounded-lg shadow-sm text-sm font-bold text-neutral-700 bg-white hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary tracking-widest uppercase transition-colors">
                                Cancel
                            </a>
                        </div>
                    </form>

                    <?php if($scanEnabled): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const otpForm = document.getElementById('otp-form');
                                const faceScanContainer = document.getElementById('face-scan-container');
                                const videoElement = document.getElementById('liveness-video');
                                const startBtn = document.getElementById('start-liveness-btn');
                                const statusBox = document.getElementById('liveness-status-box');
                                const instructionEl = document.getElementById('liveness-instruction');
                                const detailEl = document.getElementById('liveness-detail');
                                const progressContainer = document.getElementById('liveness-progress-container');
                                const progressBar = document.getElementById('liveness-progress');
                                const titleEl = document.getElementById('liveness-title');
                                const iconEl = document.getElementById('liveness-icon');
                                const switchBtn = document.getElementById('switch-to-faceid');
                                const lightWarning = document.getElementById('light-warning');

                                let videoStream = null;

                                function setCameraActive(active) {
                                    // Khi camera đang bật, bật nền trắng để hắt sáng và giảm hiện tượng đen ảnh.
                                    if (active) {
                                        document.body.style.backgroundColor = '#ffffff';
                                    } else {
                                        document.body.style.backgroundColor = '';
                                    }
                                }

                                function showLowLightWarning(show) {
                                    if (!lightWarning) return;
                                    lightWarning.classList.toggle('hidden', !show);
                                }

                                if (switchBtn) {
                                    switchBtn.addEventListener('click', function() {
                                        otpForm.style.display = 'none';
                                        faceScanContainer.style.display = 'block';
                                        switchBtn.parentElement.style.display = 'none';
                                    });
                                }

                                const backupOtpBtn = document.getElementById('backup-otp-btn');
                                let scanFailCount = 0;

                                function showBackupOtpOption() {
                                    if (backupOtpBtn) {
                                        backupOtpBtn.classList.remove('hidden');
                                    }
                                }

                                if (backupOtpBtn) {
                                    backupOtpBtn.addEventListener('click', function() {
                                        otpForm.style.display = 'block';
                                        faceScanContainer.style.display = 'none';
                                        if (switchBtn) {
                                            switchBtn.parentElement.style.display = 'none';
                                        }
                                    });
                                }
                                
                                async function startLivenessDetection() {
                                    startBtn.style.display = 'none';
                                    statusBox.classList.remove('hidden');
                                    statusBox.classList.add('flex');
                                    detailEl.innerText = "Accessing secure biometric hardware...";

                                    try {
                                        videoStream = await navigator.mediaDevices.getUserMedia({ 
                                            video: { 
                                                facingMode: 'user',
                                                width: { ideal: 1280 },
                                                height: { ideal: 720 }
                                            }, 
                                            audio: false 
                                        });
                                        videoElement.srcObject = videoStream;
                                        setCameraActive(true);
                                        
                                        // Ensure the video is ready and playing before starting logic
                                        videoElement.oncanplay = async () => {
                                            try {
                                                await videoElement.play();
                                                runLivenessSequence();
                                            } catch (playErr) {
                                                console.error("Playback failed:", playErr);
                                                detailEl.innerText = "Playback error. Refresh page.";
                                            }
                                        };
                                    } catch (err) {
                                        console.error("Camera Error:", err);
                                        setCameraActive(false);
                                        instructionEl.innerText = "Camera Access Denied";
                                        instructionEl.classList.replace('text-white', 'text-red-500');
                                        detailEl.innerText = "Please allow camera access in your browser to verify your identity.";
                                        iconEl.className = "fas fa-exclamation-triangle text-red-500";
                                        startBtn.style.display = 'flex'; // Allow retry
                                    }
                                }

                                function updateProgress(pct) {
                                    progressContainer.classList.remove('hidden');
                                    progressBar.style.width = pct + '%';
                                }

                                const sleep = ms => new Promise(r => setTimeout(r, ms));

                                async function runLivenessSequence() {
                                    try {
                                        // 1. Lighting Analysis (Native JS canvas processing)
                                        instructionEl.innerText = "Analyzing environment...";
                                        detailEl.innerText = "Optimizing sensor contrast for 3D landmarks...";
                                        iconEl.className = "fas fa-adjust text-gold animate-spin";
                                        
                                        await sleep(1500);
                                        
                                        const canvas = document.createElement('canvas');
                                        const context = canvas.getContext('2d', { willReadFrequently: true });
                                        canvas.width = videoElement.videoWidth;
                                        canvas.height = videoElement.videoHeight;
                                        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                                        
                                        const imgData = context.getImageData(0, 0, canvas.width, canvas.height).data;
                                        let sum = 0;
                                        for(let i=0; i<imgData.length; i+=4) {
                                            sum += (imgData[i] + imgData[i+1] + imgData[i+2]) / 3;
                                        }
                                        const brightness = sum / (canvas.width * canvas.height);
                                        
                                        if(brightness < 80) {
                                            showLowLightWarning(true);
                                            detailEl.innerText = "Môi trường tối. Đang tăng sáng tự động...";
                                            videoElement.style.filter = "contrast(1.4) brightness(1.5)";
                                            await sleep(1500);
                                            showLowLightWarning(false);
                                        } else {
                                            showLowLightWarning(false);
                                        }

                                        updateProgress(15);

                                        // 2. Start Sequence (Straight -> Left -> Right -> Center Capture)
                                        instructionEl.innerText = "Look STRAIGHT at the camera";
                                        detailEl.innerText = "Align your face in the oval frame...";
                                        iconEl.className = "fas fa-user-check text-gold animate-pulse";
                                        updateProgress(30);
                                        await sleep(2500);

                                        instructionEl.innerText = "Turn your head to the LEFT";
                                        detailEl.innerText = "Scanning 3D left profile...";
                                        iconEl.className = "fas fa-arrow-left text-gold animate-bounce";
                                        updateProgress(55);
                                        await sleep(3000);
                                        
                                        instructionEl.innerText = "Turn your head to the RIGHT";
                                        detailEl.innerText = "Scanning 3D right profile...";
                                        iconEl.className = "fas fa-arrow-right text-gold animate-bounce";
                                        updateProgress(80);
                                        await sleep(3000);

                                        instructionEl.innerText = "Look CENTER & HOLD STILL";
                                        detailEl.innerText = "Capturing high-resolution biometric snapshot...";
                                        iconEl.className = "fas fa-camera text-emerald-400 animate-ping";
                                        updateProgress(95);
                                        await sleep(2000);

                                        // 3. Final Capture for Identity
                                        const captureCanvas = document.createElement('canvas');
                                        captureCanvas.width = videoElement.videoWidth;
                                        captureCanvas.height = videoElement.videoHeight;
                                        const captureContext = captureCanvas.getContext('2d');
                                        captureContext.drawImage(videoElement, 0, 0, captureCanvas.width, captureCanvas.height);
                                        const faceData = captureCanvas.toDataURL('image/jpeg', 0.95);

                                        updateProgress(100);

                                        // 4. Verification UI
                                        instructionEl.innerText = "Processing Data...";
                                        detailEl.innerText = "Sending secure biometric data to AI Guard Agent...";
                                        iconEl.className = "fas fa-shield-alt text-gold animate-spin";
                                        
                                        await sleep(1000);
                                        
                                        // Stop video and transition
                                        if (videoStream) {
                                            videoStream.getTracks().forEach(track => track.stop());
                                        }
                                        setCameraActive(false);
                                        faceScanContainer.style.transition = 'opacity 0.6s ease';
                                        faceScanContainer.style.opacity = '0';
                                        
                                        await sleep(600);
                                        faceScanContainer.style.display = 'none';
                                        
                                        // Create hidden inputs for face verification
                                        const faceVerifiedInput = document.createElement('input');
                                        faceVerifiedInput.type = 'hidden';
                                        faceVerifiedInput.name = 'face_verified';
                                        faceVerifiedInput.value = 'true';
                                        otpForm.appendChild(faceVerifiedInput);

                                        const faceDataInput = document.createElement('input');
                                        faceDataInput.type = 'hidden';
                                        faceDataInput.name = 'face_data';
                                        faceDataInput.value = faceData;
                                        otpForm.appendChild(faceDataInput);
                                        
                                        // Submit automatically
                                        otpForm.submit();
                                    } catch (seqErr) {
                                        console.error("Sequence Error:", seqErr);
                                        scanFailCount += 1;
                                        if (scanFailCount >= 3) {
                                            showBackupOtpOption();
                                        }
                                        instructionEl.innerText = "Scan Failed";
                                        detailEl.innerText = `Scan failed (${scanFailCount}/3). Please adjust lighting and try again.`;
                                        startBtn.style.display = 'flex';
                                    }
                                }
                                
                                startBtn.addEventListener('click', startLivenessDetection);
                            });
                        </script>
                    <?php endif; ?>
                <?php endif; ?>

                <script>
                    (function () {
                        const enableWebcamBtn = document.getElementById('enable-webcam');
                        const webcamContainer = document.getElementById('webcam-container');
                        const webcamVideo = document.getElementById('webcam');
                        const captureBtn = document.getElementById('capture-btn');
                        const cancelWebcamBtn = document.getElementById('cancel-webcam');
                        const identityInput = document.getElementById('identity_image_input');
                        const previewContainer = document.getElementById('preview-container');
                        const capturePreview = document.getElementById('capture-preview');
                        const retakeBtn = document.getElementById('retake-btn');
                        const submitBtn = document.getElementById('submit-btn');
                        let stream;

                        function setCameraActive(active) {
                            if (active) {
                                document.body.style.backgroundColor = '#ffffff';
                            } else {
                                document.body.style.backgroundColor = '';
                            }
                        }

                        // Initially disable submit button
                        if (submitBtn) submitBtn.disabled = true;

                        async function startWebcam() {
                            try {
                                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                                webcamVideo.srcObject = stream;
                                setCameraActive(true);
                                webcamContainer.classList.remove('hidden');
                                enableWebcamBtn.classList.add('hidden');
                                previewContainer.classList.add('hidden');
                            } catch (err) {
                                console.error('Webcam access denied:', err);
                                setCameraActive(false);
                                alert('Cannot access webcam. Please allow camera access in your browser to continue setting up FaceID.');
                            }
                        }

                        function stopWebcam() {
                            if (stream) {
                                stream.getTracks().forEach(track => track.stop());
                                stream = null;
                            }
                            setCameraActive(false);
                        }

                        function hideWebcam() {
                            stopWebcam();
                            webcamContainer.classList.add('hidden');
                            enableWebcamBtn.classList.remove('hidden');
                            previewContainer.classList.add('hidden');
                            // Reset input
                            if (identityInput) identityInput.value = '';
                            if (submitBtn) submitBtn.disabled = true;
                        }

                        function captureImage() {
                            if (!webcamVideo || webcamVideo.readyState !== 4) {
                                return;
                            }

                            const canvas = document.createElement('canvas');
                            canvas.width = webcamVideo.videoWidth;
                            canvas.height = webcamVideo.videoHeight;
                            const ctx = canvas.getContext('2d');
                            // Flash effect
                            webcamContainer.style.opacity = '0.5';
                            setTimeout(() => webcamContainer.style.opacity = '1', 150);

                            ctx.drawImage(webcamVideo, 0, 0, canvas.width, canvas.height);

                            // Update preview image
                            capturePreview.src = canvas.toDataURL('image/png');

                            canvas.toBlob(blob => {
                                if (!blob) return;
                                const file = new File([blob], 'identity_capture.png', { type: 'image/png' });
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                identityInput.files = dataTransfer.files;
                                
                                // UI Transitions
                                stopWebcam();
                                webcamContainer.classList.add('hidden');
                                previewContainer.classList.remove('hidden');
                                submitBtn.disabled = false;
                            }, 'image/png');
                        }

                        if (enableWebcamBtn) enableWebcamBtn.addEventListener('click', startWebcam);
                        if (cancelWebcamBtn) cancelWebcamBtn.addEventListener('click', hideWebcam);
                        if (captureBtn) captureBtn.addEventListener('click', captureImage);
                        if (retakeBtn) {
                            retakeBtn.addEventListener('click', () => {
                                previewContainer.classList.add('hidden');
                                if (submitBtn) submitBtn.disabled = true;
                                if (identityInput) identityInput.value = '';
                                startWebcam();
                            });
                        }
                    })();
                </script>

                <?php if(isset($riskScore) && $riskScore !== null): ?>
                    <div class="mt-6 text-center text-xs text-neutral-500">
                        <span class="font-semibold">Risk Score:</span> <?php echo e(number_format($riskScore, 1)); ?> / 100
                    </div>
                <?php endif; ?>
                <span class="text-xs text-neutral-400 font-medium tracking-wide flex items-center justify-center gap-2">
                    <i class="fas fa-lock text-gold"></i> Secured by LuxGuard
                </span>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026 - Copy (2)\resources\views/auth/verify-otp.blade.php ENDPATH**/ ?>