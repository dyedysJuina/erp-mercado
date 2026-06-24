<?php

namespace App\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class VitrineCadastro extends Component
{
    public string $nome = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatedWhatsapp(): void
    {
        $this->whatsapp = preg_replace('/\D/', '', $this->whatsapp ?? '');
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:clientes,email'],
            'whatsapp' => ['required', 'string', 'max:20', 'unique:clientes,whatsapp'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nome.required' => 'Informe seu nome.',
            'email.required' => 'Informe seu e-mail.',
            'email.unique' => 'Este e-mail ja esta cadastrado.',
            'whatsapp.required' => 'Informe seu WhatsApp.',
            'password.required' => 'Crie uma senha.',
            'password.min' => 'A senha deve ter no minimo 6 caracteres.',
            'password.confirmed' => 'As senhas nao conferem.',
        ];
    }

    public function registrar()
    {
        $this->validate();

        $cliente = Cliente::create([
            'nome' => $this->nome,
            'email' => $this->email,
            'whatsapp' => preg_replace('/\D/', '', $this->whatsapp),
            'password' => $this->password,
            'ativo' => true,
        ]);

        session([
            'cliente_id' => $cliente->id,
            'cliente_nome' => $cliente->nome,
        ]);

        $this->dispatch('cliente-logado');
        return $this->redirect('/vitrine');
    }

    public function render()
    {
        return view('livewire.vitrine-cadastro')
            ->layout('layouts.guest');
    }
}
