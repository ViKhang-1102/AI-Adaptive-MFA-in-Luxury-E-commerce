

<?php $__env->startSection('title', 'Register'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto mt-12 bg-white p-8 rounded-md-lg shadow-sm">
    <h1 class="text-2xl font-bold mb-6 text-center">Register</h1>

    <form action="<?php echo e(route('register')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Full Name</label>
            <input type="text" name="name" value="<?php echo e(old('name')); ?>" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
            
            <?php $__errorArgs = ['identity_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['face_data'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
            <input type="email" name="email" value="<?php echo e(old('email')); ?>" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Select Role</label>
            <select name="role" class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                <option value="">-- Choose Role --</option>
                <option value="customer">Customer</option>
                <option value="seller">Seller</option>
            </select>
            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-4">
            <label class="block text-neutral-700 font-bold mb-2">Password</label>
            <input type="password" name="password" 
                class="w-full px-4 py-2 border rounded-md-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                required>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <span class="text-red-600 text-sm"><?php echo e($message); ?></span>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
        Already have an account? <a href="<?php echo e(route('login')); ?>" class="text-primary hover:underline">Login here</a>
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
            <a href="<?php echo e(route('google.login')); ?>" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\E-commerce2026\resources\views/auth/register.blade.php ENDPATH**/ ?>