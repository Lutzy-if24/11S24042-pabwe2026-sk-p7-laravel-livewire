<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

final class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProfileService;
        Storage::fake('public');
    }

    public function test_update_profile_without_photo(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'olduser',
            'email' => 'old@example.com',
        ]);

        $updated = $this->service->updateProfile(
            user: $user,
            name: 'New Name',
            username: 'newuser',
            email: 'new@example.com'
        );

        $this->assertSame('New Name', $updated->name);
        $this->assertSame('newuser', $updated->username);
        $this->assertSame('new@example.com', $updated->email);
    }

    public function test_update_profile_with_photo_and_removal(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('profile.png');

        $updated = $this->service->updateProfile(
            user: $user,
            name: $user->name,
            username: $user->username,
            email: $user->email,
            photo: $file
        );

        $photoPath = $updated->profile_photo_path;
        $this->assertNotNull($photoPath);
        Storage::disk('public')->assertExists($photoPath);

        // Upload replacement photo
        $newFile = UploadedFile::fake()->image('profile_new.png');
        $replaced = $this->service->updateProfile(
            user: $updated,
            name: $user->name,
            username: $user->username,
            email: $user->email,
            photo: $newFile
        );

        Storage::disk('public')->assertMissing($photoPath);
        Storage::disk('public')->assertExists($replaced->profile_photo_path);

        // Remove photo
        $removed = $this->service->updateProfile(
            user: $replaced,
            name: $user->name,
            username: $user->username,
            email: $user->email,
            photo: null,
            removePhoto: true
        );

        $this->assertNull($removed->profile_photo_path);
        Storage::disk('public')->assertMissing($replaced->profile_photo_path);
    }

    public function test_update_password_success_and_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $result = $this->service->updatePassword($user, 'old-password', 'new-password-123');
        $this->assertTrue($result);
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));

        $this->expectException(InvalidArgumentException::class);
        $this->service->updatePassword($user, 'wrong-password', 'another-password');
    }
}
