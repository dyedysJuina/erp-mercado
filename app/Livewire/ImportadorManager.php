<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Cest;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ClientesEndereco;
use App\Models\Cfop;
use App\Models\Embalagem;
use App\Models\Estado;
use App\Models\EstoqueSaldo;
use App\Models\Fornecedor;
use App\Models\Marca;
use App\Models\Ncm;
use App\Models\ProdutoBase;
use App\Models\ProdutoVariacao;
use App\Models\UnidadeMedida;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;

class ImportadorManager extends Component
{
    use WithFileUploads;

    public string $tipo = 'produtos'; // produtos | clientes | fornecedores
    public $arquivo = null;
    public string $passo = 'upload'; // upload | mapping | processando | resultado

    public array $cabecalhos = [];
    public array $preview = [];
    public array $colunas = []; // DB field => CSV column index
    public array $colunasDisponiveis = [];
    public array $mapeamentoAuto = [];

    public int $totalLinhas = 0;
    public int $processados = 0;
    public int $sucesso = 0;
    public int $erros = 0;
    public array $logErros = [];

    public string $delimitador = 'auto';
    public bool $temCabecalho = true;
    public int $lote = 1;

    public string $toastMsg = '';
    public bool $toastShow = false;

    private array $cacheCategoria = [];

    protected function rules(): array
    {
        return ['arquivo' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240']];
    }

    public function camposTipo(): array
    {
        if ($this->tipo === 'produtos') {
            return [
                'nome_completo' => 'Nome do Produto *',
                'sku' => 'SKU / Codigo Interno',
                'codigo_barras' => 'Codigo de Barras (EAN)',
                'marca' => 'Marca',
                'categoria' => 'Caminho da Categoria (N1>N2>N3)',
                'unidade_medida' => 'Unidade Medida (UN, KG, L...) *',
                'embalagem' => 'Embalagem',
                'conteudo_quantidade' => 'Conteudo (quantidade)',
                'preco_custo' => 'Preco de Custo',
                'preco_venda' => 'Preco de Venda',
                'margem' => 'Margem (%)',
                'ncm' => 'NCM (8 digitos)',
                'cest' => 'CEST',
                'cfop' => 'CFOP',
                'origem' => 'Origem (0-Nacional, 1-Estrangeira...)',
                'estoque_inicial' => 'Estoque Inicial',
                'descricao' => 'Descricao',
                'ativo' => 'Ativo (sim/nao, 1/0)',
            ];
        }
        if ($this->tipo === 'clientes') {
            return [
                'nome' => 'Nome *',
                'cpf' => 'CPF',
                'email' => 'E-mail',
                'whatsapp' => 'WhatsApp / Telefone',
                'data_nascimento' => 'Data de Nascimento',
                'cep' => 'CEP',
                'logradouro' => 'Logradouro',
                'numero' => 'Numero',
                'bairro' => 'Bairro',
                'cidade' => 'Cidade',
                'uf' => 'UF (sigla)',
                'ativo' => 'Ativo (sim/nao, 1/0)',
            ];
        }
        // fornecedores
        return [
            'razao_social' => 'Razao Social *',
            'nome_fantasia' => 'Nome Fantasia',
            'cnpj' => 'CNPJ',
            'cpf' => 'CPF',
            'inscricao_estadual' => 'Inscricao Estadual',
            'telefone' => 'Telefone',
            'email' => 'E-mail',
            'cep' => 'CEP',
            'logradouro' => 'Logradouro',
            'numero' => 'Numero',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'uf' => 'UF (sigla)',
            'ativo' => 'Ativo (sim/nao, 1/0)',
        ];
    }

    public function updatedTipo(): void
    {
        $this->resetar();
    }

    public function updatedArquivo(): void
    {
        $this->validate();
        $this->lerCsv();
    }

    private function resetar(): void
    {
        $this->arquivo = null;
        $this->passo = 'upload';
        $this->cabecalhos = [];
        $this->preview = [];
        $this->colunas = [];
        $this->totalLinhas = 0;
        $this->processados = 0;
        $this->sucesso = 0;
        $this->erros = 0;
        $this->logErros = [];
        $this->lote = 1;
    }

    private function lerCsv(): void
    {
        $caminho = $this->arquivo->getRealPath();
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$linhas || count($linhas) < 2) {
            $this->addError('arquivo', 'Arquivo deve ter cabecalho + pelo menos 1 linha de dados.');
            return;
        }

        // Detect delimiter
        if ($this->delimitador === 'auto') {
            $primeira = $linhas[0];
            $virgula = substr_count($primeira, ',');
            $pontoVirgula = substr_count($primeira, ';');
            $this->delimitador = $pontoVirgula > $virgula ? ';' : ',';
        }

        $cabecalhoRaw = str_getcsv($linhas[0], $this->delimitador);
        $this->cabecalhos = array_map('trim', $cabecalhoRaw);
        $this->totalLinhas = count($linhas) - 1;

        // Read preview (up to 5 rows)
        $this->preview = [];
        for ($i = 1; $i < min(6, count($linhas)); $i++) {
            $row = str_getcsv($linhas[$i], $this->delimitador);
            $row = array_map('trim', $row);
            $this->preview[] = $row;
        }

        // Auto-mapping
        $this->colunas = [];
        $campos = $this->camposTipo();
        $sinonimos = $this->sinonimos();

        foreach ($this->cabecalhos as $idx => $header) {
            $headerNorm = mb_strtolower(trim(preg_replace('/[^a-z0-9]/i', '', $header)));
            $mapeado = false;
            foreach ($sinonimos as $campo => $nomes) {
                if (in_array($headerNorm, $nomes)) {
                    $this->colunas[$campo] = $idx;
                    $mapeado = true;
                    break;
                }
            }
            if (!$mapeado) {
                foreach ($campos as $campo => $label) {
                    if ($campo === str_replace(['-', ' '], '_', $headerNorm)) {
                        $this->colunas[$campo] = $idx;
                        break;
                    }
                }
            }
        }

        $this->passo = 'mapping';
    }

    private function sinonimos(): array
    {
        return [
            'nome_completo' => ['nome', 'produto', 'nomeproduto', 'descricao', 'produto_descricao'],
            'sku' => ['sku', 'codigo', 'codigointerno', 'ref', 'referencia', 'cod'],
            'codigo_barras' => ['codigobarras', 'ean', 'barras', 'gtin', 'codigodebarras', 'ean13'],
            'marca' => ['marca', 'brand', 'marca_nome'],
            'categoria' => ['categoria', 'caminho', 'departamento', 'cat', 'categoria_caminho'],
            'unidade_medida' => ['unidade', 'und', 'unidademedida', 'sigla', 'medida'],
            'embalagem' => ['embalagem', 'pack', 'tipoembalagem'],
            'conteudo_quantidade' => ['conteudo', 'quantidade_conteudo', 'qtdconteudo'],
            'preco_custo' => ['precocusto', 'custo', 'custounitario', 'precodecusto'],
            'preco_venda' => ['precovenda', 'venda', 'preco', 'precodevenda', 'pv'],
            'margem' => ['margem', 'margempercentual', '%'],
            'ncm' => ['ncm', 'ncm_sh', 'ncmcodigo'],
            'cest' => ['cest', 'cestcodigo'],
            'cfop' => ['cfop', 'cfopcodigo'],
            'origem' => ['origem', 'origemmercadoria', 'origem_produto'],
            'estoque_inicial' => ['estoque', 'estoqueinicial', 'saldoinicial', 'quantidadeinicial'],
            'descricao' => ['descricao', 'obs', 'observacao'],
            'ativo' => ['ativo', 'status', 'situacao', 'ativado'],
            'nome' => ['nome', 'nomecliente', 'cliente', 'razaosocial'],
            'cpf' => ['cpf', 'cpfcnpj', 'documento', 'doc'],
            'email' => ['email', 'e_mail', 'mail'],
            'whatsapp' => ['whatsapp', 'telefone', 'celular', 'fone', 'tel', 'telefonecelular'],
            'data_nascimento' => ['datanascimento', 'nascimento', 'dtnascimento'],
            'cep' => ['cep', 'codigopostal'],
            'logradouro' => ['logradouro', 'endereco', 'rua', 'rua_endereco'],
            'numero' => ['numero', 'n', 'nr', 'num'],
            'bairro' => ['bairro', 'distrito'],
            'cidade' => ['cidade', 'municipio', 'city'],
            'uf' => ['uf', 'estado', 'state', 'uf_estado'],
            'razao_social' => ['razaosocial', 'razao', 'nome', 'fornecedor'],
            'nome_fantasia' => ['nomefantasia', 'fantasia'],
            'cnpj' => ['cnpj', 'cnpjcpf'],
            'inscricao_estadual' => ['inscricaoestadual', 'ie', 'inscricao'],
            'cidade_id' => ['cidade', 'municipio', 'city'],
        ];
    }

    public function iniciarImportacao(): void
    {
        $campos = $this->camposTipo();
        $obrigatorios = array_filter($campos, fn($v, $k) => str_contains($v, '*'), ARRAY_FILTER_USE_BOTH);

        foreach ($obrigatorios as $campo => $label) {
            if (!isset($this->colunas[$campo])) {
                $this->addError('colunas', "Campo obrigatorio '{$label}' nao mapeado.");
                return;
            }
        }

        $this->passo = 'processando';
        $this->processados = 0;
        $this->sucesso = 0;
        $this->erros = 0;
        $this->logErros = [];
        $this->lote = 1;

        $this->processarLote();
    }

    public function processarLote(): void
    {
        $caminho = $this->arquivo->getRealPath();
        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $inicio = 1 + ($this->lote - 1) * 20;
        $fim = min($inicio + 19, count($linhas));

        // Cachear todas as categorias existentes para evitar N+1
        if ($this->tipo === 'produtos') {
            $this->cacheCategoria = Categoria::pluck('id', 'nome')->toArray();
        }

        DB::beginTransaction();
        try {
            for ($i = $inicio; $i <= $fim; $i++) {
                $this->processados++;
                $row = str_getcsv($linhas[$i], $this->delimitador);
                $row = array_map('trim', $row);
                $linhaNum = $i + 1;

                try {
                    if ($this->tipo === 'produtos') {
                        $this->importarProduto($row, $linhaNum);
                    } elseif ($this->tipo === 'clientes') {
                        $this->importarCliente($row, $linhaNum);
                    } elseif ($this->tipo === 'fornecedores') {
                        $this->importarFornecedor($row, $linhaNum);
                    }
                    $this->sucesso++;
                } catch (\Exception $e) {
                    $this->erros++;
                    $this->logErros[] = "Linha {$linhaNum}: " . $e->getMessage();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logErros[] = "Erro no lote {$this->lote}: " . $e->getMessage();
            $this->erros += ($fim - $inicio + 1);
        }

        if ($fim >= count($linhas) - 1) {
            $this->passo = 'resultado';
        } else {
            $this->lote++;
        }
    }

    private function importarProduto(array $row, int $linhaNum): void
    {
        $get = fn($campo) => isset($this->colunas[$campo]) && isset($row[$this->colunas[$campo]])
            ? trim($row[$this->colunas[$campo]]) : null;

        $nome = $get('nome_completo');
        if (!$nome) throw new \Exception("Nome do produto vazio.");

        // Categoria — usa cache em memoria para evitar N+1
        $categoriaId = null;
        $caminho = $get('categoria');
        if ($caminho) {
            $parts = array_map('trim', explode('>', $caminho));
            $parentId = null;
            foreach ($parts as $p) {
                if (isset($this->cacheCategoria[$p])) {
                    $catId = $this->cacheCategoria[$p];
                } else {
                    $cat = Categoria::where('nome', $p)->where('parent_id', $parentId)->first();
                    if (!$cat) {
                        $nivel = $parentId === null ? 1 : (Categoria::find($parentId)?->nivel + 1);
                        $cat = Categoria::create([
                            'nome' => $p, 'parent_id' => $parentId,
                            'nivel' => $nivel ?? 1, 'ativo' => true,
                        ]);
                    }
                    $catId = $cat->id;
                    $this->cacheCategoria[$p] = $catId;
                }
                $parentId = $catId;
                $categoriaId = $catId;
            }
        }

        // Marca
        $marcaNome = $get('marca');
        $marcaId = null;
        if ($marcaNome) {
            $marca = Marca::firstOrCreate(['nome' => $marcaNome, 'ativo' => true]);
            $marcaId = $marca->id;
        }

        // Unidade
        $unidSigla = $get('unidade_medida');
        if (!$unidSigla) throw new \Exception("Unidade de medida vazia.");
        $unid = UnidadeMedida::where('sigla', $unidSigla)->orWhere('nome', $unidSigla)->first();
        if (!$unid) throw new \Exception("Unidade de medida '{$unidSigla}' nao encontrada.");

        // Embalagem
        $embNome = $get('embalagem');
        $embId = null;
        if ($embNome) {
            $emb = Embalagem::firstOrCreate(['nome' => $embNome, 'sigla' => $embNome, 'ativo' => true]);
            $embId = $emb->id;
        }

        // NCM
        $ncmId = null;
        $ncmCod = preg_replace('/\D/', '', $get('ncm') ?? '');
        if ($ncmCod) {
            $ncm = Ncm::where('codigo', $ncmCod)->first();
            $ncmId = $ncm?->id;
        }

        // CEST
        $cestId = null;
        $cestCod = preg_replace('/\D/', '', $get('cest') ?? '');
        if ($cestCod) {
            $cest = Cest::where('codigo', $cestCod)->first();
            $cestId = $cest?->id;
        }

        // CFOP
        $cfopId = null;
        $cfopCod = preg_replace('/\D/', '', $get('cfop') ?? '');
        if ($cfopCod) {
            $cfop = Cfop::where('codigo', $cfopCod)->first();
            $cfopId = $cfop?->id;
        }

        $sku = $get('sku');
        $slug = Str::slug($nome . ($sku ? '-' . $sku : '')) . '-' . time() . rand(100, 999);

        // Create ProdutoBase
        $base = ProdutoBase::create([
            'categoria_id' => $categoriaId,
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => $get('descricao'),
            'ativo' => $this->parseBool($get('ativo'), true),
            'ncm_id' => $ncmId,
            'cfop_id' => $cfopId,
            'cest_id' => $cestId,
            'origem_mercadoria' => $get('origem') ?: '0',
        ]);

        $nomeCompleto = $nome . ($marcaNome ? " {$marcaNome}" : '');
        $slugVar = Str::slug($nomeCompleto . '-' . $sku) . '-' . time() . rand(100, 999);

        $precoCusto = (float)str_replace([',', '.'], ['.', ''], $get('preco_custo') ?? '0');
        $precoVenda = (float)str_replace([',', '.'], ['.', ''], $get('preco_venda') ?? '0');
        $margem = (float)str_replace(',', '.', $get('margem') ?? '0');
        $conteudo = (float)str_replace(',', '.', $get('conteudo_quantidade') ?? '1');

        $variacao = ProdutoVariacao::create([
            'produto_base_id' => $base->id,
            'marca_id' => $marcaId,
            'unidade_medida_id' => $unid->id,
            'embalagem_id' => $embId,
            'nome_completo' => $nomeCompleto,
            'slug' => $slugVar,
            'sku' => $sku ?: null,
            'conteudo_quantidade' => $conteudo,
            'pesavel' => false,
            'fracionado' => false,
            'quantidade_minima_venda' => 1,
            'passo_venda' => 1,
            'ncm_id' => $ncmId,
            'cfop_id' => $cfopId,
            'cest_id' => $cestId,
            'origem_mercadoria' => $get('origem') ?: '0',
            'cst_icms' => '102',
            'ativo' => $this->parseBool($get('ativo'), true),
        ]);

        // Barcode
        $barcode = $get('codigo_barras');
        if ($barcode) {
            DB::table('produto_codigos_barras')->insert([
                'produto_variacao_id' => $variacao->id,
                'codigo' => $barcode,
                'tipo' => 'ean13',
                'principal' => true,
            ]);
        }

        // Price in all active lojas price tables
        if ($precoVenda > 0 || $precoCusto > 0) {
            $tabelas = DB::table('lojas')->where('ativo', true)
                ->whereNotNull('tabela_preco_id')->distinct()->pluck('tabela_preco_id');
            foreach ($tabelas as $tabelaId) {
                $data = [];
                if ($precoCusto > 0) $data['preco_custo'] = $precoCusto;
                if ($precoVenda > 0) $data['preco_venda'] = $precoVenda;
                if ($margem > 0) $data['margem_percentual'] = $margem;
                if (!empty($data)) {
                    DB::table('tabela_precos_itens')->updateOrInsert(
                        ['tabela_preco_id' => $tabelaId, 'produto_variacao_id' => $variacao->id],
                        $data
                    );
                }
            }
        }

        // Estoque inicial
        $estoque = (float)str_replace(',', '.', $get('estoque_inicial') ?? '0');
        if ($estoque > 0) {
            $lojasAtivas = DB::table('lojas')->where('ativo', true)->pluck('id');
            foreach ($lojasAtivas as $lojaId) {
                EstoqueSaldo::create([
                    'loja_id' => $lojaId,
                    'produto_variacao_id' => $variacao->id,
                    'quantidade_atual' => $estoque,
                    'estoque_minimo' => 0,
                ]);
            }
        }
    }

    private function importarCliente(array $row, int $linhaNum): void
    {
        $get = fn($campo) => isset($this->colunas[$campo]) && isset($row[$this->colunas[$campo]])
            ? trim($row[$this->colunas[$campo]]) : null;

        $nome = $get('nome');
        if (!$nome) throw new \Exception("Nome do cliente vazio.");

        $cpf = preg_replace('/\D/', '', $get('cpf') ?? '');

        $cliente = Cliente::create([
            'nome' => $nome,
            'email' => $get('email') ?: null,
            'cpf' => $cpf ?: null,
            'whatsapp' => $get('whatsapp') ?: null,
            'data_nascimento' => $get('data_nascimento') ?: null,
            'ativo' => $this->parseBool($get('ativo'), true),
        ]);

        // Endereco
        $logradouro = $get('logradouro');
        $cidadeNome = $get('cidade');
        if ($logradouro && $cidadeNome) {
            $uf = $get('uf');
            $cidade = null;
            if ($uf) {
                $estado = Estado::where('uf', strtoupper($uf))->first();
                if ($estado) {
                    $cidade = Cidade::where('nome', $cidadeNome)->where('estado_id', $estado->id)->first();
                }
            }
            if (!$cidade) {
                $cidade = Cidade::where('nome', $cidadeNome)->first();
            }

            ClientesEndereco::create([
                'cliente_id' => $cliente->id,
                'titulo' => 'Principal',
                'cep' => preg_replace('/\D/', '', $get('cep') ?? ''),
                'logradouro' => $logradouro,
                'numero' => $get('numero') ?: null,
                'bairro' => $get('bairro') ?: null,
                'cidade_id' => $cidade?->id,
                'principal' => true,
            ]);
        }
    }

    private function importarFornecedor(array $row, int $linhaNum): void
    {
        $get = fn($campo) => isset($this->colunas[$campo]) && isset($row[$this->colunas[$campo]])
            ? trim($row[$this->colunas[$campo]]) : null;

        $razao = $get('razao_social');
        if (!$razao) throw new \Exception("Razao social vazia.");

        $cnpj = preg_replace('/\D/', '', $get('cnpj') ?? '');
        $cpf = preg_replace('/\D/', '', $get('cpf') ?? '');

        Fornecedor::create([
            'tipo_pessoa' => $cnpj ? 'juridica' : 'fisica',
            'razao_social' => $razao,
            'nome_fantasia' => $get('nome_fantasia') ?: null,
            'cnpj_cpf' => $cnpj ?: $cpf,
            'inscricao_estadual' => $get('inscricao_estadual') ?: null,
            'telefone' => $get('telefone') ?: null,
            'email' => $get('email') ?: null,
            'cep' => preg_replace('/\D/', '', $get('cep') ?? ''),
            'logradouro' => $get('logradouro') ?: null,
            'numero' => $get('numero') ?: null,
            'bairro' => $get('bairro') ?: null,
            'cidade_id' => null,
            'ativo' => $this->parseBool($get('ativo'), true),
        ]);
    }

    private function parseBool(?string $value, bool $default): bool
    {
        if ($value === null) return $default;
        $value = mb_strtolower(trim($value));
        return in_array($value, ['1', 'sim', 'yes', 'true', 's', 'ativo', 'ativado', 'on']);
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.importador-manager')
            ->layout('components.layouts.app', ['title' => 'Importador · ERP Mercado']);
    }
}
