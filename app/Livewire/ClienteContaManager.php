<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\ClientesEndereco;
use App\Models\Pedido;
use App\Models\Estado;
use App\Models\Cidade;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClienteContaManager extends Component
{
    public string $aba = 'resumo';
    public ?int $pedidoDetalheId = null;
    public string $toastMsg = '';
    public bool $toastShow = false;

    // Dados pessoais
    public string $edit_nome = '';
    public string $edit_email = '';
    public string $edit_whatsapp = '';
    public string $edit_cpf = '';
    public string $edit_data_nascimento = '';

    // Senha
    public string $senha_atual = '';
    public string $senha_nova = '';
    public string $senha_confirmacao = '';

    // Endereco
    public ?int $enderecoEditandoId = null;
    public string $end_cep = '';
    public string $end_logradouro = '';
    public string $end_numero = '';
    public string $end_bairro = '';
    public string $end_complemento = '';
    public string $end_cidade = '';
    public string $end_uf = '';
    public string $end_tipo = 'casa';
    public bool $end_principal = false;

    // Preferencias
    public bool $pref_ofertas_whatsapp = false;
    public bool $pref_ofertas_email = false;
    public bool $pref_substituicao_auto = false;
    public string $pref_entrega = 'entrega';
    public string $pref_substituicao_regra = 'avisar';

    private function cliente(): ?Cliente
    {
        $id = session('cliente_id');
        if (!$id) return null;
        return Cliente::find($id);
    }

    public function mount(): void
    {
        $this->carregarDados();
    }

    public function carregarDados(): void
    {
        $c = $this->cliente();
        if (!$c) return;
        $this->edit_nome = $c->nome;
        $this->edit_email = $c->email ?? '';
        $this->edit_whatsapp = $c->whatsapp ?? '';
        $this->edit_cpf = $c->cpf ?? '';
        $this->edit_data_nascimento = $c->data_nascimento?->format('Y-m-d') ?? '';
    }

    #[Computed]
    public function clienteDados(): ?array
    {
        $c = $this->cliente();
        if (!$c) return null;
        return $c->toArray();
    }

    #[Computed]
    public function enderecos(): array
    {
        $c = $this->cliente();
        if (!$c) return [];
        return ClientesEndereco::where('cliente_id', $c->id)->orderByDesc('principal')->orderBy('id')->get()->toArray();
    }

    #[Computed]
    public function pedidosRecentes(): array
    {
        $c = $this->cliente();
        if (!$c) return [];
        return Pedido::where('cliente_id', $c->id)->where('origem', 'site')
            ->with('itens.variacao')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function verPedido(int $id): void
    {
        $this->pedidoDetalheId = $id;
        $this->aba = 'pedido-detalhe';
    }

    public function fecharPedido(): void
    {
        $this->pedidoDetalheId = null;
        $this->aba = 'pedidos';
    }

    #[Computed]
    public function pedidoDetalhe(): ?array
    {
        if (!$this->pedidoDetalheId) return null;
        $c = $this->cliente();
        if (!$c) return null;
        return Pedido::where('cliente_id', $c->id)->where('id', $this->pedidoDetalheId)
            ->with('itens.variacao')
            ->first()?->toArray();
    }

    public function salvarDados(): void
    {
        $c = $this->cliente();
        if (!$c) return;

        $this->validate([
            'edit_nome' => ['required', 'string', 'min:2', 'max:150'],
            'edit_whatsapp' => ['required', 'string', 'max:20'],
            'edit_email' => ['nullable', 'email', 'max:150'],
            'edit_cpf' => ['nullable', 'string', 'max:14'],
        ]);

        $c->update([
            'nome' => $this->edit_nome,
            'whatsapp' => preg_replace('/\D/', '', $this->edit_whatsapp),
            'email' => $this->edit_email ?: null,
            'cpf' => $this->edit_cpf ?: null,
            'data_nascimento' => $this->edit_data_nascimento ?: null,
        ]);

        session(['cliente_nome' => $c->nome]);
        $this->toast('Dados atualizados!');
    }

    public function alterarSenha(): void
    {
        $c = $this->cliente();
        if (!$c) return;

        $this->validate([
            'senha_atual' => ['required'],
            'senha_nova' => ['required', 'string', 'min:6'],
            'senha_confirmacao' => ['required', 'same:senha_nova'],
        ]);

        if (!Hash::check($this->senha_atual, $c->user?->password ?? '')) {
            $this->addError('senha_atual', 'Senha atual incorreta.');
            return;
        }

        $c->user?->update(['password' => Hash::make($this->senha_nova)]);
        $this->senha_atual = '';
        $this->senha_nova = '';
        $this->senha_confirmacao = '';
        $this->toast('Senha alterada!');
    }

    // ─── Enderecos ───

    public function novoEndereco(): void
    {
        $this->enderecoEditandoId = null;
        $this->end_cep = '';
        $this->end_logradouro = '';
        $this->end_numero = '';
        $this->end_bairro = '';
        $this->end_complemento = '';
        $this->end_cidade = '';
        $this->end_uf = '';
        $this->end_tipo = 'casa';
        $this->end_principal = false;
    }

    public function editarEndereco(int $id): void
    {
        $e = ClientesEndereco::findOrFail($id);
        $this->enderecoEditandoId = $e->id;
        $this->end_cep = $e->cep ?? '';
        $this->end_logradouro = $e->logradouro ?? '';
        $this->end_numero = $e->numero ?? '';
        $this->end_bairro = $e->bairro ?? '';
        $this->end_complemento = $e->complemento ?? '';
        $this->end_cidade = (string)$e->cidade_id;
        $this->end_uf = (string)$e->cidade?->estado_id ?? '';
        $this->end_tipo = $e->titulo ?? 'casa';
        $this->end_principal = $e->principal;
    }

    public function salvarEndereco(): void
    {
        $c = $this->cliente();
        if (!$c) return;

        $this->validate([
            'end_logradouro' => ['required', 'string', 'max:255'],
            'end_numero' => ['nullable', 'string', 'max:20'],
            'end_bairro' => ['nullable', 'string', 'max:150'],
            'end_cidade' => ['nullable'],
            'end_cep' => ['nullable', 'string', 'max:10'],
        ]);

        $data = [
            'cliente_id' => $c->id,
            'titulo' => $this->end_tipo,
            'cep' => preg_replace('/\D/', '', $this->end_cep),
            'logradouro' => $this->end_logradouro,
            'numero' => $this->end_numero ?: null,
            'bairro' => $this->end_bairro ?: null,
            'complemento' => $this->end_complemento ?: null,
            'cidade_id' => $this->end_cidade ? (int)$this->end_cidade : null,
            'principal' => $this->end_principal,
        ];

        if ($this->enderecoEditandoId) {
            ClientesEndereco::where('id', $this->enderecoEditandoId)->where('cliente_id', $c->id)->update($data);
        } else {
            if ($this->end_principal) {
                ClientesEndereco::where('cliente_id', $c->id)->update(['principal' => false]);
            }
            ClientesEndereco::create($data);
        }

        $this->enderecoEditandoId = null;
        $this->toast('Endereco salvo!');
    }

    public function excluirEndereco(int $id): void
    {
        ClientesEndereco::where('id', $id)->where('cliente_id', session('cliente_id'))->delete();
        $this->toast('Endereco excluido.');
    }

    public function definirEnderecoPrincipal(int $id): void
    {
        $c = $this->cliente();
        if (!$c) return;
        ClientesEndereco::where('cliente_id', $c->id)->update(['principal' => false]);
        ClientesEndereco::where('id', $id)->where('cliente_id', $c->id)->update(['principal' => true]);
        $this->toast('Endereco principal atualizado.');
    }

    public function logout(): void
    {
        session()->forget(['cliente_id', 'cliente_nome']);
        $this->redirect('/vitrine');
    }

    public function excluirConta(): void
    {
        $c = $this->cliente();
        if (!$c) return;
        $c->update(['ativo' => false]);
        $this->logout();
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.cliente-conta-manager')
            ->layout('layouts.guest', ['title' => 'Minha Conta · ERP Mercado']);
    }
}
