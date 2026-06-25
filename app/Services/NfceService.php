<?php

namespace App\Services;

use App\Models\FiscalDocumento;
use App\Models\FiscalDocumentoItem;
use App\Models\FiscalPerfil;
use App\Models\Loja;
use App\Models\PdvVenda;
use App\Models\Cidade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NfceService
{
    public function emitir(PdvVenda $venda, Loja $loja): FiscalDocumento
    {
        $perfil = FiscalPerfil::where('ativo', true)->first();

        return DB::transaction(function () use ($venda, $loja, $perfil) {
            $serie = $perfil?->serie_nfce ?? '1';
            $proximoNumero = $this->proximoNumero($serie);

            $chave = $this->gerarChave($loja, $serie, $proximoNumero, $perfil?->ambiente ?? '2');

            $doc = FiscalDocumento::create([
                'loja_id' => $loja->id,
                'tipo' => 'nfce',
                'modelo' => '65',
                'serie' => $serie,
                'numero' => $proximoNumero,
                'chave_acesso' => $chave,
                'status' => 'rascunho',
                'valor_total' => $venda->total,
                'emitida_at' => now(),
                'cliente_id' => $venda->cliente_id,
                'pdv_venda_id' => $venda->id,
            ]);

            if ($perfil) {
                $perfil->increment('numero_nfce_atual');
            }

            $impostoService = app(CalculoImpostoService::class);

            $venda->loadMissing(['itens.variacao.unidadeMedida', 'itens.variacao.codigosBarras', 'itens.variacao.ncm', 'itens.variacao.cfop']);

            foreach ($venda->itens as $item) {
                $variacao = $item->variacao;
                if (!$variacao) continue;

                $ncmCodigo = $variacao->ncm?->codigo ?? '00000000';
                $cfopCodigo = $variacao->cfop?->codigo ?? '5102';
                $unidSigla = optional($variacao->unidadeMedida)->sigla ?? 'UN';
                $codBarras = $variacao->codigosBarras?->firstWhere('principal', true)?->codigo ?? 'SEM GTIN';

                $valorUnit = $item->preco_unitario;
                $quant = $item->quantidade;
                $total = $item->valor_total;

                $tributos = $impostoService->calcular($variacao, $total);

                FiscalDocumentoItem::create([
                    'fiscal_documento_id' => $doc->id,
                    'produto_variacao_id' => $variacao->id,
                    'quantidade' => $quant,
                    'valor_unitario' => $valorUnit,
                    'valor_total' => $total,
                    'ncm' => $ncmCodigo,
                    'cfop' => $cfopCodigo,
                    'cst_icms_csosn' => $tributos['icms']['cst'],
                    'aliquota_icms' => $tributos['icms']['aliquota'],
                    'valor_icms' => $tributos['icms']['valor'],
                    'cst_pis' => $tributos['pis']['cst'],
                    'aliquota_pis' => $tributos['pis']['aliquota'],
                    'valor_pis' => $tributos['pis']['valor'],
                    'cst_cofins' => $tributos['cofins']['cst'],
                    'aliquota_cofins' => $tributos['cofins']['aliquota'],
                    'valor_cofins' => $tributos['cofins']['valor'],
                ]);
            }

            $doc->loadMissing(['itens.variacao.unidadeMedida', 'itens.variacao.codigosBarras']);

            $xml = $this->gerarXml($doc, $loja, $perfil, $venda);
            $path = 'fiscal/nfce/' . $chave . '.xml';
            $salvou = Storage::disk('public')->put($path, $xml);
            if (!$salvou) {
                throw new \RuntimeException('Falha ao salvar o arquivo XML da NFC-e.');
            }
            $doc->update(['xml_path' => $path, 'status' => 'rascunho']);

            return $doc->fresh(['itens.variacao.unidadeMedida', 'itens.variacao.codigosBarras']);
        });
    }

    private function proximoNumero(string $serie): string
    {
        $ultimo = FiscalDocumento::where('serie', $serie)
            ->where('tipo', 'nfce')
            ->orderBy('numero', 'desc')
            ->lockForUpdate()
            ->value('numero');
        return str_pad((int)$ultimo + 1, 9, '0', STR_PAD_LEFT);
    }

    private function gerarChave(Loja $loja, string $serie, string $numero, string $tpAmb = '2'): string
    {
        $cnpj = preg_replace('/\D/', '', $loja->cnpj ?? '');
        if (strlen($cnpj) !== 14) $cnpj = '00000000000000';

        $uf = $loja->cidade?->estado?->uf ?? $loja->uf ?? 'MT';
        $ufCodigo = $this->ufParaCodigo($uf);

        $ano = now()->format('y');
        $mes = now()->format('m');
        $tpEmis = '1';
        $mod = '65';
        $seriePadded = str_pad($serie, 3, '0', STR_PAD_LEFT);
        $numPadded = str_pad($numero, 9, '0', STR_PAD_LEFT);
        $base = "{$ufCodigo}{$ano}{$mes}{$cnpj}{$mod}{$seriePadded}{$numPadded}{$tpEmis}{$tpAmb}";
        $dv = $this->calcularDvChave($base);
        return $base . $dv;
    }

    private function calcularDvChave(string $base): string
    {
        $multiplicadores = [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        foreach (array_reverse(str_split($base)) as $i => $digito) {
            $soma += (int)$digito * ($multiplicadores[$i] ?? 2);
        }
        $resto = $soma % 11;
        if ($resto < 2) return '0';
        return (string)(11 - $resto);
    }

    private function gerarXml(FiscalDocumento $doc, Loja $loja, ?FiscalPerfil $perfil, ?PdvVenda $venda = null): string
    {
        $cnpj = preg_replace('/\D/', '', $loja->cnpj ?? '');
        $ie = preg_replace('/\D/', '', $loja->inscricao_estadual ?? '');
        $razao = $loja->nome_fantasia ?: $loja->nome;
        $fantasia = $loja->nome_fantasia ?: $razao;
        $logradouro = $loja->logradouro ?? '';
        $numero = $loja->numero ?? 'S/N';
        $bairro = $loja->bairro ?? '';
        $cidadeNome = $loja->cidade?->nome ?? $loja->cidade ?? 'Juina';
        $uf = $loja->cidade?->estado?->uf ?? $loja->uf ?? 'MT';
        $ufCodigo = $this->ufParaCodigo($uf);
        $cep = preg_replace('/\D/', '', $loja->cep ?? '');
        $telefone = preg_replace('/\D/', '', $loja->telefone ?? '');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><NFCe xmlns="http://www.portalfiscal.inf.br/nfe"></NFCe>');
        $infNFe = $xml->addChild('infNFe');
        $infNFe->addAttribute('versao', '4.00');
        $infNFe->addAttribute('Id', 'NFe' . $doc->chave_acesso);

        $ide = $infNFe->addChild('ide');
        $ide->addChild('cUF', $ufCodigo);
        $ide->addChild('cNF', substr($doc->chave_acesso, 35, 8));
        $ide->addChild('natOp', 'VENDA');
        $ide->addChild('mod', '65');
        $ide->addChild('serie', $doc->serie);
        $ide->addChild('nNF', $doc->numero);
        $ide->addChild('dhEmi', now()->setTimezone(config('app.timezone', 'America/Cuiaba'))->format('Y-m-d\TH:i:sP'));
        $ide->addChild('tpNF', '1');
        $ide->addChild('idDest', '1');
        $ide->addChild('cMunFG', $this->codigoMunicipio($cidadeNome, $uf));
        $ide->addChild('tpImp', '4');
        $ide->addChild('tpEmis', $perfil?->tp_emis ?? '1');
        $ide->addChild('cDV', substr($doc->chave_acesso, -1));
        $ide->addChild('tpAmb', $perfil?->ambiente ?? '2');
        $ide->addChild('finNFe', '1');
        $ide->addChild('indFinal', '1');
        $ide->addChild('indPres', '1');
        $ide->addChild('indIntermed', '0');
        $ide->addChild('procEmi', '0');
        $ide->addChild('verProc', config('app.nfce_versao', 'ERP Mercado 1.0'));

        $emit = $infNFe->addChild('emit');
        $emit->addChild('CNPJ', $cnpj);
        $emit->addChild('xNome', $razao);
        $emit->addChild('xFant', $fantasia);
        $enderEmit = $emit->addChild('enderEmit');
        $enderEmit->addChild('xLgr', $logradouro);
        $enderEmit->addChild('nro', $numero);
        $enderEmit->addChild('xBairro', $bairro);
        $enderEmit->addChild('cMun', $this->codigoMunicipio($cidadeNome, $uf));
        $enderEmit->addChild('xMun', $cidadeNome);
        $enderEmit->addChild('UF', $uf);
        $enderEmit->addChild('CEP', $cep);
        if ($telefone) $enderEmit->addChild('fone', $telefone);
        $emit->addChild('IE', $ie);
        $emit->addChild('CRT', $perfil?->regime_tributario ?? '1');

        $dest = $infNFe->addChild('dest');
        $cliente = $venda?->cliente;
        if ($cliente && ($cliente->cpf || $cliente->cnpj)) {
            $docCpf = preg_replace('/\D/', '', $cliente->cpf ?? $cliente->cnpj ?? '');
            $tagDoc = strlen($docCpf) === 14 ? 'CNPJ' : 'CPF';
            $dest->addChild($tagDoc, $docCpf);
            $dest->addChild('xNome', mb_substr($cliente->nome, 0, 60));
            $endCli = $cliente->enderecos?->first();
            if ($endCli) {
                $enderDest = $dest->addChild('enderDest');
                $enderDest->addChild('xLgr', $endCli->logradouro ?? 'NAO INFORMADO');
                $enderDest->addChild('nro', $endCli->numero ?? 'S/N');
                $enderDest->addChild('xBairro', $endCli->bairro ?? 'NAO INFORMADO');
                $cidadeCli = $endCli->cidade;
                $enderDest->addChild('cMun', $cidadeCli && $cidadeCli->codigo_ibge ? $cidadeCli->codigo_ibge : $this->codigoMunicipio($cidadeNome, $uf));
                $enderDest->addChild('xMun', $cidadeCli->nome ?? $cidadeNome);
                $enderDest->addChild('UF', $cidadeCli?->estado?->uf ?? $uf);
            }
            $dest->addChild('indIEDest', '9');
        } else {
            $dest->addChild('CPF', '00000000000');
            $dest->addChild('xNome', 'CONSUMIDOR NAO IDENTIFICADO');
            $enderDest = $dest->addChild('enderDest');
            $enderDest->addChild('xLgr', 'NAO INFORMADO');
            $enderDest->addChild('nro', 'S/N');
            $enderDest->addChild('xBairro', 'NAO INFORMADO');
            $enderDest->addChild('cMun', $this->codigoMunicipio($cidadeNome, $uf));
            $enderDest->addChild('xMun', $cidadeNome);
            $enderDest->addChild('UF', $uf);
            $dest->addChild('indIEDest', '2');
        }

        $vProdTotal = 0;
        $vDescTotal = (float)($venda?->desconto ?? 0);
        $vAcrTotal = (float)($venda?->acrescimo ?? 0);
        $vNFTotal = (float)($venda?->total ?? $doc->valor_total);

        foreach ($doc->itens as $i => $item) {
            $nItem = $i + 1;
            $vProdTotal += $item->valor_total;

            $det = $infNFe->addChild('det');
            $det->addAttribute('nItem', (string)$nItem);
            $prod = $det->addChild('prod');
            $prod->addChild('cProd', $item->variacao?->sku ?? (string)$item->produto_variacao_id);
            $gtin = $item->variacao?->codigosBarras?->firstWhere('principal', true)?->codigo;
            $prod->addChild('cEAN', $gtin ?: 'SEM GTIN');
            $prod->addChild('xProd', $item->variacao?->nome_completo ?? 'Produto');
            $prod->addChild('NCM', $item->ncm ?: '00000000');
            $prod->addChild('CFOP', $item->cfop ?: '5102');
            $prod->addChild('uCom', $item->variacao?->unidadeMedida?->sigla ?? 'UN');
            $prod->addChild('qCom', number_format($item->quantidade, 4, '.', ''));
            $prod->addChild('vUnCom', number_format($item->valor_unitario, 10, '.', ''));
            $prod->addChild('vProd', number_format($item->valor_total, 2, '.', ''));
            $prod->addChild('cEANTrib', $gtin ?: 'SEM GTIN');
            $prod->addChild('uTrib', $item->variacao?->unidadeMedida?->sigla ?? 'UN');
            $prod->addChild('qTrib', number_format($item->quantidade, 4, '.', ''));
            $prod->addChild('vUnTrib', number_format($item->valor_unitario, 10, '.', ''));
            $prod->addChild('indTot', '1');

            $imposto = $det->addChild('imposto');
            $vTotTrib = (float)$item->valor_icms + (float)$item->valor_pis + (float)$item->valor_cofins;
            $imposto->addChild('vTotTrib', number_format($vTotTrib, 2, '.', ''));

            $csosn = $item->cst_icms_csosn ?? '102';
            $origem = '0';
            $icmsNode = $imposto->addChild('ICMS');

            if (in_array($csosn, ['101', '102', '103', '300', '400'])) {
                $tag = 'ICMSSN102';
                $sn = $icmsNode->addChild($tag);
                $sn->addChild('orig', $origem);
                $sn->addChild('CSOSN', $csosn);
                if ($csosn === '102') {
                    $sn->addChild('modBC', '3');
                    $sn->addChild('vBC', '0.00');
                    $sn->addChild('pICMS', '0.00');
                    $sn->addChild('vICMS', '0.00');
                }
            } elseif (in_array($csosn, ['201', '202', '203'])) {
                $tag = 'ICMSSN201';
                $sn = $icmsNode->addChild($tag);
                $sn->addChild('orig', $origem);
                $sn->addChild('CSOSN', $csosn);
                $sn->addChild('modBC', '3');
                $sn->addChild('vBC', '0.00');
                $sn->addChild('pICMS', '0.00');
                $sn->addChild('vICMS', '0.00');
                $sn->addChild('modBCST', '4');
                $sn->addChild('pMVAST', '0.00');
                $sn->addChild('pRedBCST', '0.00');
                $sn->addChild('vBCST', number_format($item->valor_total, 2, '.', ''));
                $sn->addChild('pICMSST', number_format($item->aliquota_icms ?: 0, 2, '.', ''));
                $sn->addChild('vICMSST', number_format($item->valor_icms ?: 0, 2, '.', ''));
            } elseif ($csosn === '500') {
                $sn = $icmsNode->addChild('ICMSSN500');
                $sn->addChild('orig', $origem);
                $sn->addChild('CSOSN', '500');
                $sn->addChild('vBCSTRet', number_format($item->valor_total, 2, '.', ''));
                $sn->addChild('pST', number_format($item->aliquota_icms ?: 0, 2, '.', ''));
                $sn->addChild('vICMSSTRet', number_format($item->valor_icms ?: 0, 2, '.', ''));
            } elseif ($csosn === '900') {
                $sn = $icmsNode->addChild('ICMSSN900');
                $sn->addChild('orig', $origem);
                $sn->addChild('CSOSN', '900');
                $sn->addChild('modBC', '3');
                $sn->addChild('vBC', '0.00');
                $sn->addChild('pICMS', '0.00');
                $sn->addChild('vICMS', '0.00');
                $sn->addChild('modBCST', '4');
                $sn->addChild('pMVAST', '0.00');
                $sn->addChild('vBCST', '0.00');
                $sn->addChild('pICMSST', '0.00');
                $sn->addChild('vICMSST', '0.00');
            }

            $pisNode = $imposto->addChild('PIS');
            $pisOutr = $pisNode->addChild('PISOutr');
            $pisOutr->addChild('CST', $item->cst_pis ?: '49');
            $pisOutr->addChild('vBC', '0.00');
            $pisOutr->addChild('pPIS', '0.00');
            $pisOutr->addChild('vPIS', '0.00');

            $cofinsNode = $imposto->addChild('COFINS');
            $cofinsOutr = $cofinsNode->addChild('COFINSOutr');
            $cofinsOutr->addChild('CST', $item->cst_cofins ?: '49');
            $cofinsOutr->addChild('vBC', '0.00');
            $cofinsOutr->addChild('pCOFINS', '0.00');
            $cofinsOutr->addChild('vCOFINS', '0.00');
        }

        $total = $infNFe->addChild('total');
        $icmsTot = $total->addChild('ICMSTot');
        $icmsTot->addChild('vBC', '0.00');
        $icmsTot->addChild('vICMS', '0.00');
        $icmsTot->addChild('vICMSDeson', '0.00');
        $icmsTot->addChild('vFCP', '0.00');
        $icmsTot->addChild('vBCST', '0.00');
        $icmsTot->addChild('vST', '0.00');
        $icmsTot->addChild('vProd', number_format($vProdTotal, 2, '.', ''));
        $icmsTot->addChild('vFrete', '0.00');
        $icmsTot->addChild('vSeg', '0.00');
        $icmsTot->addChild('vDesc', number_format($vDescTotal, 2, '.', ''));
        $icmsTot->addChild('vII', '0.00');
        $icmsTot->addChild('vIPI', '0.00');
        $icmsTot->addChild('vIOF', '0.00');
        $icmsTot->addChild('vOutro', number_format($vAcrTotal, 2, '.', ''));
        $icmsTot->addChild('vNF', number_format($vNFTotal, 2, '.', ''));

        $transp = $infNFe->addChild('transp');
        $transp->addChild('modFrete', '9');

        $pag = $infNFe->addChild('pag');
        $pagamentos = $venda?->pagamentos ?? collect();
        if ($pagamentos->isNotEmpty()) {
            foreach ($pagamentos as $pgto) {
                $detPag = $pag->addChild('detPag');
                $tPag = $this->formaPagamentoParaCodigo($pgto->formaPagamento?->tipo ?? 'outro');
                $detPag->addChild('tPag', $tPag);
                $detPag->addChild('vPag', number_format((float)$pgto->valor, 2, '.', ''));
            }
        } else {
            $detPag = $pag->addChild('detPag');
            $detPag->addChild('tPag', '01');
            $detPag->addChild('vPag', number_format($vNFTotal, 2, '.', ''));
        }

        $infAdic = $infNFe->addChild('infAdic');
        $infAdic->addChild('infCpl', 'NFC-e gerada pelo ERP Mercado');

        return $xml->asXML();
    }

    private function codigoMunicipio(string $cidade, string $uf): string
    {
        $codigo = Cidade::where('nome', $cidade)->whereHas('estado', fn($q) => $q->where('uf', $uf))->value('codigo_ibge');
        if ($codigo) return $codigo;
        throw new \RuntimeException("Código IBGE não encontrado para a cidade: {$cidade}/{$uf}");
    }

    private function ufParaCodigo(string $uf): string
    {
        $mapa = [
            'RO' => '11', 'AC' => '12', 'AM' => '13', 'RR' => '14',
            'PA' => '15', 'AP' => '16', 'TO' => '17', 'MA' => '21',
            'PI' => '22', 'CE' => '23', 'RN' => '24', 'PB' => '25',
            'PE' => '26', 'AL' => '27', 'SE' => '28', 'BA' => '29',
            'MG' => '31', 'ES' => '32', 'RJ' => '33', 'SP' => '35',
            'PR' => '41', 'SC' => '42', 'RS' => '43', 'MS' => '50',
            'MT' => '51', 'GO' => '52', 'DF' => '53',
        ];
        return $mapa[strtoupper($uf)] ?? '51';
    }

    private function formaPagamentoParaCodigo(string $tipo): string
    {
        return match ($tipo) {
            'dinheiro' => '01',
            'cartao_credito' => '03',
            'cartao_debito' => '04',
            'crediario' => '14',
            'pix' => '17',
            'voucher' => '10',
            default => '99',
        };
    }
}
