<?php

namespace App\Services;

use App\Models\ProdutoVariacao;
use App\Models\TabelaPrecoItem;
use App\Support\BrazilianNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PriceTableService
{
    public function duplicatePrices(int $fromTableId, int $toTableId): void
    {
        if ($fromTableId === 0) {
            $variacoes = ProdutoVariacao::where('ativo', true)->pluck('id');
            foreach ($variacoes as $vid) {
                TabelaPrecoItem::firstOrCreate([
                    'tabela_preco_id' => $toTableId,
                    'produto_variacao_id' => $vid,
                ]);
            }
        } else {
            DB::statement(
                'INSERT INTO tabela_precos_itens (tabela_preco_id, produto_variacao_id, preco_custo, margem_percentual, preco_venda, preco_atacado, created_at, updated_at)
                 SELECT ?, produto_variacao_id, preco_custo, margem_percentual, preco_venda, preco_atacado, NOW(), NOW()
                 FROM tabela_precos_itens WHERE tabela_preco_id = ?',
                [$toTableId, $fromTableId]
            );
        }
    }

    public function saveItems(int $tableId, array $items): int
    {
        try {
            $normalized = collect($items)->map(fn (array $item): array => [
                'id' => $item['id'] ?? null,
                'preco_custo' => BrazilianNumber::decimal($item['c'] ?? 0, 4),
                'margem_percentual' => BrazilianNumber::decimal($item['m'] ?? 0, 4),
                'preco_venda' => BrazilianNumber::decimal($item['v'] ?? 0),
                'preco_atacado' => BrazilianNumber::decimal($item['a'] ?? 0),
            ])->values()->all();
        } catch (InvalidArgumentException) {
            throw ValidationException::withMessages([
                'precos' => 'Existe um valor monetário ou percentual inválido.',
            ]);
        }

        Validator::make(['items' => $normalized], [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.preco_custo' => ['required', 'numeric', 'min:0', 'max:99999999.9999'],
            'items.*.margem_percentual' => ['required', 'numeric', 'min:-100', 'max:9999.9999'],
            'items.*.preco_venda' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.preco_atacado' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ])->validate();

            return DB::transaction(function () use ($tableId, $normalized): int {
                $ids = collect($normalized)->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();

                $allowedIds = DB::table('tabela_precos_itens')
                    ->where('tabela_preco_id', $tableId)
                    ->whereIn('id', $ids)
                    ->lockForUpdate()
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id);

                if ($allowedIds->count() !== $ids->count()) {
                    throw ValidationException::withMessages([
                        'precos' => 'Um ou mais itens não pertencem à tabela de preços selecionada.',
                    ]);
                }

                $case = 'UPDATE tabela_precos_itens SET ';
                $case .= 'preco_custo = CASE id ';
                foreach ($normalized as $item) {
                    $case .= sprintf("WHEN %d THEN %s ", (int)$item['id'], (float)$item['preco_custo']);
                }
                $case .= 'END, margem_percentual = CASE id ';
                foreach ($normalized as $item) {
                    $case .= sprintf("WHEN %d THEN %s ", (int)$item['id'], (float)$item['margem_percentual']);
                }
                $case .= 'END, preco_venda = CASE id ';
                foreach ($normalized as $item) {
                    $case .= sprintf("WHEN %d THEN %s ", (int)$item['id'], (float)$item['preco_venda']);
                }
                $case .= 'END, preco_atacado = CASE id ';
                foreach ($normalized as $item) {
                    $case .= sprintf("WHEN %d THEN %s ", (int)$item['id'], (float)$item['preco_atacado']);
                }
                $case .= 'END, updated_at = NOW() WHERE id IN (' . $ids->implode(',') . ') AND tabela_preco_id = ?';
                DB::update($case, [$tableId]);

                return count($normalized);
            });
    }
}
