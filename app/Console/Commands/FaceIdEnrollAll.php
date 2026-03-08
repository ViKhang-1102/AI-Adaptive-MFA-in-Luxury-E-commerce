<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class FaceIdEnrollAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'faceid:enroll-all {--dry-run : Do not execute the face extraction, just report what would run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build local FaceID cache for all users by extracting Google Vision landmarks from stored identity images.';

    public function handle()
    {
        $this->info('Starting FaceID batch enrollment...');

        $python = env('PYTHON_BINARY');
        if (!$python) {
            $finder = new ExecutableFinder();
            $python = $finder->find('python') ?: $finder->find('python3');
        }

        if (!$python) {
            $this->error('Python executable not found. Set PYTHON_BINARY or ensure python is in PATH.');
            return 1;
        }

        $script = base_path('scripts/face_verify.py');
        if (!file_exists($script)) {
            $this->error('face_verify.py helper script not found at: ' . $script);
            return 1;
        }

        $count = 0;
        foreach (User::whereNotNull('identity_image')->cursor() as $user) {
            $identityPath = $user->identity_image;
            $this->info("[User {$user->id}] identity image: {$identityPath}");

            $fullPath = Storage::disk('public')->path($identityPath);
            if (!file_exists($fullPath)) {
                $this->warn("  -> File not found: {$fullPath}");
                continue;
            }

            $count++;
            if ($this->option('dry-run')) {
                continue;
            }

            // Dùng flag --enroll để đồng bộ với logic mới trong face_verify.py
            $process = new Process([$python, $script, $fullPath, $fullPath, '--enroll']);
            $process->setTimeout(30);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->error("  -> Failed to process: " . $process->getErrorOutput());
                continue;
            }

            $output = trim($process->getOutput());
            $this->info("  -> Result: {$output}");
            
            // Log thêm thông tin từ stderr
            $stderr = $process->getErrorOutput();
            if (!empty($stderr)) {
                $this->line("  -> Debug Info: " . trim($stderr));
            }
        }

        $this->info("Completed batch enrollment. Processed {$count} users.");
        return 0;
    }
}
