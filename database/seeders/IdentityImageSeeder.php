<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class IdentityImageSeeder extends Seeder
{
    public function run()
    {
        // Path to a sample face image (you can replace this with an actual file if available)
        // For now, we'll try to find any existing profile image or use a placeholder
        $user = User::where('email', 'customer1@example.com')->first();
        
        if ($user) {
            $identityPath = 'identity_images/user_' . $user->id . '.jpg';
            
            // If the user already has a profile picture, we use it as identity image for testing
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->copy($user->profile_photo_path, $identityPath);
                $user->update(['identity_image' => $identityPath]);
                $this->command->info("Updated Customer1 identity image using profile photo.");
            } else {
                // Try to find any image in public/uploads or similar to use as mock
                $this->command->warn("No profile photo found for Customer1. Please upload an identity image in profile settings.");
            }
        }
    }
}
