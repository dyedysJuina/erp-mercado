<?php

namespace App\Services;

use App\Support\BrazilianNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PdvSaleService
{
    public function openCash(int $storeId, int $userId, mixed $openingValue): int
    {
        $opening = $this->money($openingValue, 'valorAbertura');

        return DB::transaction(function () use ($storeId, $userId, $opening): int {
            $store = DB::table('lojas')->where('id', $storeId)->where('ativo', true)->lockForUpdate()->first();

            if (! $store) {
                $this->fail('loja_id', 'A loja selecionada não está ativa.');
            }

            if (! $store->tabela_preco_id) {
                $this->fail('loja_id', 'Vincule uma tabela de preços à loja antes de abrir o PDV.');
            }

            $cash = DB::table('pdv_caixas')
                ->where('loja_id', $storeId)
                ->where('ativo', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $cash) {
                $cashId = DB::table('pdv_caixas')->insertGetId([
                    'loja_id' => $storeId,
                    'nome' => 'Caixa principal',
                    'numero' => '1',
                    'ativo' => true,
                ]);
            } else {
                $cashId = (int) $cash->id;
            }

            $existing = DB::table('pdv_caixas_aberturas')
                ->where('caixa_id', $cashId)
                ->where('usuario_id', $userId)
                ->where('status', 'aberto')
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($existing) {
                return (int) $existing->id;
            }

            return DB::table('pdv_caixas_aberturas')->insertGetId([
                'caixa_id' => $cashId,
                'usuario_id' => $userId,
                'status' => 'aberto',
                'valor_abertura' => $opening,
                'aberto_at' => now(),
            ]);
        });
    }

    public function finalize(
        int $storeId,
        int $openingId,
        int $userId,
        ?int $clientId,
        array $cart,
        mixed $discount,
        mixed $addition,
        int $paymentMethodId,
        mixed $received,
    ): array {
        if ($cart === []) {
            $this->fail('carrinho', 'Adicione pelo menos um produto à venda.');
        }

        $discountCents = $this->cents($discount, 'desconto');
        $additionCents = $this->cents($addition, 'acrescimo');
        $receivedCents = $this->cents($received, 'valorRecebido');

        return DB::transaction(function () use ($storeId, $openingId, $userId, $clientId, $cart, $discountCents, $additionCents, $paymentMethodId, $receivedCents): array {
            $opening = DB::table('pdv_caixas_aberturas as a')
                ->join('pdv_caixas as c', 'c.id', '=', 'a.caixa_id')
                ->where('a.id', $openingId)
                ->where('a.usuario_id', $userId)
                ->where('a.status', 'aberto')
                ->where('c.loja_id', $storeId)
                ->lockForUpdate()
                ->first(['a.id']);

            if (! $opening) {
                $this->fail('caixa', 'O caixa não está aberto para este usuário e esta loja.');
            }

            $store = DB::table('lojas')->where('id', $storeId)->lockForUpdate()->first();
            $payment = DB::table('formas_pagamento')->where('id', $paymentMethodId)->where('ativo', true)->first();

            if (! $store?->tabela_preco_id) {
                $this->fail('loja_id', 'A loja não possui tabela de preços ativa.');
            }

            if (! $payment) {
                $this->fail('forma_pagamento_id', 'Selecione uma forma de pagamento válida.');
            }

            $normalizedCart = collect($cart)->map(function (array $item): array {
                $quantity = $this->quantity($item['quantidade'] ?? 0);

                return ['variation_id' => (int) ($item['variacao_id'] ?? 0), 'quantity' => $quantity];
            })->groupBy('variation_id')->map(fn ($items, $variationId): array => [
                'variation_id' => (int) $variationId,
                'quantity' => round($items->sum('quantity'), 3),
            ])->values();

            $variationIds = $normalizedCart->pluck('variation_id');
            $variations = DB::table('produto_variacoes')->whereIn('id', $variationIds)->where('ativo', true)->lockForUpdate()->get()->keyBy('id');
            $prices = DB::table('tabela_precos_itens')->where('tabela_preco_id', $store->tabela_preco_id)->whereIn('produto_variacao_id', $variationIds)->lockForUpdate()->get()->keyBy('produto_variacao_id');
            $stocks = DB::table('estoque_saldos')->where('loja_id', $storeId)->whereIn('produto_variacao_id', $variationIds)->lockForUpdate()->get()->keyBy('produto_variacao_id');

            $items = [];
            $subtotalCents = 0;
            $itemDiscountCentsTotal = 0;

            foreach ($normalizedCart as $cartItem) {
                $variation = $variations->get($cartItem['variation_id']);
                $price = $prices->get($cartItem['variation_id']);
                $stock = $stocks->get($cartItem['variation_id']);

                if (! $variation || ! $price || (float) $price->preco_venda <= 0) {
                    $this->fail('carrinho', 'Um produto está inativo ou sem preço de venda válido.');
                }

                $minimum = max(0.001, (float) $variation->quantidade_minima_venda);
                $step = max(0.001, (float) $variation->passo_venda);
                $quantity = $cartItem['quantity'];

                if ($quantity < $minimum || abs(($quantity / $step) - round($quantity / $step)) > 0.0001) {
                    $this->fail('carrinho', "Quantidade inválida para {$variation->nome_completo}.");
                }

                $available = $stock ? (float) $stock->quantidade_atual - (float) $stock->quantidade_reservada : 0;
                if ($available < $quantity - 0.0001) {
                    $this->fail('carrinho', "Estoque insuficiente para {$variation->nome_completo}. Disponível: ".number_format($available, 3, ',', '.'));
                }

                $unitCents = (int) round((float) $price->preco_venda * 100, 0, PHP_ROUND_HALF_UP);
                $lineCents = (int) round($quantity * $unitCents, 0, PHP_ROUND_HALF_UP);
                $itemDiscCents = (int) round($this->cents($cartItem['desconto'] ?? 0), 0, PHP_ROUND_HALF_UP);
                $itemDiscountCentsTotal += $itemDiscCents;
                $subtotalCents += $lineCents;
                $items[] = compact('variation', 'stock', 'quantity', 'unitCents', 'lineCents', 'itemDiscCents');
            }

            $totalCents = $subtotalCents + $additionCents - $discountCents - $itemDiscountCentsTotal;
            if ($totalCents <= 0) {
                $this->fail('desconto', 'O total da venda precisa ser maior que zero.');
            }

            if ($payment->tipo === 'dinheiro' && $receivedCents < $totalCents) {
                $this->fail('valorRecebido', 'O valor recebido é menor que o total da venda.');
            }

            $saleId = DB::table('pdv_vendas')->insertGetId([
                'loja_id' => $storeId,
                'caixa_abertura_id' => $openingId,
                'usuario_id' => $userId,
                'cliente_id' => $clientId,
                'status' => 'concluida',
                'subtotal' => $subtotalCents / 100,
                'desconto' => $discountCents / 100,
                'acrescimo' => $additionCents / 100,
                'total' => $totalCents / 100,
                'created_at' => now(),
                'updated_at' => now(),
                'finalizada_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('pdv_venda_itens')->insert([
                    'venda_id' => $saleId,
                    'produto_variacao_id' => $item['variation']->id,
                    'quantidade' => $item['quantity'],
                    'preco_unitario' => $item['unitCents'] / 100,
                    'desconto' => $item['itemDiscCents'] / 100,
                    'total_item' => ($item['lineCents'] - $item['itemDiscCents']) / 100,
                    'cancelado' => false,
                ]);

                if ($item['stock']) {
                    $updated = DB::table('estoque_saldos')
                        ->where('id', $item['stock']->id)
                        ->where('quantidade_atual', '>=', $item['quantity'])
                        ->decrement('quantidade_atual', $item['quantity']);

                    if ((int) $updated !== 1) {
                        $this->fail('carrinho', "O estoque de {$item['variation']->nome_completo} mudou durante a venda.");
                    }
                }

                DB::table('estoque_movimentacoes')->insert([
                    'loja_id' => $storeId,
                    'produto_variacao_id' => $item['variation']->id,
                    'usuario_id' => $userId,
                    'origem_tipo' => 'pdv_venda',
                    'origem_id' => $saleId,
                    'tipo' => 'saida_venda_pdv',
                    'quantidade' => $item['quantity'],
                    'justificativa' => "Venda PDV #{$saleId}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('pdv_venda_pagamentos')->insert([
                'venda_id' => $saleId,
                'forma_pagamento_id' => $paymentMethodId,
                'valor' => $totalCents / 100,
                'parcelas' => 1,
            ]);

            DB::table('financeiro_lancamentos')->insert([
                'empresa_id' => $store->empresa_id,
                'loja_id' => $storeId,
                'categoria_id' => $this->defaultReceitaCategoriaId(),
                'pdv_venda_id' => $saleId,
                'tipo' => 'receita',
                'descricao' => "Venda PDV #{$saleId}",
                'valor' => $totalCents / 100,
                'data_competencia' => now(),
                'data_vencimento' => now(),
                'data_pagamento' => now(),
                'status' => 'pago',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'id' => $saleId,
                'total' => $totalCents / 100,
                'change' => $payment->tipo === 'dinheiro' ? max(0, $receivedCents - $totalCents) / 100 : 0,
            ];
        });
    }

    public function finalizeMulti(
        int $storeId,
        int $openingId,
        int $userId,
        ?int $clientId,
        array $cart,
        mixed $discount,
        mixed $addition,
        array $payments,
    ): array {
        if ($cart === []) {
            $this->fail('carrinho', 'Adicione pelo menos um produto à venda.');
        }

        if ($payments === []) {
            $this->fail('forma_pagamento_id', 'Adicione pelo menos uma forma de pagamento.');
        }

        $discountCents = $this->cents($discount, 'desconto');
        $additionCents = $this->cents($addition, 'acrescimo');

        return DB::transaction(function () use ($storeId, $openingId, $userId, $clientId, $cart, $discountCents, $additionCents, $payments): array {
            $opening = DB::table('pdv_caixas_aberturas as a')
                ->join('pdv_caixas as c', 'c.id', '=', 'a.caixa_id')
                ->where('a.id', $openingId)
                ->where('a.usuario_id', $userId)
                ->where('a.status', 'aberto')
                ->where('c.loja_id', $storeId)
                ->lockForUpdate()
                ->first(['a.id']);

            if (! $opening) {
                $this->fail('caixa', 'O caixa não está aberto para este usuário e esta loja.');
            }

            $store = DB::table('lojas')->where('id', $storeId)->lockForUpdate()->first();

            if (! $store?->tabela_preco_id) {
                $this->fail('loja_id', 'A loja não possui tabela de preços ativa.');
            }

            $normalizedCart = collect($cart)->map(function (array $item): array {
                $quantity = $this->quantity($item['quantidade'] ?? 0);
                return ['variation_id' => (int) ($item['variacao_id'] ?? 0), 'quantity' => $quantity];
            })->groupBy('variation_id')->map(fn ($items, $variationId): array => [
                'variation_id' => (int) $variationId,
                'quantity' => round($items->sum('quantity'), 3),
            ])->values();

            $variationIds = $normalizedCart->pluck('variation_id');
            $variations = DB::table('produto_variacoes')->whereIn('id', $variationIds)->where('ativo', true)->lockForUpdate()->get()->keyBy('id');
            $prices = DB::table('tabela_precos_itens')->where('tabela_preco_id', $store->tabela_preco_id)->whereIn('produto_variacao_id', $variationIds)->lockForUpdate()->get()->keyBy('produto_variacao_id');
            $stocks = DB::table('estoque_saldos')->where('loja_id', $storeId)->whereIn('produto_variacao_id', $variationIds)->lockForUpdate()->get()->keyBy('produto_variacao_id');

            $items = [];
            $subtotalCents = 0;
            $itemDiscountCentsTotal = 0;

            foreach ($normalizedCart as $cartItem) {
                $variation = $variations->get($cartItem['variation_id']);
                $price = $prices->get($cartItem['variation_id']);
                $stock = $stocks->get($cartItem['variation_id']);

                if (! $variation || ! $price || (float) $price->preco_venda <= 0) {
                    $this->fail('carrinho', 'Um produto está inativo ou sem preço de venda válido.');
                }

                $minimum = max(0.001, (float) $variation->quantidade_minima_venda);
                $step = max(0.001, (float) $variation->passo_venda);
                $quantity = $cartItem['quantity'];

                if ($quantity < $minimum || abs(($quantity / $step) - round($quantity / $step)) > 0.0001) {
                    $this->fail('carrinho', "Quantidade inválida para {$variation->nome_completo}.");
                }

                $available = $stock ? (float) $stock->quantidade_atual - (float) $stock->quantidade_reservada : 0;
                if ($available < $quantity - 0.0001) {
                    $this->fail('carrinho', "Estoque insuficiente para {$variation->nome_completo}. Disponível: ".number_format($available, 3, ',', '.'));
                }

                $unitCents = (int) round((float) $price->preco_venda * 100, 0, PHP_ROUND_HALF_UP);
                $lineCents = (int) round($quantity * $unitCents, 0, PHP_ROUND_HALF_UP);
                $itemDiscCents = (int) round($this->cents($cartItem['desconto'] ?? 0), 0, PHP_ROUND_HALF_UP);
                $itemDiscountCentsTotal += $itemDiscCents;
                $subtotalCents += $lineCents;
                $items[] = compact('variation', 'stock', 'quantity', 'unitCents', 'lineCents', 'itemDiscCents');
            }

            $totalCents = $subtotalCents + $additionCents - $discountCents - $itemDiscountCentsTotal;
            if ($totalCents <= 0) {
                $this->fail('desconto', 'O total da venda precisa ser maior que zero.');
            }

            $paymentSum = 0;
            $receivedCents = 0;
            foreach ($payments as $p) {
                $valCents = $this->cents($p['valor'] ?? 0, 'forma_pagamento_id');
                $paymentSum += $valCents;
                if (isset($p['tipo']) && $p['tipo'] === 'dinheiro') {
                    $receivedCents += $valCents;
                }
            }

            if (abs($paymentSum - $totalCents) > 1) {
                $this->fail('forma_pagamento_id', 'A soma dos pagamentos difere do total da venda.');
            }

            $saleId = DB::table('pdv_vendas')->insertGetId([
                'loja_id' => $storeId,
                'caixa_abertura_id' => $openingId,
                'usuario_id' => $userId,
                'cliente_id' => $clientId,
                'status' => 'concluida',
                'subtotal' => $subtotalCents / 100,
                'desconto' => $discountCents / 100,
                'acrescimo' => $additionCents / 100,
                'total' => $totalCents / 100,
                'created_at' => now(),
                'updated_at' => now(),
                'finalizada_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('pdv_venda_itens')->insert([
                    'venda_id' => $saleId,
                    'produto_variacao_id' => $item['variation']->id,
                    'quantidade' => $item['quantity'],
                    'preco_unitario' => $item['unitCents'] / 100,
                    'desconto' => $item['itemDiscCents'] / 100,
                    'total_item' => ($item['lineCents'] - $item['itemDiscCents']) / 100,
                    'cancelado' => false,
                ]);

                if ($item['stock']) {
                    $updated = DB::table('estoque_saldos')
                        ->where('id', $item['stock']->id)
                        ->where('quantidade_atual', '>=', $item['quantity'])
                        ->decrement('quantidade_atual', $item['quantity']);

                    if ((int) $updated !== 1) {
                        $this->fail('carrinho', "O estoque de {$item['variation']->nome_completo} mudou durante a venda.");
                    }
                }

                DB::table('estoque_movimentacoes')->insert([
                    'loja_id' => $storeId,
                    'produto_variacao_id' => $item['variation']->id,
                    'usuario_id' => $userId,
                    'origem_tipo' => 'pdv_venda',
                    'origem_id' => $saleId,
                    'tipo' => 'saida_venda_pdv',
                    'quantidade' => $item['quantity'],
                    'justificativa' => "Venda PDV #{$saleId}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($payments as $p) {
                $valCents = $this->cents($p['valor'] ?? 0, 'forma_pagamento_id');
                $formaId = (int) ($p['forma_pagamento_id'] ?? 0);
                $formaExiste = DB::table('formas_pagamento')->where('id', $formaId)->exists();
                if (!$formaExiste) {
                    $this->fail('forma_pagamento_id', 'Forma de pagamento inválida.');
                }
                DB::table('pdv_venda_pagamentos')->insert([
                    'venda_id' => $saleId,
                    'forma_pagamento_id' => $formaId,
                    'valor' => $valCents / 100,
                    'parcelas' => 1,
                ]);
            }

            DB::table('financeiro_lancamentos')->insert([
                'empresa_id' => $store->empresa_id,
                'loja_id' => $storeId,
                'categoria_id' => $this->defaultReceitaCategoriaId(),
                'pdv_venda_id' => $saleId,
                'tipo' => 'receita',
                'descricao' => "Venda PDV #{$saleId}",
                'valor' => $totalCents / 100,
                'data_competencia' => now(),
                'data_vencimento' => now(),
                'data_pagamento' => now(),
                'status' => 'pago',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $change = $receivedCents > $totalCents ? ($receivedCents - $totalCents) / 100 : 0;

            return [
                'id' => $saleId,
                'total' => $totalCents / 100,
                'change' => $change,
            ];
        });
    }

    private function money(mixed $value, string $field): string
    {
        try {
            return BrazilianNumber::decimal($value, 2);
        } catch (InvalidArgumentException) {
            $this->fail($field, 'Informe um valor monetário válido.');
        }
    }

    private function cents(mixed $value, string $field): int
    {
        return (int) round((float) $this->money($value, $field) * 100, 0, PHP_ROUND_HALF_UP);
    }

    private function quantity(mixed $value): float
    {
        try {
            $quantity = (float) BrazilianNumber::decimal($value, 3);
        } catch (InvalidArgumentException) {
            $this->fail('carrinho', 'Existe uma quantidade inválida no carrinho.');
        }

        if ($quantity <= 0 || $quantity > 999999999.999) {
            $this->fail('carrinho', 'As quantidades precisam ser maiores que zero.');
        }

        return $quantity;
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    private function defaultReceitaCategoriaId(): ?int
    {
        return \App\Models\FinanceiroCategoria::where('tipo', 'receita')
            ->where('ativo', true)
            ->orderBy('id')
            ->value('id');
    }
}
