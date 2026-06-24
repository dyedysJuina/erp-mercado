<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaCompletaSeeder extends Seeder
{
    public function run(): void
    {
        $n1 = $this->criarDepartamentos();
        $arvore = $this->montarArvore();

        foreach ($arvore as $deptoNome => $categorias) {
            $depto = $n1[$deptoNome] ?? null;
            if (!$depto) continue;

            foreach ($categorias as $catNome => $subs) {
                $cat = $this->criarCategoria($catNome, $depto->id, 2, $depto->caminho);
                foreach ($subs as $subNome) {
                    $this->criarCategoria($subNome, $cat->id, 3, $cat->caminho);
                }
            }
        }

        $total = Categoria::count();
        $this->command->info("✅ {$total} categorias criadas/atualizadas!");
    }

    private function criarDepartamentos(): array
    {
        $nomes = [
            'Açougue',
            'Bebê e Infantil',
            'Bebidas',
            'Bebidas Alcoólicas',
            'Calçados',
            'Congelados, Resfriados e Sobremesas',
            'Frios e Laticínios',
            'Higiene e Perfumaria',
            'Hortifruti',
            'Limpeza',
            'Magazine',
            'Mercearia',
            'Padaria',
            'Papelaria',
            'Peixes',
            'Pet',
            'Utilidades e Casa',
        ];

        $result = [];
        foreach ($nomes as $i => $nome) {
            $slug = Str::slug($nome);
            $cat = Categoria::firstOrCreate(
                ['slug' => $slug],
                [
                    'parent_id' => null,
                    'nome' => $nome,
                    'slug' => $slug,
                    'caminho' => $nome,
                    'nivel' => 1,
                    'ordem' => $i + 1,
                    'ativo' => true,
                ]
            );
            if (!isset($cat->id)) continue;
            $cat->update(['categoria_raiz_id' => $cat->id]);
            $result[$nome] = $cat;
        }
        return $result;
    }

    private function criarCategoria(string $nome, int $parentId, int $nivel, string $caminhoPai): Categoria
    {
        $slug = Str::slug($nome) . '-' . uniqid();
        $caminho = $caminhoPai . ' > ' . $nome;

        $cat = Categoria::firstOrCreate(
            ['slug' => Str::slug($nome)],
            [
                'parent_id' => $parentId,
                'nome' => $nome,
                'slug' => $slug,
                'caminho' => $caminho,
                'nivel' => $nivel,
                'ativo' => true,
            ]
        );

        if ($nivel === 1 && !$cat->categoria_raiz_id) {
            $cat->update(['categoria_raiz_id' => $cat->id]);
        }

        return $cat;
    }

    private function montarArvore(): array
    {
        return [

            // 1. Açougue
            'Açougue' => [
                'Aves' => ['Asa', 'Coxa', 'Filé', 'Frango Inteiro', 'Miúdos', 'Outras Carnes de Aves', 'Peito'],
                'Carne Bovina' => [
                    'Acém', 'Alcatra', 'Bife do Vazio', 'Capa de Filé', 'Carne Moída Congelada',
                    'Contra Filé', 'Coração da Alcatra', 'Costela', 'Coxão Duro', 'Coxão Mole',
                    'Cupim', 'Filé de Costela', 'Filé Mignon', 'Fralda', 'Fraldinha', 'Lagarto',
                    'Maminha', 'Miolo da Paleta', 'Miúdos Bovinos', 'Músculo', 'Paleta',
                    'Patinho', 'Peito', 'Peixinho', 'Picanha', 'Recorte Alcatra', 'Recorte Capa de Filé',
                ],
                'Carne Suína' => ['Alcatra Suína', 'Bisteca', 'Costela Suína', 'Lombo Suíno', 'Pernil Suíno', 'Picanha Suína'],
                'Carnes e Aves Especiais' => ['Chester', 'Peru', 'Tender'],
                'Linguiças' => ['Calabresa', 'Frango', 'Paio', 'Suína', 'Toscana'],
                'Salsichas' => ['Frango', 'Hot Dog', 'Soja'],
            ],

            // 2. Bebê e Infantil
            'Bebê e Infantil' => [
                'Acessórios Infantis' => ['Banheira', 'Bico de Mamadeira', 'Chupeta', 'Mamadeira'],
                'Alimentação Infantil' => ['Papinha', 'Preparo para Mingau', 'Purê de Frutas'],
                'Brinquedos' => ['Brinquedos Infantis'],
                'Higiene Infantil' => ['Banho e Pós Banho', 'Fraldas', 'Higiene Bucal', 'Lenço Umedecido', 'Pomada para Assaduras'],
            ],

            // 3. Bebidas
            'Bebidas' => [
                'Águas' => ['Com Gás', 'Coco', 'Saborizada', 'Sem Gás', 'Tônica'],
                'Bebidas Lácteas e Achocolatados' => ['Tradicional', 'Zero Açúcar e Lactose'],
                'Chás e Erva Mate' => ['Chá Pronto', 'Erva Mate'],
                'Energéticos e Isotônicos' => ['Energéticos', 'Isotônicos'],
                'Leites' => ['Desnatado', 'Integral', 'Semi Desnatado', 'Zero Lactose', 'Em Pó Integral', 'Em Pó Desnatado'],
                'Refrigerantes' => ['Tradicional', 'Light', 'Zero'],
                'Sucos, Néctares e Refrescos' => ['Integral', 'Pronto', 'Néctar', 'Refresco em Pó'],
            ],

            // 4. Bebidas Alcoólicas
            'Bebidas Alcoólicas' => [
                'Cervejas' => ['Com Álcool', 'Sem Álcool'],
                'Destilados' => ['Aguardente', 'Conhaque', 'Gin', 'Licor', 'Rum', 'Tequila', 'Vodka', 'Whisky'],
                'Vinhos' => ['Tinto', 'Branco', 'Rosé', 'Espumante'],
                'Sidras e Champanhes' => ['Champanhe', 'Sidra'],
            ],

            // 5. Calçados
            'Calçados' => [
                'Chinelos' => ['Chinelos Masculinos', 'Chinelos Femininos', 'Chinelos Infantis'],
                'Sandálias' => ['Sandálias Masculinas', 'Sandálias Femininas'],
            ],

            // 6. Congelados
            'Congelados, Resfriados e Sobremesas' => [
                'Batatas Congeladas' => ['Batata Frita', 'Batata Palha'],
                'Hambúrgueres' => ['Bovino', 'Frango', 'Vegetal'],
                'Lasanhas Congeladas' => ['Bovino', 'Frango', 'Vegetariana'],
                'Legumes e Vegetais Congelados' => ['Legumes', 'Vegetais', 'Polpas de Frutas'],
                'Pratos Prontos Congelados' => ['Massas', 'Pizza', 'Pão de Queijo', 'Petiscos'],
                'Sorvetes e Sobremesas' => ['Sorvete', 'Picolé', 'Açaí', 'Sobremesas'],
            ],

            // 7. Frios e Laticínios
            'Frios e Laticínios' => [
                'Frios' => ['Bacon', 'Lombo', 'Mortadela', 'Peito de Peru', 'Presunto', 'Salame'],
                'Iogurtes' => ['Natural', 'Grego', 'Líquido', 'Proteinado', 'Light/Diet', 'Sobremesa'],
                'Manteigas e Margarinas' => ['Manteiga', 'Margarina', 'Creme Vegetal'],
                'Queijos' => ['Mussarela', 'Prato', 'Parmesão', 'Provolone', 'Minas Frescal', 'Cheddar', 'Coalho', 'Gorgonzola', 'Requeijão'],
                'Cremes e Ricotas' => ['Creme de Leite', 'Ricota', 'Cottage', 'Creme Cheese'],
            ],

            // 8. Higiene e Perfumaria
            'Higiene e Perfumaria' => [
                'Cabelos' => ['Shampoo', 'Condicionador', 'Máscara Capilar', 'Creme de Pentear', 'Tintura'],
                'Corpo' => ['Sabonete', 'Desodorante', 'Hidratante', 'Protetor Solar', 'Repelente'],
                'Higiene Bucal' => ['Creme Dental', 'Escova Dental', 'Enxaguante', 'Fio Dental'],
                'Maquiagem' => ['Batom', 'Base', 'Sombra', 'Rímel', 'Delineador', 'Blush'],
                'Rosto' => ['Creme Facial', 'Hidratante', 'Esfoliante', 'Máscara Facial'],
                'Unhas' => ['Esmalte', 'Removedor', 'Alicate', 'Lixa'],
                'Papel Higiênico' => ['Papel Higiênico', 'Lenço Umedecido'],
                'Absorventes' => ['Absorventes', 'Protetor Diário'],
            ],

            // 9. Hortifruti
            'Hortifruti' => [
                'Frutas Frescas' => ['Frutas Frescas'],
                'Legumes' => ['Legumes'],
                'Verduras' => ['Verduras e Hortaliças'],
                'Ovos' => ['Ovos Brancos', 'Ovos Vermelhos', 'Ovos Caipira', 'Ovos de Codorna'],
                'Empório' => ['Empório'],
            ],

            // 10. Limpeza
            'Limpeza' => [
                'Cozinha' => ['Detergente', 'Desengordurante', 'Esponja', 'Sabão em Pasta', 'Saponáceo', 'Limpa Alumínio'],
                'Banheiro' => ['Água Sanitária', 'Limpador', 'Desinfetante', 'Pedra Sanitária'],
                'Lavanderia' => ['Sabão em Pó', 'Sabão Líquido', 'Amaciante', 'Alvejante', 'Tira Manchas'],
                'Limpeza Geral' => ['Desinfetante', 'Inseticida', 'Limpa Vidros', 'Lustra Móveis', 'Odorizador', 'Álcool'],
                'Automotivo' => ['Cera', 'Lubrificante', 'Limpeza Automotiva'],
                'Utensílios' => ['Vassoura', 'Rodo', 'Pá de Lixo', 'Saco de Lixo', 'Pano de Limpeza', 'Luva', 'Escova'],
            ],

            // 11. Magazine
            'Magazine' => [
                'Eletrodomésticos' => ['Eletrodomésticos'],
                'Eletrônicos' => ['Eletrônicos'],
                'Brinquedos' => ['Brinquedos', 'Jogos'],
                'Armarinhos' => ['Armarinhos'],
                'Confecções' => ['Cama Mesa e Banho', 'Confecções em Geral'],
                'Porcelanas e Cristais' => ['Porcelanas e Cristais'],
                'Produtos Térmicos' => ['Garrafa Térmica', 'Caixa Térmica'],
            ],

            // 12. Mercearia
            'Mercearia' => [
                'Açúcar e Adoçantes' => ['Açúcar Refinado', 'Açúcar Mascavo', 'Açúcar Cristal', 'Adoçante', 'Demerara'],
                'Azeites, Óleos e Vinagres' => ['Azeite', 'Óleo de Soja', 'Óleo de Cozinha', 'Vinagre'],
                'Arroz' => ['Arroz Branco', 'Arroz Integral', 'Arroz Parboilizado', 'Arroz Arbóreo'],
                'Biscoitos e Snacks' => ['Biscoito Doce', 'Biscoito Salgado', 'Bolacha Recheada', 'Salgadinho', 'Batata Frita', 'Cookie'],
                'Cafés, Chás e Achocolatados' => ['Café Torrado', 'Café Solúvel', 'Café em Cápsula', 'Chá', 'Achocolatado', 'Cappuccino'],
                'Enlatados e Conservas' => ['Atum', 'Sardinha', 'Milho', 'Ervilha', 'Azeitona', 'Palmito', 'Seleta'],
                'Feijão' => ['Feijão Carioca', 'Feijão Preto', 'Feijão Branco', 'Feijão Fradinho', 'Feijão Verde'],
                'Farinhas e Farináceos' => ['Farinha de Trigo', 'Farinha de Mandioca', 'Farinha de Milho', 'Fubá', 'Amido de Milho'],
                'Grãos e Cereais' => ['Lentilha', 'Grão de Bico', 'Milho de Pipoca', 'Ervilha Seca', 'Quinoa', 'Soja'],
                'Massas' => ['Macarrão', 'Macarrão Instantâneo', 'Lasanha', 'Macarrão Integral', 'Talharim'],
                'Molhos e Temperos' => ['Extrato de Tomate', 'Molho de Tomate', 'Maionese', 'Ketchup', 'Mostarda', 'Sal', 'Temperos'],
                'Óleos e Gorduras' => ['Óleo de Soja', 'Óleo de Canola', 'Banha', 'Margarina'],
                'Leite Condensado e Cremes' => ['Leite Condensado', 'Creme de Leite', 'Leite de Coco'],
                'Doces, Geleias e Pastas' => ['Doce de Leite', 'Geleia', 'Creme de Amendoim', 'Mel', 'Goiabada'],
                'Sopas e Cremes' => ['Sopa Instantânea', 'Creme', 'Caldo'],
                'Suplementos' => ['Suplementos', 'Barras de Proteína', 'Creatina'],
            ],

            // 13. Padaria
            'Padaria' => [
                'Pães' => ['Pão de Forma', 'Pão Francês', 'Pão Integral', 'Pão de Hambúrguer', 'Pão de Hot Dog', 'Pão Doce', 'Torrada'],
                'Bolos' => ['Bolo Pronto', 'Bolo Confeitado', 'Torta'],
                'Biscoitos de Padaria' => ['Biscoitos', 'Torresmo'],
                'Doces' => ['Doces', 'Panetone', 'Chocotone'],
                'Pizzas' => ['Pizzas'],
            ],

            // 14. Papelaria
            'Papelaria' => [
                'Cadernos e Agendas' => ['Caderno', 'Agenda', 'Caderneta'],
                'Canetas e Lápis' => ['Caneta', 'Lápis', 'Lapiseira', 'Marca Texto', 'Canetinha'],
                'Material Escolar' => ['Borracha', 'Apontador', 'Cola', 'Tesoura', 'Régua', 'Grafite', 'Corretivo'],
                'Lápis de Cor e Giz' => ['Lápis de Cor', 'Giz de Cera', 'Tinta Guache'],
                'Papel' => ['Sulfite', 'Cartolina', 'Papel Carmem'],
            ],

            // 15. Peixes
            'Peixes' => [
                'Peixes Frescos' => ['Peixes Frescos'],
                'Peixes Congelados' => ['Filé de Peixe', 'Lombo de Peixe', 'Costela de Peixe', 'Bacalhau'],
                'Frutos do Mar' => ['Camarão', 'Lula', 'Polvo', 'Mexilhão'],
            ],

            // 16. Pet
            'Pet' => [
                'Alimentos para Cães' => ['Ração Seca', 'Ração Úmida', 'Petiscos', 'Bifinhos', 'Ossinhos'],
                'Alimentos para Gatos' => ['Ração Seca', 'Ração Úmida', 'Petiscos'],
                'Alimentos para Pássaros' => ['Ração para Pássaros', 'Sementes'],
                'Higiene Pet' => ['Areia Sanitária', 'Shampoo', 'Talco', 'Tapete Higiênico'],
                'Acessórios' => ['Coleiras', 'Guias', 'Bebedouro', 'Comedouro', 'Brinquedos', 'Camas'],
                'Saúde Pet' => ['Antissépticos', 'Vermífugos'],
            ],

            // 17. Utilidades e Casa
            'Utilidades e Casa' => [
                'Panelas' => ['Panelas', 'Caçarolas', 'Frigideiras', 'Chaleiras'],
                'Talheres e Faqueiros' => ['Facas', 'Garfos', 'Colheres', 'Conjuntos', 'Espátulas'],
                'Utensílios de Cozinha' => ['Assadeiras', 'Formas', 'Canecos', 'Jarras', 'Potes', 'Tigelas'],
                'Copos e Taças' => ['Copos', 'Taças', 'Xícaras', 'Canecas'],
                'Descartáveis' => ['Copos Descartáveis', 'Pratos Descartáveis', 'Talheres Descartáveis', 'Sacos Plásticos'],
                'Produtos para Festa' => ['Decoração', 'Convites', 'Velas', 'Guardanapos'],
                'Jardinagem' => ['Vasos', 'Adubos', 'Fertilizantes', 'Sementes', 'Mangueiras'],
                'Ferramentas' => ['Martelos', 'Chaves', 'Alicates', 'Fitas', 'Colas', 'Pilhas'],
                'Churrasco' => ['Carvão', 'Grelha', 'Espeto', 'Churrasqueira', 'Isqueiro', 'Fósforo'],
                'Artigos para Pesca' => ['Anzol', 'Linha', 'Vara'],
            ],
        ];
    }
}
