<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CestSeeder extends Seeder
{
    public function run(): void
    {
        $cest = [
            ['0100100', 'Carnes bovinas, suínas, ovinas, caprinas e de aves', 'Carnes'],
            ['0100200', 'Peixes e frutos do mar', 'Peixes'],
            ['0100300', 'Leite e laticínios', 'Laticínios'],
            ['0100400', 'Ovos', 'Ovos'],
            ['0100500', 'Mel e outros produtos apícolas', 'Mel'],
            ['0100600', 'Óleos e gorduras vegetais e animais', 'Óleos'],
            ['0100700', 'Margarina e creme vegetal', 'Margarina'],
            ['0100800', 'Açúcares e produtos de confeitaria', 'Açúcar'],
            ['0100900', 'Cacau e seus derivados', 'Cacau'],
            ['0101000', 'Café, chá, mate e especiarias', 'Café'],
            ['0101100', 'Farinhas, sêmolas e flocos de cereais', 'Farinhas'],
            ['0101200', 'Massas alimentícias', 'Massas'],
            ['0101300', 'Pães, bolos, biscoitos e torradas', 'Panificação'],
            ['0101400', 'Preparações de cereais, farinhas ou amidos', 'Cereais'],
            ['0101500', 'Produtos de padaria, pastelaria e confeitaria', 'Padaria'],
            ['0101600', 'Sorvetes, pudins e sobremesas', 'Sorvetes'],
            ['0101700', 'Sopas, caldos e preparações alimentícias', 'Sopas'],
            ['0101800', 'Molhos, temperos e condimentos', 'Molhos'],
            ['0101900', 'Águas minerais e refrigerantes', 'Bebidas não alcoólicas'],
            ['0102000', 'Cervejas, chopes e outras bebidas alcoólicas', 'Bebidas alcoólicas'],
            ['0102100', 'Vinhos e sidras', 'Vinhos'],
            ['0102200', 'Destilados e aguardentes', 'Destilados'],
            ['0102300', 'Vinagres', 'Vinagres'],
            ['0102400', 'Produtos de higiene pessoal', 'Higiene'],
            ['0102500', 'Sabonetes, shampoos e condicionadores', 'Higiene capilar'],
            ['0102600', 'Perfumes e cosméticos', 'Cosméticos'],
            ['0102700', 'Produtos de limpeza e conservação', 'Limpeza'],
            ['0102800', 'Sabões e detergentes', 'Detergentes'],
            ['0102900', 'Produtos para lavanderia', 'Lavanderia'],
            ['0103000', 'Rações para animais domésticos', 'Pet food'],
            ['0103100', 'Produtos veterinários', 'Veterinário'],
            ['0103200', 'Ferramentas', 'Ferramentas'],
            ['0103300', 'Cutelaria e utensílios de cozinha', 'Utensílios'],
            ['0103400', 'Panelas e outros artigos para cozinha', 'Panelas'],
            ['0103500', 'Artigos de mesa e copa', 'Mesa e copa'],
            ['0103600', 'Produtos de papelaria', 'Papelaria'],
            ['0103700', 'Material escolar', 'Escolar'],
            ['0103800', 'Brinquedos e jogos', 'Brinquedos'],
            ['0103900', 'Artigos esportivos', 'Esportes'],
            ['0104000', 'Móveis e colchões', 'Móveis'],
            ['0104100', 'Eletrodomésticos', 'Eletrodomésticos'],
            ['0104200', 'Eletroeletrônicos', 'Eletrônicos'],
            ['0104300', 'Equipamentos de informática', 'Informática'],
            ['0104400', 'Telefones e smartphones', 'Telefonia'],
            ['0104500', 'Ferramentas elétricas', 'Ferramentas elétricas'],
            ['0104600', 'Materiais de construção', 'Construção'],
            ['0104700', 'Tintas e vernizes', 'Tintas'],
            ['0104800', 'Vidros e espelhos', 'Vidros'],
            ['0104900', 'Cerâmicas e porcelanatos', 'Cerâmica'],
            ['0105000', 'Produtos têxteis e vestuário', 'Têxtil'],
            ['0105100', 'Calçados', 'Calçados'],
            ['0105200', 'Bolsas, malas e mochilas', 'Bolsas'],
            ['0105300', 'Artigos de cama, mesa e banho', 'Cama mesa banho'],
            ['0105400', 'Jóias e bijuterias', 'Jóias'],
            ['0105500', 'Relógios', 'Relógios'],
            ['0105600', 'Instrumentos musicais', 'Instrumentos'],
            ['0105700', 'Veículos e autopeças', 'Automotivo'],
            ['0105800', 'Pneus e câmaras de ar', 'Pneus'],
            ['0105900', 'Bicicletas', 'Bicicletas'],
            ['0106000', 'Máquinas e equipamentos', 'Máquinas'],
            ['0106100', 'Embalagens', 'Embalagens'],
            ['0106200', 'Velas e artigos de iluminação', 'Velas'],
            ['0106300', 'Produtos fumígenos', 'Fumígenos'],
            ['0106400', 'Medicamentos e farmacêuticos', 'Farmácia'],
            ['0106500', 'Correlatos e produtos para saúde', 'Saúde'],
            ['0106600', 'Produtos ópticos', 'Ótica'],
            ['0106700', 'Combustíveis e lubrificantes', 'Combustíveis'],
            ['0106800', 'Gás liquefeito de petróleo (GLP)', 'GLP'],
            ['0106900', 'Produtos químicos', 'Químicos'],
            ['0107000', 'Defensivos agrícolas', 'Defensivos'],
            ['0107100', 'Fertilizantes', 'Fertilizantes'],
            ['0107200', 'Sementes e mudas', 'Sementes'],
            ['0107300', 'Produtos agropecuários', 'Agropecuária'],
            ['0107400', 'Papel e celulose', 'Papel'],
            ['0107500', 'Produtos de limpeza profissional', 'Limpeza profissional'],
            ['0107600', 'Artigos de festa e decoração', 'Festa'],
            ['0107700', 'Plantas e flores', 'Plantas'],
            ['0107800', 'Artigos para animais vivos', 'Animais'],
            ['0107900', 'Materiais para escritório', 'Escritório'],
            ['0108000', 'Equipamentos de segurança', 'Segurança'],
            ['0108100', 'Produtos para piscina', 'Piscina'],
            ['0108200', 'Artigos de jardinagem', 'Jardinagem'],
            ['0108300', 'Produtos de camping', 'Camping'],
            ['0108400', 'Artigos de pesca', 'Pesca'],
        ];

        $data = [];
        foreach ($cest as $item) {
            $data[] = ['codigo' => $item[0], 'descricao' => $item[1], 'segmento' => $item[2], 'ativo' => true];
        }
        DB::table('cest')->insertOrIgnore($data);
    }
}
