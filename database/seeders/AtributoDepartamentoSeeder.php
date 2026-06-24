<?php

namespace Database\Seeders;

use App\Models\Atributo;
use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AtributoDepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $planos = [
            'Açougue' => [
                ['Corte', 'lista', 'Alcatra, Filé Mignon, Picanha, Coxão Mole, Patinho, Lagarto, Maminha, Fraldinha, Cupim, Costela, Contra Filé, Acém, Paleta, Peito'],
                ['Tipo de Criação', 'lista', 'Caipira, Confinado, Orgânico, Free Range, Natural'],
                ['Apresentação', 'lista', 'Peça Inteira, Fatiado, Moído, em Cubos, Bife'],
                ['Com Osso', 'booleano', null],
                ['Refrigeração', 'lista', 'Resfriado, Congelado'],
            ],
            'Bebidas' => [
                ['Sabor (Bebidas)', 'lista', 'Cola, Guaraná, Laranja, Limão, Uva, Maçã, Pessêgo, Maracujá, Tutti Frutti, Lichia'],
                ['Volume (Bebidas)', 'decimal', null],
                ['Carbonatação', 'lista', 'Com Gás, Sem Gás'],
                ['Zero Açúcar', 'booleano', null],
                ['Concentrado', 'booleano', null],
            ],
            'Bebidas Alcoólicas' => [
                ['Tipo (Alcoólicas)', 'lista', 'Cerveja, Vinho, Whisky, Vodka, Gin, Cachaça, Conhaque, Tequila, Licor, Rum'],
                ['Volume (Alcoólicas)', 'decimal', null],
                ['Teor Alcoólico', 'decimal', null],
                ['Origem (Alcoólicas)', 'lista', 'Nacional, Importado'],
                ['Sabor (Alcoólicas)', 'lista', 'Tradicional, Puro Malte, Stout, IPA, Lager, Suave, Seco, Doce'],
            ],
            'Calçados' => [
                ['Tamanho (Calçados)', 'numero', null],
                ['Cor (Calçados)', 'checkbox', 'Preto, Branco, Azul, Vermelho, Marrom, Cinza, Bege, Rosa'],
                ['Gênero (Calçados)', 'radio', 'Masculino, Feminino, Infantil, Unissex'],
                ['Material (Calçados)', 'lista', 'Couro, Sintético, Borracha, Têxtil, EVA'],
            ],
            'Congelados, Resfriados e Sobremesas' => [
                ['Sabor (Congelados)', 'lista', 'Chocolate, Morango, Creme, Flocos, Napolitano, Baunilha, Limão, Açaí'],
                ['Tipo (Congelados)', 'lista', 'Sorvete, Picolé, Açaí, Pizza, Lasanha, Hambúrguer, Pronto, Petisco'],
                ['Vegano', 'booleano', null],
                ['Sem Glúten', 'booleano', null],
                ['Quantidade', 'decimal', null],
            ],
            'Frios e Laticínios' => [
                ['Apresentação (Frios)', 'lista', 'Fatiado, Peça, Ralado, Light, Light/Diet'],
                ['Embalagem (Frios)', 'lista', 'Fatiado, Embalado a Vácuo, Bandeja, Pote'],
                ['Zero Lactose', 'booleano', null],
                ['Light/Diet', 'booleano', null],
                ['Produto (Frios)', 'lista', 'Mussarela, Prato, Parmesão, Provolone, Minas Frescal, Requeijão, Manteiga, Margarina'],
            ],
            'Higiene e Perfumaria' => [
                ['Tipo de Pele', 'lista', 'Normal, Oleosa, Seca, Mista, Sensível, Acneica'],
                ['Tipo de Cabelo', 'lista', 'Liso, Cacheado, Ondulado, Crespo, Seco, Oleoso, Quimicamente Tratado'],
                ['FPS', 'numero', null],
                ['Hipoalergênico', 'booleano', null],
                ['Gênero (Higiene)', 'radio', 'Masculino, Feminino, Unissex, Infantil'],
                ['Volume (Higiene)', 'decimal', null],
            ],
            'Hortifruti' => [
                ['Cultivo', 'radio', 'Orgânico, Convencional, Hidropônico'],
                ['Unidade (Hortifruti)', 'lista', 'Unidade, Maço, Kg, Bandeja, Saca, Caixa'],
                ['Origem (Hortifruti)', 'lista', 'Nacional, Importado, Local'],
                ['Orgânico (Hortifruti)', 'booleano', null],
            ],
            'Limpeza' => [
                ['Concentrado (Limpeza)', 'booleano', null],
                ['Aroma', 'lista', 'Neutro, Lavanda, Limão, Floral, Mar, Verão, Talco'],
                ['Tipo de Superfície', 'checkbox', 'Pisos, Vidros, Cozinha, Banheiro, Roupas, Louças, Metais'],
                ['Volume (Limpeza)', 'decimal', null],
                ['Hipoalergênico (Limpeza)', 'booleano', null],
            ],
            'Magazine' => [
                ['Cor (Magazine)', 'checkbox', 'Preto, Branco, Cinza, Azul, Vermelho, Prata'],
                ['Material (Magazine)', 'lista', 'Plástico, Metal, Vidro, Madeira, Tecido'],
                ['Voltagem (Magazine)', 'radio', '110V, 220V, Bivolt, Universal'],
                ['Potência', 'decimal', null],
            ],
            'Mercearia' => [
                ['Tipo (Mercearia)', 'lista', 'Integral, Branco, Refinado, Cristal, Mascavo, Orgânico'],
                ['Peso Líquido', 'decimal', null],
                ['Orgânico (Mercearia)', 'booleano', null],
                ['Sem Glúten (Mercearia)', 'booleano', null],
                ['Zero Lactose (Mercearia)', 'booleano', null],
                ['Sabor (Mercearia)', 'lista', 'Tradicional, Chocolate, Morango, Baunilha, Coco, Limão'],
            ],
            'Padaria' => [
                ['Tipo (Padaria)', 'lista', 'Integral, Branco, Doce, Salgado, Italiano, Francês, Caseiro'],
                ['Peso (Padaria)', 'decimal', null],
                ['Recheio', 'lista', 'Chocolate, Creme, Frango, Queijo, Presunto, Ricota, Romeu e Julieta'],
                ['Congelado (Padaria)', 'booleano', null],
                ['Sem Glúten (Padaria)', 'booleano', null],
            ],
            'Papelaria' => [
                ['Cor (Papelaria)', 'checkbox', 'Azul, Preto, Vermelho, Verde, Rosa, Colorido'],
                ['Marca (Papelaria)', 'texto', null],
                ['Tipo (Papelaria)', 'lista', 'Escolar, Profissional, Infantil, Artístico'],
                ['Quantidade (Papelaria)', 'numero', null],
            ],
            'Peixes' => [
                ['Tipo (Peixes)', 'lista', 'Fresco, Congelado, Salgado, Defumado, Seco'],
                ['Corte (Peixes)', 'lista', 'Filé, Lombo, Posta, Inteiro, Costela, Anéis, Cauda'],
                ['Origem (Peixes)', 'lista', 'Nacional, Importado, Água Doce, Água Salgada'],
                ['Sem Espinha', 'booleano', null],
            ],
            'Pet' => [
                ['Espécie', 'radio', 'Cachorro, Gato, Pássaro, Peixe, Roedor, Réptil'],
                ['Porte', 'lista', 'Pequeno, Médio, Grande, Gigante'],
                ['Idade (Pet)', 'lista', 'Filhote, Adulto, Sênior, Todas as Idades'],
                ['Sabor (Pet)', 'lista', 'Carne, Frango, Peixe, Vegetais, Salmão, Cordeiro'],
                ['Tipo de Ração', 'lista', 'Seca, Úmida, Premiada, Premium, Super Premium'],
            ],
            'Utilidades e Casa' => [
                ['Material (Utilidades)', 'lista', 'Plástico, Vidro, Metal, Madeira, Alumínio, Inox, Cerâmica'],
                ['Cor (Utilidades)', 'checkbox', 'Preto, Branco, Cinza, Azul, Vermelho, Verde, Incolor'],
                ['Dimensão', 'texto', null],
                ['Capacidade', 'decimal', null],
                ['Voltagem (Utilidades)', 'radio', '110V, 220V, Bivolt, Sem Voltagem'],
            ],
            'Bebê e Infantil' => [
                ['Idade Recomendada', 'lista', '0-6 meses, 6-12 meses, 1-3 anos, 3-5 anos, 5-7 anos, 7+ anos'],
                ['Material (Bebê)', 'lista', 'Silicone, Plástico, Algodão, Poliéster, Borracha, Bambu'],
                ['Gênero (Bebê)', 'radio', 'Menino, Menina, Unissex'],
                ['Hipoalergênico (Bebê)', 'booleano', null],
            ],
        ];

        foreach ($planos as $deptoNome => $atributos) {
            $cat = Categoria::whereNull('parent_id')->where('nome', $deptoNome)->where('ativo', true)->first();
            if (!$cat) { $this->command->warn("Departamento '{$deptoNome}' não encontrado"); continue; }

            foreach ($atributos as $i => [$nome, $tipo, $opcoes]) {
                $slug = Str::slug($nome);
                $attr = Atributo::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'nome' => $nome,
                        'slug' => $slug,
                        'tipo' => $tipo,
                        'opcoes' => $opcoes ? explode(', ', $opcoes) : null,
                        'ativo' => true,
                    ]
                );

                DB::table('categorias_atributos')->updateOrInsert(
                    ['categoria_id' => $cat->id, 'atributo_id' => $attr->id],
                    [
                        'ordem' => $i + 1,
                        'ativo' => true,
                        'obrigatorio' => false,
                        'filtravel' => in_array($tipo, ['lista','radio','checkbox','booleano']),
                        'aparece_vitrine' => false,
                        'herda_subcategorias' => true,
                    ]
                );
            }

            $this->command->info("✓ {$deptoNome}: " . count($atributos) . " atributos vinculados");
        }

        $totalAttr = Atributo::count();
        $totalVinc = DB::table('categorias_atributos')->count();
        $this->command->info("\n✅ {$totalAttr} atributos criados, {$totalVinc} vínculos com categorias!");
    }
}
