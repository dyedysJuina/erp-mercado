<?php

namespace App\Services;

use App\Models\Ncm;
use App\Models\ProdutoVariacao;
use App\Models\RegraFiscalNcm;

class CalculoImpostoService
{
    public function calcular(ProdutoVariacao $variacao, float $valorUnitario, ?\App\Models\FiscalPerfil $perfil = null): array
    {
        $csosn = $this->resolverCsosn($variacao);
        $origem = $variacao->origem_mercadoria ?? '0';

        $icmsAliquota = $variacao->aliquota_icms ?? $perfil?->aliquota_icms;
        $pisAliquota  = $variacao->aliquota_pis ?? $perfil?->aliquota_pis;
        $cofinsAliquota = $variacao->aliquota_cofins ?? $perfil?->aliquota_cofins;

        return [
            'icms'   => $this->calcularIcms($csosn, $origem, $valorUnitario, $icmsAliquota),
            'pis'    => $this->calcularPisCofins($csosn, $valorUnitario, $pisAliquota, $variacao->cst_pis),
            'cofins' => $this->calcularPisCofins($csosn, $valorUnitario, $cofinsAliquota, $variacao->cst_cofins),
        ];
    }

    private function resolverCsosn(ProdutoVariacao $variacao): string
    {
        if ($variacao->cst_icms) return $variacao->cst_icms;

        $cest = $variacao->cest_id;
        if ($cest) return '500';

        $ncmCodigo = null;
        if ($variacao->ncm_id) {
            $ncm = Ncm::find($variacao->ncm_id);
            $ncmCodigo = $ncm?->codigo;
        }

        if ($ncmCodigo && preg_match('/^\d{8}$/', $ncmCodigo)) {
            $sugerido = RegraFiscalNcm::sugerirCsosn($ncmCodigo);
            if ($sugerido !== '102') return $sugerido;
        }

        return '102';
    }

    private function calcularIcms(string $csosn, string $origem, float $valor, ?float $aliquota): array
    {
        $st = in_array($csosn, ['201', '202', '203', '500']);
        $aliquotaFinal = $aliquota ?? 0.0;
        $base = $st ? $valor : ($aliquotaFinal > 0 ? $valor : 0.0);

        return [
            'cst'       => $csosn,
            'origem'    => (int)$origem,
            'aliquota'  => $aliquotaFinal,
            'base_calculo' => $base,
            'valor'     => $base * $aliquotaFinal / 100,
            'substituicao_tributaria' => $st,
            'manual'    => $aliquota !== null,
        ];
    }

    private function calcularPisCofins(string $csosn, float $valor, ?float $aliquota, ?string $cstVariacao = null): array
    {
        $cst = $cstVariacao ?: match ($csosn) {
            '101', '102', '201', '202' => '49',
            '103', '203', '300', '400' => '04',
            '500' => '05',
            default => '49',
        };
        $aliquotaFinal = $aliquota ?? 0.0;
        $base = $aliquotaFinal > 0 ? $valor : 0.0;

        return [
            'cst'      => $cst,
            'base_calculo' => $base,
            'aliquota' => $aliquotaFinal,
            'valor'    => $base * $aliquotaFinal / 100,
            'manual'   => $aliquota !== null,
        ];
    }
}
