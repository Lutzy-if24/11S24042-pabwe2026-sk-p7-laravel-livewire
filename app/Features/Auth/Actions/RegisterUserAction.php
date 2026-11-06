<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class RegisterUserAction
{
    /**
     * Register a new user with the default member role and log them in.
     *
     * @param array{name: string, username: string, email: string, password: string} $data
     */
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'member',
        ]);

        Auth::login($user);
        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        return $user;
    }
}
