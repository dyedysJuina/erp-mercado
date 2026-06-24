<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $lembrar = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'E-mail inválido.',
            'password.required' => 'Informe sua senha.',
        ];
    }

    public function entrar()
    {
        $this->validate();

        $throttleKey = mb_strtolower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Muitas tentativas. Aguarde " . ceil($seconds / 60) . " minuto(s).");
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->lembrar)) {
            $user = Auth::user();

            if (!$user->ativo) {
                Auth::logout();
                $this->addError('email', 'Usuário inativo. Contate o administrador.');
                return;
            }

            RateLimiter::clear($throttleKey);
            request()->session()->regenerate();

            DB::table('users')
                ->where('id', Auth::id())
                ->update(['last_login_at' => now()]);

            if ($user->hasRole('Operador')) {
                return $this->redirect(route('vendas'), navigate: true);
            }

            return $this->redirect(route('dashboard'), navigate: true);
        }

        RateLimiter::hit($throttleKey, 180);
        $this->addError('email', 'E-mail ou senha incorretos.');
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest');
    }
}
