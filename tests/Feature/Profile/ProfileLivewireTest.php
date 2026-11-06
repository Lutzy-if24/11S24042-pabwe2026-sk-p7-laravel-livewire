<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Features\Profile\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class ProfileLivewireTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
        ]);
        Storage::fake('public');
    }

    public function test_renders_profile_page(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Profile::class)
            ->assertStatus(200)
            ->assertSee('Pengaturan Profil')
            ->assertSet('name', 'John Doe')
            ->assertSet('username', 'johndoe')
            ->assertSet('email', 'john@example.com');
    }

    public function test_update_profile_validation_errors(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Profile::class)
            ->set('name', '')
            ->set('username', 'ab')
            ->set('email', 'not-an-email')
            ->call('updateProfileInformation')
            ->assertHasErrors(['name', 'username', 'email']);
    }

    public function test_update_profile_information_with_photo(): void
    {
        $this->actingAs($this->user);

        $photo = UploadedFile::fake()->image('avatar.jpg');

        Livewire::test(Profile::class)
            ->set('name', 'John Updated')
            ->set('username', 'johnupdated')
            ->set('email', 'johnupdated@example.com')
            ->set('photo', $photo)
            ->call('updateProfileInformation');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'John Updated',
            'username' => 'johnupdated',
            'email' => 'johnupdated@example.com',
        ]);

        $this->assertNotNull($this->user->fresh()->profile_photo_path);
        Storage::disk('public')->assertExists($this->user->fresh()->profile_photo_path);
    }

    public function test_remove_profile_photo(): void
    {
        $this->actingAs($this->user);

        $photo = UploadedFile::fake()->image('avatar_remove.jpg');
        $path = $photo->store('profile-photos', 'public');
        $this->user->update(['profile_photo_path' => $path]);

        Livewire::test(Profile::class)
            ->call('removeProfilePhoto');

        $this->assertNull($this->user->fresh()->profile_photo_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_update_password_validation_errors(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Profile::class)
            ->set('current_password', '')
            ->set('new_password', '123')
            ->set('new_password_confirmation', '456')
            ->call('updatePassword')
            ->assertHasErrors(['current_password', 'new_password']);
    }

    public function test_update_password_fails_with_wrong_current_password(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Profile::class)
            ->set('current_password', 'wrong-pass')
            ->set('new_password', 'newsecret123')
            ->set('new_password_confirmation', 'newsecret123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }

    public function test_update_password_succeeds(): void
    {
        $this->actingAs($this->user);

        Livewire::test(Profile::class)
            ->set('current_password', 'password123')
            ->set('new_password', 'newsecret123')
            ->set('new_password_confirmation', 'newsecret123')
            ->call('updatePassword');

        $this->assertTrue(Hash::check('newsecret123', $this->user->fresh()->password));
    }
}
