<?php

declare(strict_types=1);

namespace App\Features\Auth\Actions;

use Illuminate\Support\Facades\Auth;

final class LoginUserAction
{
    /**
     * Attempt to authenticate a user using either username or email.
     */
    public function execute(string $login, string $password, bool $remember = false): bool
    {
        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $login,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            if (request()->hasSession()) {
                request()->session()->regenerate();
            }

            return true;
        }

        return false;
    }
}
