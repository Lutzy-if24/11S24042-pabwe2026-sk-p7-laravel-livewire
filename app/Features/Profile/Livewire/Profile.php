<?php

declare(strict_types=1);

namespace App\Features\Profile\Livewire;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public $photo = null;

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
    }

    public function updateProfileInformation(ProfileService $profileService): void
    {
        $userId = (int) auth()->id();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar pada akun lain.',
            'photo.image' => 'File foto profil harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        /** @var User $user */
        $user = auth()->user();
        $file = $this->photo instanceof UploadedFile ? $this->photo : null;

        $profileService->updateProfile(
            user: $user,
            name: $this->name,
            username: $this->username,
            email: $this->email,
            photo: $file
        );

        $this->photo = null;
        session()->flash('success_profile', 'Profil berhasil diperbarui.');
    }

    public function removeProfilePhoto(ProfileService $profileService): void
    {
        /** @var User $user */
        $user = auth()->user();

        $profileService->updateProfile(
            user: $user,
            name: $this->name,
            username: $this->username,
            email: $this->email,
            photo: null,
            removePhoto: true
        );

        session()->flash('success_profile', 'Foto profil berhasil dihapus.');
    }

    public function updatePassword(ProfileService $profileService): void
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        /** @var User $user */
        $user = auth()->user();

        try {
            $profileService->updatePassword(
                user: $user,
                currentPassword: $this->current_password,
                newPassword: $this->new_password
            );

            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
            session()->flash('success_password', 'Kata sandi berhasil diperbarui.');
        } catch (InvalidArgumentException $e) {
            $this->addError('current_password', $e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.profile.profile', [
            'user' => auth()->user(),
        ])->layout('components.layouts.app', ['title' => 'Profil Pengguna - Stock']);
    }
}
