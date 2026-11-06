<?php

declare(strict_types=1);

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\LoginUserAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Login extends Component
{
    public string $login = '';
    public string $password = '';
    public bool $remember = false;

    /**
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'login.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }

    public function mount(): mixed
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.users')
                : redirect()->route('stocks.index');
        }

        return null;
    }

    public function authenticate(LoginUserAction $loginAction): mixed
    {
        $this->validate();

        $success = $loginAction->execute($this->login, $this->password, $this->remember);

        if (! $success) {
            $this->addError('login', 'Kredensial yang diberikan tidak cocok dengan data kami.');

            return null;
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.users'));
        }

        return redirect()->intended(route('stocks.index'));
    }

    public function render(): View
    {
        return view('livewire.auth.login')
            ->layout('components.layouts.app', ['title' => 'Masuk - Stock']);
    }
}
