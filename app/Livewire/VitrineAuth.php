<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class VitrineAuth extends Component
{
    public string $aba = 'login';
    public string $message = '';

    public function mount(): void
    {
        $this->aba = request()->query('aba', 'login');
    }

    // Login fields
    public string $login_email = '';
    public string $login_password = '';

    // Register fields
    public string $reg_nome = '';
    public string $reg_email = '';
    public string $reg_whatsapp = '';
    public string $reg_password = '';
    public string $reg_password_confirmation = '';

    protected function rules(): array
    {
        if ($this->aba === 'login') {
            return [
                'login_email' => ['required', 'email'],
                'login_password' => ['required'],
            ];
        }
        return [
            'reg_nome' => ['required', 'string', 'min:2', 'max:150'],
            'reg_email' => ['required', 'email', 'max:150', 'unique:clientes,email'],
            'reg_whatsapp' => ['required', 'string', 'max:20', 'unique:clientes,whatsapp'],
            'reg_password' => ['required', 'string', 'min:6'],
            'reg_password_confirmation' => ['required', 'same:reg_password'],
        ];
    }

    protected function messages(): array
    {
        return [
            'login_email.required' => 'Informe seu e-mail.',
            'login_email.email' => 'E-mail inválido.',
            'login_password.required' => 'Informe sua senha.',
            'reg_nome.required' => 'Informe seu nome.',
            'reg_email.required' => 'Informe seu e-mail.',
            'reg_email.unique' => 'Este e-mail já está cadastrado.',
            'reg_whatsapp.required' => 'Informe seu WhatsApp.',
            'reg_whatsapp.unique' => 'Este WhatsApp já está cadastrado.',
            'reg_password.required' => 'Crie uma senha.',
            'reg_password.min' => 'Mínimo 6 caracteres.',
            'reg_password_confirmation.required' => 'Confirme sua senha.',
            'reg_password_confirmation.same' => 'As senhas não conferem.',
        ];
    }

    public function updatedRegWhatsapp(): void
    {
        $this->reg_whatsapp = preg_replace('/\D/', '', $this->reg_whatsapp ?? '');
    }

    public function entrar()
    {
        $this->aba = 'login';
        $this->validate();

        $cliente = Cliente::where('email', $this->login_email)->first();

        if (!$cliente || !$cliente->ativo || !Hash::check($this->login_password, $cliente->password ?? '')) {
            $this->addError('login_email', 'E-mail ou senha incorretos.');
            return;
        }

        $cliente->update(['ultimo_login_at' => now()]);

        session([
            'cliente_id' => $cliente->id,
            'cliente_nome' => $cliente->nome,
        ]);

        return $this->redirect('/vitrine', navigate: true);
    }

    public function registrar()
    {
        $this->aba = 'register';
        $this->validate();

        $cliente = Cliente::create([
            'nome' => $this->reg_nome,
            'email' => $this->reg_email,
            'whatsapp' => $this->reg_whatsapp,
            'password' => $this->reg_password,
            'ativo' => true,
        ]);

        session([
            'cliente_id' => $cliente->id,
            'cliente_nome' => $cliente->nome,
        ]);

        return $this->redirect('/vitrine');
    }

    public function render()
    {
        return view('livewire.vitrine-auth')
            ->layout('layouts.guest');
    }
}
