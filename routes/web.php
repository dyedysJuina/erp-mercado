<?php

use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/setup', [\App\Http\Controllers\SetupController::class, 'index']);
Route::get('/setup/run', [\App\Http\Controllers\SetupController::class, 'run']);

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::get('/loja', \App\Livewire\StorefrontManager::class)->name('loja');
Route::get('/vitrine', \App\Livewire\StorefrontManager::class)->name('vitrine');

Route::get('/vitrine/auth', \App\Livewire\VitrineAuth::class)->name('vitrine.auth')->middleware('throttle:10,1');
Route::get('/vitrine/minha-conta', \App\Livewire\ClienteContaManager::class)->name('vitrine.minha-conta')->middleware('cliente.auth');
Route::get('/vitrine/pedidos', \App\Livewire\ClienteContaManager::class)->name('vitrine.pedidos')->middleware('cliente.auth');
Route::get('/vitrine/meus-pedidos', \App\Livewire\ClienteContaManager::class)->name('vitrine.meus-pedidos')->middleware('cliente.auth');
Route::get('/vitrine/ofertas', \App\Livewire\StorefrontManager::class)->name('vitrine.ofertas');
Route::get('/vitrine/favoritos', function () {
    return redirect('/vitrine');
})->name('vitrine.favoritos');
Route::get('/vitrine/atendimento', function () {
    return '<div style="font-family:sans-serif;text-align:center;padding:40px"><h2>Atendimento</h2><p>Em breve.</p><a href="/vitrine">Voltar</a></div>';
})->name('vitrine.atendimento');
Route::get('/vitrine/logout', function () {
    session()->forget(['cliente_id', 'cliente_nome']);
    return redirect('/vitrine');
})->name('vitrine.logout');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

Route::middleware(['auth', 'permission:dashboard'])->group(function () {
    Route::get('/', \App\Livewire\DashboardManager::class)->name('dashboard');
});

Route::middleware('auth')->group(function () {

    Route::get('/componentes', function () { return view('components.demo'); })->name('componentes');

    $r = function ($uri, $comp, $name, $perm = null) {
        Route::get($uri, $comp)->name($name)->middleware('permission:' . ($perm ?? $name));
    };

    $r('/categorias', \App\Livewire\CategoryManager::class, 'categorias');
    $r('/atributos', \App\Livewire\AtributoManager::class, 'atributos');
    $r('/unidades-medida', \App\Livewire\UnidadeMedidaManager::class, 'unidades-medida', 'unidades');
    $r('/embalagens', \App\Livewire\EmbalagemManager::class, 'embalagens');
    $r('/marcas', \App\Livewire\MarcaManager::class, 'marcas');
    $r('/produtos-base', \App\Livewire\ProdutoBaseManager::class, 'produtos-base');
    $r('/variacoes', \App\Livewire\VariacaoManager::class, 'variacoes');
    $r('/clientes', \App\Livewire\ClienteManager::class, 'clientes');
    Route::get('/clientes/{id}', \App\Livewire\ClienteManager::class)->name('clientes.detalhe')->middleware('auth');
    $r('/fornecedores', \App\Livewire\FornecedorManager::class, 'fornecedores');
    $r('/precos', \App\Livewire\PrecoManager::class, 'precos');
    $r('/precos/historico', \App\Livewire\PrecoHistoricoManager::class, 'precos.historico', 'precos');
    $r('/ofertas', \App\Livewire\OfertaManager::class, 'ofertas');
    $r('/curva-abc', \App\Livewire\CurvaAbcManager::class, 'curva-abc', 'relatorios');
    $r('/sugestao-compras', \App\Livewire\SugestaoCompraManager::class, 'sugestao-compras', 'compras');
    $r('/dre', \App\Livewire\DreManager::class, 'dre', 'relatorios');
    $r('/margem-departamento', \App\Livewire\MargemDepartamentoManager::class, 'margem-departamento', 'relatorios');
    $r('/fluxo-caixa', \App\Livewire\FluxoCaixaManager::class, 'fluxo-caixa', 'financeiro');
    $r('/dashboard-executivo', \App\Livewire\DashboardExecutivoManager::class, 'dashboard-executivo', 'admin.gerencial');
    $r('/notificacoes', \App\Livewire\NotificacaoManager::class, 'notificacoes', 'dashboard');
    $r('/etiquetas', \App\Livewire\EtiquetaManager::class, 'etiquetas', 'precos');
    $r('/nfe-entrada', \App\Livewire\NfeEntradaManager::class, 'nfe-entrada', 'compras');
    $r('/importador', \App\Livewire\ImportadorManager::class, 'importador', 'dashboard');
    $r('/lojas', \App\Livewire\LojaManager::class, 'lojas');
    $r('/estoque', \App\Livewire\EstoqueManager::class, 'estoque');
    $r('/compras', \App\Livewire\SugestaoCompraManager::class, 'compras');
    $r('/pedidos', \App\Livewire\PedidoListaManager::class, 'pedidos');
    $r('/pedidos-online', \App\Livewire\PedidoOnlineManager::class, 'pedidos-online', 'pedidos');
    Route::get('/pedidos-online/{id}', \App\Livewire\PedidoOnlineDetalheManager::class)->name('pedidos-online.detalhe')->middleware(['auth', 'permission:pedidos']);
    Route::get('/pedidos/{id}', \App\Livewire\PedidoDetalheManager::class)->name('pedidos.detalhe')->middleware(['auth', 'permission:pedidos']);
    $r('/vendas', \App\Livewire\VendaManager::class, 'vendas', 'pdv');
    $r('/financeiro', \App\Livewire\FinanceiroManager::class, 'financeiro');
    $r('/financeiro/conciliacao', \App\Livewire\ConciliacaoManager::class, 'financeiro.conciliacao');
    $r('/lotes', \App\Livewire\LoteManager::class, 'lotes');
    $r('/relatorios', \App\Livewire\RelatorioManager::class, 'relatorios');
    $r('/admin/temas', \App\Livewire\Admin\ThemeEditor::class, 'admin.themes', 'admin.temas');
    $r('/admin/fiscal', \App\Livewire\Admin\FiscalConfigManager::class, 'admin.fiscal');
    $r('/admin/gerencial', \App\Livewire\Admin\GerencialManager::class, 'admin.gerencial');
    $r('/usuarios', \App\Livewire\UserManager::class, 'usuarios');
    $r('/usuarios/papeis', \App\Livewire\RoleManager::class, 'papeis');

    Route::get('/admin/testar-regras', function () {
        $regras = \App\Models\RegraFiscalNcm::where('ativo', true)->orderBy('ncm_prefix')->get();
        return view('fiscal.testar-regras', ['regras' => $regras]);
    })->name('admin.testar-regras')->middleware('permission:admin.temas');

    Route::get('/admin/testar-regras-api', function () {
        $ncm = request('ncm');
        if (!$ncm || strlen($ncm) < 4) return response()->json(['csosn' => '102', 'regra' => 'NCM muito curto, padrão 102', 'origem' => 'fallback']);
        $regra = \App\Models\RegraFiscalNcm::sugerirCsosn($ncm);
        $descricao = \App\Models\RegraFiscalNcm::where('ncm_prefix', substr($ncm, 0, 4))->where('ativo', true)->value('descricao')
            ?? \App\Models\RegraFiscalNcm::where('ncm_prefix', substr($ncm, 0, 2))->where('ativo', true)->value('descricao')
            ?? 'Regra padrão (tributado)';
        return response()->json(['csosn' => $regra, 'regra' => $descricao, 'origem' => $regra === '102' ? 'Padrão (tributado)' : 'Regra específica']);
    })->name('admin.testar-regras-api')->middleware('permission:admin.temas');

    Route::get('/fiscal/danfe/{id}', function (int $id) {
        $doc = \App\Models\FiscalDocumento::with(['itens.variacao.unidadeMedida','itens.variacao.codigosBarras','loja.cidade.estado','cliente'])->findOrFail($id);
        return view('fiscal.danfe-nfce', ['doc' => $doc, 'loja' => $doc->loja]);
    })->name('fiscal.danfe')->middleware('permission:pdv');

    Route::get('/fiscal/xml/{id}', function (int $id) {
        $doc = \App\Models\FiscalDocumento::findOrFail($id);
        if (!$doc->xml_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($doc->xml_path)) abort(404, 'XML não encontrado');
        return response()->streamDownload(function () use ($doc) { echo \Illuminate\Support\Facades\Storage::disk('public')->get($doc->xml_path); }, 'NFCe-' . $doc->chave_acesso . '.xml', ['Content-Type' => 'application/xml']);
    })->name('fiscal.xml')->middleware('permission:pdv');

    Route::get('/pedidos/{id}/pdf', function (int $id) {
        $pedido = \App\Models\CompraPedido::with(['fornecedor', 'loja', 'itens.variacao.unidadeMedida'])->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fiscal.pedido-pdf', ['pedido' => $pedido]);
        $pdf->setPaper('A4');
        return $pdf->download('pedido-' . $id . '.pdf');
    })->name('pedidos.pdf')->middleware('permission:compras');

    Route::get('/etiquetas/imprimir', function () {
        $ids = request('ids');
        $lojaId = (int) request('loja');
        $tipo = request('tipo', 'venda');
        $qtd = max(1, min(10, (int) request('qtd', 1)));
        if (!$ids || !$lojaId) abort(400, 'Parametros invalidos');
        $ids = array_map('intval', explode(',', $ids));
        $campo = $tipo === 'atacado' ? 'preco_atacado' : 'preco_venda';

        $produtos = \App\Models\ProdutoVariacao::whereIn('id', $ids)->where('ativo', true)
            ->with('marca', 'unidadeMedida', 'codigosBarras')->get();

        $precos = \Illuminate\Support\Facades\DB::table('tabela_precos_itens')
            ->join('lojas', 'lojas.tabela_preco_id', '=', 'tabela_precos_itens.tabela_preco_id')
            ->where('lojas.id', $lojaId)
            ->whereIn('tabela_precos_itens.produto_variacao_id', $ids)
            ->select('produto_variacao_id', $campo)
            ->get()
            ->keyBy('produto_variacao_id');

        $loja = \App\Models\Loja::find($lojaId);

        return view('fiscal.etiquetas-print', compact('produtos', 'precos', 'campo', 'qtd', 'loja'));
    })->name('etiquetas.imprimir')->middleware('permission:precos');
});
