<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Support\SupportAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(SupportAccess $supportAccess): mixed
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $authenticated = Auth::attempt([
            'email' => str($credentials['email'])->lower()->toString(),
            'password' => $credentials['password'],
            'status' => 'ativo',
        ], $this->remember);

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => ['E-mail, senha ou status inválidos.'],
            ]);
        }

        $user = Auth::user();

        if (! $user instanceof User || ! $supportAccess->canAccess($user)) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => ['Este acesso é exclusivo para perfis autorizados.'],
            ]);
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        return $this->redirectRoute('dashboard', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login-form')->layout('layouts.auth');
    }
}
