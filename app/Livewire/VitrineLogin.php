<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class VitrineLogin extends Component
{
    public string $email = '';
    public string $password = '';

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
            'email.email' => 'E-mail invalido.',
            'password.required' => 'Informe sua senha.',
        ];
    }

    public function entrar()
    {
        $this->validate();

        $cliente = Cliente::where('email', $this->email)->first();

        if (!$cliente || !$cliente->ativo || !Hash::check($this->password, $cliente->password ?? '')) {
            $this->addError('email', 'E-mail ou senha incorretos.');
            return;
        }

        $cliente->update(['ultimo_login_at' => now()]);

        session([
            'cliente_id' => $cliente->id,
            'cliente_nome' => $cliente->nome,
        ]);

        return $this->redirect('/vitrine');
    }

    public function render()
    {
        return view('livewire.vitrine-login')
            ->layout('layouts.guest');
    }
}
