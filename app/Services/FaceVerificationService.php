<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class FaceVerificationService
{
    /**
     * Determine whether a cached face descriptor exists for a stored identity image.
     */
    public function hasCachedFaceDescriptor(string $storedImagePath): bool
    {
        $cacheDir = storage_path('app/face_verify_cache');
        if (!is_dir($cacheDir)) {
            return false;
        }

        if (!Storage::disk('public')->exists($storedImagePath)) {
            return false;
        }

        $fullPath = Storage::disk('public')->path($storedImagePath);
        if (!file_exists($fullPath)) {
            return false;
        }

        // Sử dụng User ID (nếu có) để chỉ kiểm tra file cache của người đó.
        $userId = $this->getUserIdFromPath($storedImagePath);
        if ($userId) {
            $cacheFile = $cacheDir . '/user_' . $userId . '.json';
            return file_exists($cacheFile);
        }

        // Fallback cho các cache cũ sử dụng hash của ảnh
        $hash = hash_file('sha256', $fullPath);
        $cacheFile = $cacheDir . '/' . $hash . '.json';
        return file_exists($cacheFile);
    }

    private function getUserIdFromPath(string $path): ?int
    {
        $base = basename($path);
        if (preg_match('/user[_-]?(\d+)/', $base, $m)) {
            return (int)$m[1];
        }
        return null;
    }

    /**
     * Compare a live face snapshot with the user's stored identity image.
     *
     * @param string $base64Snapshot Base64 encoded JPEG image from webcam
     * @param string $storedImagePath Path to the stored identity image in storage
     * @param bool $enrollment If true, we only extract landmarks and save to cache
     * @return array ['success' => bool, 'reason' => string]
     */
    public function verify(string $base64Snapshot, string $storedImagePath, bool $enrollment = false, ?int $userId = null): array
    {
        try {
            // Determine User ID for cache isolation
            if (!$userId) {
                $userId = $this->getUserIdFromPath($storedImagePath);
            }

            // Prepare images (strip data URI prefix)
            $snapshotBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64Snapshot);

            if (!Storage::disk('public')->exists($storedImagePath)) {
                Log::warning("Stored identity image not found at: " . $storedImagePath);
                return ['success' => false, 'reason' => 'Stored identity image not found.'];
            }

            $fullStoredPath = Storage::disk('public')->path($storedImagePath);

            // Đảm bảo file tồn tại và đã được ghi xong hoàn toàn (phòng race condition)
            if (!file_exists($fullStoredPath)) {
                clearstatcache(true, $fullStoredPath);
                if (!file_exists($fullStoredPath)) {
                    Log::error("Face Verification: Stored image not found on disk", ['path' => $fullStoredPath]);
                    return ['success' => false, 'reason' => 'Stored identity image not found on disk.'];
                }
            }

            // Prepare live snapshot in a temp file
            $tmpDir = storage_path('app/face_verify_tmp');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }
            $liveFilePath = $tmpDir . '/live_' . uniqid() . '.jpg';
            file_put_contents($liveFilePath, base64_decode($snapshotBase64));

            $python = env('PYTHON_BINARY');
            if ($python && !file_exists($python)) {
                $python = null;
            }
            if (!$python) {
                $finder = new ExecutableFinder();
                $python = $finder->find('python') ?: $finder->find('python3');
            }

            if (!$python) {
                @unlink($liveFilePath);
                return ['success' => false, 'reason' => 'Python interpreter not found.'];
            }

            $script = base_path('scripts/face_verify.py');

            // face_verify.py [reference] [candidate] (--user-id <id>)
            $args = [$python, $script, $fullStoredPath, $liveFilePath];
            if ($userId) {
                $args[] = '--user-id';
                $args[] = (string)$userId;
            }
            if ($enrollment) {
                $args[] = '--enroll';
            }
            
            $process = new Process($args);
            $process->setTimeout(30);
            $process->run();

            // Clean up live snapshot
            @unlink($liveFilePath);

            if (!$process->isSuccessful()) {
                Log::error('Face Verification helper failed', ['error' => $process->getErrorOutput()]);
                return ['success' => false, 'reason' => 'Face verification helper failed to run.'];
            }

            $output = trim($process->getOutput());
            $errorOutput = $process->getErrorOutput();
            
            if (!empty($errorOutput)) {
                Log::debug('Python Debug Logs:', ['stderr' => $errorOutput]);
            }

            // Tìm chuỗi JSON trong output
            $jsonStart = strpos($output, '{');
            $jsonEnd = strrpos($output, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonStr = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
                $json = json_decode($jsonStr, true);
            } else {
                $json = null;
            }

            if (!is_array($json) || !isset($json['match'])) {
                Log::error('Face Verification helper returned invalid JSON', [
                    'stdout' => $output,
                    'stderr' => $errorOutput
                ]);
                return ['success' => false, 'reason' => 'Face verification returned an unexpected response.'];
            }

            return [
                'success' => (bool)($json['match'] ?? false),
                'reason' => $json['reason'] ?? 'Unknown response from AI engine.',
                'confidence' => $json['confidence'] ?? 0,
                'used_google_vision' => $json['used_google_vision'] ?? false
            ];

        } catch (\Exception $e) {
            Log::error('Face Verification Exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'reason' => 'Internal verification error.'];
        }
    }
}
