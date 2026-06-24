<?php

namespace App\Livewire\Admin;

use App\Models\FiscalPerfil;
use Livewire\Component;
use Livewire\WithFileUploads;

class FiscalConfigManager extends Component
{
    use WithFileUploads;

    public ?int $perfilId = null;
    public string $nome = '';
    public string $descricao = '';
    public string $ambiente = '2';
    public string $serie_nfce = '1';
    public string $numero_nfce_atual = '1';
    public string $regime_tributario = '1';
    public $certificado = null;
    public string $certificado_senha = '';
    public bool $certificado_carregado = false;
    public string $cfop_saida_padrao = '';
    public string $cfop_entrada_padrao = '';

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'ambiente' => ['required', 'in:1,2'],
            'serie_nfce' => ['required', 'string', 'max:3'],
            'regime_tributario' => ['required', 'in:1,2,3'],
            'certificado' => ['nullable', 'file', 'mimes:pfx,p12', 'max:5120'],
            'certificado_senha' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function mount(): void
    {
        $perfil = FiscalPerfil::where('ativo', true)->first();
        if ($perfil) {
            $this->perfilId = $perfil->id;
            $this->nome = $perfil->nome;
            $this->descricao = $perfil->descricao ?? '';
            $this->ambiente = $perfil->ambiente ?? '2';
            $this->serie_nfce = $perfil->serie_nfce ?? '1';
            $this->numero_nfce_atual = $perfil->numero_nfce_atual ?? '1';
            $this->regime_tributario = $perfil->regime_tributario ?? '1';
            $this->cfop_saida_padrao = $perfil->cfop_saida_padrao ?? '';
            $this->cfop_entrada_padrao = $perfil->cfop_entrada_padrao ?? '';
            $this->certificado_carregado = !empty($perfil->certificado_path);
        }
    }

    public function salvar(): void
    {
        $this->validate();

        $data = [
            'nome' => $this->nome,
            'descricao' => $this->descricao ?: null,
            'ambiente' => $this->ambiente,
            'serie_nfce' => $this->serie_nfce,
            'numero_nfce_atual' => $this->numero_nfce_atual,
            'regime_tributario' => $this->regime_tributario,
            'cfop_saida_padrao' => $this->cfop_saida_padrao ?: null,
            'cfop_entrada_padrao' => $this->cfop_entrada_padrao ?: null,
            'ativo' => true,
        ];

        if ($this->certificado) {
            $path = $this->certificado->store('certificados', 'local');
            $data['certificado_path'] = $path;
            $data['certificado_senha'] = $this->certificado_senha ?: null;
        } elseif ($this->certificado_senha && $this->perfilId) {
            $data['certificado_senha'] = $this->certificado_senha;
        }

        if ($this->perfilId) {
            $perfil = FiscalPerfil::findOrFail($this->perfilId);
            $perfil->update($data);
        } else {
            $perfil = FiscalPerfil::create($data);
            $this->perfilId = $perfil->id;
        }

        $this->certificado_carregado = !empty($perfil->certificado_path ?? $data['certificado_path'] ?? null);
        session()->flash('fiscal_ok', 'Configuração fiscal salva com sucesso.');
    }

    public function removerCertificado(): void
    {
        if ($this->perfilId) {
            $perfil = FiscalPerfil::find($this->perfilId);
            if ($perfil && $perfil->certificado_path) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($perfil->certificado_path);
                $perfil->update(['certificado_path' => null, 'certificado_senha' => null]);
            }
        }
        $this->certificado = null;
        $this->certificado_senha = '';
        $this->certificado_carregado = false;
    }

    public function render()
    {
        return view('livewire.admin.fiscal-config-manager')
            ->layout('components.layouts.app', ['title' => 'Configuração Fiscal · ERP Mercado']);
    }
}
