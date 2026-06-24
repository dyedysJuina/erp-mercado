<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaComplementarSeeder extends Seeder
{
    public function run(): void
    {
        $this->criar([
            'Congelados, Resfriados e Sobremesas' => [
                'Gelo' => ['Gelo em Cubos', 'Gelo Triturado'],
                'Veganos e Vegetarianos' => ['Hambúrguer Vegano', 'Lasanha Vegana', 'Sobremesa Vegana'],
            ],
            'Higiene e Perfumaria' => [
                'Cuidados Geriátricos' => ['Fraldas Geriátricas', 'Protetor para Cama', 'Cuidadores'],
            ],
            'Limpeza' => [
                'Piscinas' => ['Cloro', 'Algicida', 'Teste de pH', 'Acessórios para Piscina'],
            ],
            'Mercearia' => [
                'Matinais' => ['Aveia', 'Cereal Matinal', 'Granola', 'Mingau'],
                'Cesta Básica' => ['Cesta Básica'],
            ],
            'Padaria' => [
                'Pascoattone e Colomba' => ['Pascoattone', 'Colomba Pascal'],
            ],
            'Utilidades e Casa' => [
                'Chimarrão e Tereré' => ['Bomba', 'Cuia', 'Garrafa Térmica para Chimarrão', 'Conjunto Chimarrão'],
            ],
        ]);

        $total = Categoria::count();
        $this->command->info("✅ Complemento criado! Total de categorias: {$total}");
    }

    private function criar(array $arvore): void
    {
        foreach ($arvore as $deptoNome => $categorias) {
            $depto = Categoria::whereNull('parent_id')->where('nome', $deptoNome)->first();
            if (!$depto) { $this->command->warn("Departamento '{$deptoNome}' não encontrado"); continue; }

            foreach ($categorias as $catNome => $subs) {
                $cat = $this->criarCat($catNome, $depto->id, 2, $depto->caminho);
                foreach ($subs as $subNome) {
                    $this->criarCat($subNome, $cat->id, 3, $cat->caminho);
                }
            }
        }
    }

    private function criarCat(string $nome, int $parentId, int $nivel, string $caminhoPai): Categoria
    {
        $slug = Str::slug($nome) . '-' . uniqid();
        $caminho = $caminhoPai . ' > ' . $nome;

        return Categoria::firstOrCreate(
            ['nome' => $nome, 'parent_id' => $parentId],
            [
                'parent_id' => $parentId,
                'nome' => $nome,
                'slug' => $slug,
                'caminho' => $caminho,
                'nivel' => $nivel,
                'ativo' => true,
            ]
        );
    }
}
