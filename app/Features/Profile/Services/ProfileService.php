<?php

declare(strict_types=1);

namespace App\Features\Profile\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

final class ProfileService
{
    /**
     * Update user profile information and optionally update profile photo.
     */
    public function updateProfile(
        User $user,
        string $name,
        string $username,
        string $email,
        ?UploadedFile $photo = null,
        bool $removePhoto = false
    ): User {
        $photoPath = $user->profile_photo_path;

        if ($removePhoto && $photoPath !== null) {
            Storage::disk('public')->delete($photoPath);
            $photoPath = null;
        }

        if ($photo !== null) {
            if ($user->profile_photo_path !== null) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $photoPath = $photo->store('profile-photos', 'public');
        }

        $user->update([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'profile_photo_path' => $photoPath,
        ]);

        return $user->fresh() ?? $user;
    }

    /**
     * Update user password after verifying current password.
     *
     * @throws InvalidArgumentException
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new InvalidArgumentException('Kata sandi saat ini tidak cocok.');
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return true;
    }
}
