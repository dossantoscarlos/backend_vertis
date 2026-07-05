<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Support\SupportAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $ip = request()->ip();
        $lockoutKey = 'login_lockout:' . $ip;
        $attemptsKey = 'login_attempts:' . $ip;

        // Check if currently locked out
        if (Cache::has($lockoutKey)) {
            $lockoutTimestamp = Cache::get($lockoutKey);
            if (time() < $lockoutTimestamp) {
                $remainingSeconds = $lockoutTimestamp - time();
                $remainingMinutes = (int) ceil($remainingSeconds / 60);
                throw ValidationException::withMessages([
                    'email' => ["Muitas tentativas de login. Por favor, aguarde {$remainingMinutes} minutos."],
                ]);
            }
        }

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
            $this->handleFailedAttempt($attemptsKey, $lockoutKey);

            throw ValidationException::withMessages([
                'email' => ['E-mail, senha ou status inválidos.'],
            ]);
        }

        $user = Auth::user();

        if (! $user instanceof User || ! $supportAccess->canAccess($user)) {
            Auth::logout();

            $this->handleFailedAttempt($attemptsKey, $lockoutKey);

            throw ValidationException::withMessages([
                'email' => ['Este acesso é exclusivo para perfis autorizados.'],
            ]);
        }

        // Clear attempts on success
        Cache::forget($attemptsKey);
        Cache::forget($lockoutKey);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $url = null;
        $referer = request()->headers->get('referer');
        if ($referer) {
            $parsed = parse_url($referer);
            if (isset($parsed['host'])) {
                $scheme = $parsed['scheme'] ?? 'http';
                $hostAndPort = $parsed['host'];
                if (isset($parsed['port'])) {
                    $hostAndPort .= ':' . $parsed['port'];
                }
                $url = "{$scheme}://{$hostAndPort}/dashboard";
            }
        }

        if (!$url) {
            $scheme = request()->header('X-Forwarded-Proto') ?: request()->getScheme();
            $hostAndPort = request()->header('X-Forwarded-Host') ?: request()->getHttpHost();
            $url = "{$scheme}://{$hostAndPort}/dashboard";
        }

        return redirect()->to($url);
    }

    private function handleFailedAttempt(string $attemptsKey, string $lockoutKey): void
    {
        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addDays(1));

        if ($attempts >= 3) {
            $exponent = (int) floor(($attempts - 3) / 3);
            $waitMinutes = 12 * (3 ** $exponent);
            $lockoutEndTimestamp = time() + ($waitMinutes * 60);
            Cache::put($lockoutKey, $lockoutEndTimestamp, $waitMinutes * 60);

            throw ValidationException::withMessages([
                'email' => ["Limite de tentativas excedido. Aguarde {$waitMinutes} minutos."],
            ]);
        }
    }

    public function render(): View
    {
        return view('livewire.auth.login-form')->layout('layouts.auth');
    }
}
