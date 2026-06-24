/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `anexos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anexos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entidade_tipo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidade_id` bigint unsigned NOT NULL,
  `nome_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caminho` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamanho_bytes` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `atributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atributos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('texto','numero','decimal','booleano','lista','data') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'texto',
  `unidade_medida_id` bigint unsigned DEFAULT NULL,
  `opcoes` json DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `atributos_slug_unique` (`slug`),
  KEY `atributos_unidade_medida_id_foreign` (`unidade_medida_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `auditoria_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `loja_id` bigint unsigned DEFAULT NULL,
  `acao` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidade_tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entidade_id` bigint unsigned DEFAULT NULL,
  `dados_antes` json DEFAULT NULL,
  `dados_depois` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auditoria_logs_entidade_tipo_entidade_id_index` (`entidade_tipo`,`entidade_id`),
  KEY `auditoria_logs_usuario_id_created_at_index` (`usuario_id`,`created_at`),
  KEY `auditoria_logs_loja_id_foreign` (`loja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `carrinho_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrinho_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `carrinho_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `oferta_produto_id` bigint unsigned DEFAULT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  `preco_unitario` decimal(12,2) NOT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ci_unique` (`carrinho_id`,`loja_id`,`produto_variacao_id`,`oferta_produto_id`),
  KEY `carrinho_itens_loja_id_foreign` (`loja_id`),
  KEY `carrinho_itens_produto_variacao_id_foreign` (`produto_variacao_id`),
  KEY `carrinho_itens_oferta_produto_id_foreign` (`oferta_produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `carrinhos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrinhos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `session_token` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loja_id` bigint unsigned DEFAULT NULL,
  `status` enum('ativo','convertido','abandonado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carrinhos_cliente_id_foreign` (`cliente_id`),
  KEY `carrinhos_loja_id_foreign` (`loja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `categoria_raiz_id` bigint unsigned DEFAULT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caminho` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel` tinyint NOT NULL DEFAULT '1',
  `icone` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem` int NOT NULL DEFAULT '0',
  `permite_produtos` tinyint(1) NOT NULL DEFAULT '1',
  `destaque_site` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_slug_unique` (`slug`),
  KEY `categorias_parent_id_ordem_index` (`parent_id`,`ordem`),
  KEY `categorias_categoria_raiz_id_index` (`categoria_raiz_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias_atributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_atributos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned NOT NULL,
  `atributo_id` bigint unsigned NOT NULL,
  `obrigatorio` tinyint(1) NOT NULL DEFAULT '0',
  `filtravel` tinyint(1) NOT NULL DEFAULT '1',
  `aparece_vitrine` tinyint(1) NOT NULL DEFAULT '0',
  `herda_subcategorias` tinyint(1) NOT NULL DEFAULT '1',
  `ordem` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorias_atributos_categoria_id_atributo_id_unique` (`categoria_id`,`atributo_id`),
  KEY `categorias_atributos_categoria_id_ordem_index` (`categoria_id`,`ordem`),
  KEY `categorias_atributos_atributo_id_foreign` (`atributo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cidades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estado_id` bigint unsigned NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_ibge` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cidades_estado_id_nome_unique` (`estado_id`,`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `aceita_marketing` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_email_unique` (`email`),
  UNIQUE KEY `clientes_cpf_unique` (`cpf`),
  UNIQUE KEY `clientes_whatsapp_unique` (`whatsapp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `clientes_enderecos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes_enderecos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `titulo` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Principal',
  `cidade_id` bigint unsigned NOT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referencia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `clientes_enderecos_cliente_id_foreign` (`cliente_id`),
  KEY `clientes_enderecos_cidade_id_foreign` (`cidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compras_cotacao_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_cotacao_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotacao_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  `preco_cotado` decimal(12,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_cotacao_itens_cotacao_id_foreign` (`cotacao_id`),
  KEY `compras_cotacao_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compras_cotacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_cotacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `status` enum('aberta','respondida','aprovada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberta',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_cotacoes_loja_id_foreign` (`loja_id`),
  KEY `compras_cotacoes_fornecedor_id_foreign` (`fornecedor_id`),
  KEY `compras_cotacoes_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compras_pedido_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_pedido_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compra_pedido_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `produto_apresentacao_id` bigint unsigned DEFAULT NULL,
  `quantidade_pedida` decimal(12,3) NOT NULL,
  `quantidade_convertida_estoque` decimal(14,3) DEFAULT NULL,
  `quantidade_recebida` decimal(12,3) NOT NULL DEFAULT '0.000',
  `custo_unitario` decimal(12,4) NOT NULL,
  `custo_unitario_estoque` decimal(12,4) DEFAULT NULL,
  `total_item` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_pedido_itens_produto_apresentacao_id_index` (`produto_apresentacao_id`),
  KEY `compras_pedido_itens_compra_pedido_id_foreign` (`compra_pedido_id`),
  KEY `compras_pedido_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compras_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_pedidos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `status` enum('rascunho','enviado','parcialmente_recebido','recebido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `total_produtos` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_frete` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_pedido` decimal(12,2) NOT NULL DEFAULT '0.00',
  `previsao_entrega` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_pedidos_loja_id_foreign` (`loja_id`),
  KEY `compras_pedidos_fornecedor_id_foreign` (`fornecedor_id`),
  KEY `compras_pedidos_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compras_recebimento_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_recebimento_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recebimento_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `produto_apresentacao_id` bigint unsigned DEFAULT NULL,
  `lote_id` bigint unsigned DEFAULT NULL,
  `quantidade_recebida` decimal(12,3) NOT NULL,
  `quantidade_convertida_estoque` decimal(14,3) DEFAULT NULL,
  `custo_unitario` decimal(12,4) NOT NULL,
  `custo_unitario_estoque` decimal(12,4) DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_recebimento_itens_produto_apresentacao_id_index` (`produto_apresentacao_id`),
  KEY `compras_recebimento_itens_recebimento_id_foreign` (`recebimento_id`),
  KEY `compras_recebimento_itens_produto_variacao_id_foreign` (`produto_variacao_id`),
  KEY `compras_recebimento_itens_lote_id_foreign` (`lote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `compras_recebimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras_recebimentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compra_pedido_id` bigint unsigned DEFAULT NULL,
  `loja_id` bigint unsigned NOT NULL,
  `fornecedor_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fiscal_documento_id` bigint unsigned DEFAULT NULL,
  `numero_nota` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chave_nfe` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('em_conferencia','conferido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'em_conferencia',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `compras_recebimentos_compra_pedido_id_foreign` (`compra_pedido_id`),
  KEY `compras_recebimentos_loja_id_foreign` (`loja_id`),
  KEY `compras_recebimentos_fornecedor_id_foreign` (`fornecedor_id`),
  KEY `compras_recebimentos_usuario_id_foreign` (`usuario_id`),
  KEY `compras_recebimentos_fiscal_documento_id_foreign` (`fiscal_documento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuracoes_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracoes_sistema` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `loja_id` bigint unsigned DEFAULT NULL,
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` json NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `configuracoes_sistema_empresa_id_loja_id_chave_unique` (`empresa_id`,`loja_id`,`chave`),
  KEY `configuracoes_sistema_loja_id_foreign` (`loja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crm_clientes_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crm_clientes_grupos` (
  `cliente_id` bigint unsigned NOT NULL,
  `grupo_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`cliente_id`,`grupo_id`),
  KEY `crm_clientes_grupos_grupo_id_foreign` (`grupo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crm_cupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crm_cupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_desconto` enum('valor','percentual','frete_gratis') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_minimo_pedido` decimal(12,2) NOT NULL DEFAULT '0.00',
  `uso_maximo` int DEFAULT NULL,
  `uso_por_cliente` int NOT NULL DEFAULT '1',
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `crm_cupons_codigo_unique` (`codigo`),
  KEY `crm_cupons_empresa_id_foreign` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crm_cupons_usos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crm_cupons_usos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cupom_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `pedido_id` bigint unsigned DEFAULT NULL,
  `valor_desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_cupons_usos_cupom_id_foreign` (`cupom_id`),
  KEY `crm_cupons_usos_cliente_id_foreign` (`cliente_id`),
  KEY `crm_cupons_usos_pedido_id_foreign` (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crm_grupos_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crm_grupos_clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `crm_grupos_clientes_empresa_id_foreign` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crm_pontos_movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `crm_pontos_movimentacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `pedido_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('credito','debito','expiracao','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pontos` int NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `crm_pontos_movimentacoes_cliente_id_foreign` (`cliente_id`),
  KEY `crm_pontos_movimentacoes_pedido_id_foreign` (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `embalagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `embalagens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sigla` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `embalagens_nome_unique` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inscricao_estadual` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inscricao_municipal` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regime_tributario` enum('simples_nacional','lucro_presumido','lucro_real') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'simples_nacional',
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresas_cnpj_unique` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entregas_ocorrencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entregas_ocorrencias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('cliente_ausente','endereco_incorreto','produto_danificado','atraso','outro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'outro',
  `descricao` mediumtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entregas_ocorrencias_pedido_id_foreign` (`pedido_id`),
  KEY `entregas_ocorrencias_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entregas_rotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entregas_rotas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `entregador_id` bigint unsigned DEFAULT NULL,
  `status` enum('planejada','em_rota','finalizada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planejada',
  `data_rota` date NOT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entregas_rotas_loja_id_foreign` (`loja_id`),
  KEY `entregas_rotas_entregador_id_foreign` (`entregador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `entregas_rotas_pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entregas_rotas_pedidos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rota_id` bigint unsigned NOT NULL,
  `pedido_id` bigint unsigned NOT NULL,
  `ordem` int NOT NULL DEFAULT '0',
  `status` enum('pendente','entregue','nao_entregue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `entregue_at` datetime DEFAULT NULL,
  `comprovante` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entregas_rotas_pedidos_rota_id_pedido_id_unique` (`rota_id`,`pedido_id`),
  KEY `entregas_rotas_pedidos_pedido_id_foreign` (`pedido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uf` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estados_uf_unique` (`uf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_inventario_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_inventario_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventario_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade_sistema` decimal(12,3) NOT NULL DEFAULT '0.000',
  `quantidade_contada` decimal(12,3) DEFAULT NULL,
  `divergencia` decimal(12,3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_inventario_itens_inventario_id_foreign` (`inventario_id`),
  KEY `estoque_inventario_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_inventarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_inventarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `status` enum('aberto','em_contagem','finalizado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `finalizado_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_inventarios_loja_id_foreign` (`loja_id`),
  KEY `estoque_inventarios_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_locais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_locais` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('deposito','loja','corredor','gondola','camara_fria','outro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'loja',
  `corredor` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prateleira` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `estoque_locais_loja_id_foreign` (`loja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_lotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_lotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `numero_lote` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `quantidade_atual` decimal(12,3) NOT NULL DEFAULT '0.000',
  `custo_unitario` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_lotes_loja_id_foreign` (`loja_id`),
  KEY `estoque_lotes_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_movimentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_movimentacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `lote_id` bigint unsigned DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `origem_tipo` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origem_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('entrada_compra','saida_venda_pdv','saida_venda_online','perda','avaria','vencimento','ajuste','transferencia_entrada','transferencia_saida','reserva','baixa_reserva') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  `custo_unitario` decimal(12,4) DEFAULT NULL,
  `justificativa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_movimentacoes_produto_variacao_id_created_at_index` (`produto_variacao_id`,`created_at`),
  KEY `estoque_movimentacoes_loja_id_foreign` (`loja_id`),
  KEY `estoque_movimentacoes_lote_id_foreign` (`lote_id`),
  KEY `estoque_movimentacoes_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_saldos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_saldos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade_atual` decimal(12,3) NOT NULL DEFAULT '0.000',
  `quantidade_reservada` decimal(12,3) NOT NULL DEFAULT '0.000',
  `estoque_minimo` decimal(12,3) NOT NULL DEFAULT '0.000',
  `estoque_maximo` decimal(12,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estoque_saldos_loja_id_produto_variacao_id_unique` (`loja_id`,`produto_variacao_id`),
  KEY `estoque_saldos_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_transferencia_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_transferencia_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transferencia_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_transferencia_itens_transferencia_id_foreign` (`transferencia_id`),
  KEY `estoque_transferencia_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estoque_transferencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque_transferencias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_origem_id` bigint unsigned NOT NULL,
  `loja_destino_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `status` enum('rascunho','enviada','recebida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `recebida_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `estoque_transferencias_loja_origem_id_foreign` (`loja_origem_id`),
  KEY `estoque_transferencias_loja_destino_id_foreign` (`loja_destino_id`),
  KEY `estoque_transferencias_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financeiro_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financeiro_categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('receita','despesa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `financeiro_categorias_empresa_id_foreign` (`empresa_id`),
  KEY `financeiro_categorias_parent_id_foreign` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financeiro_centros_custo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financeiro_centros_custo` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `financeiro_centros_custo_empresa_id_foreign` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financeiro_conciliacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financeiro_conciliacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `movimento_bancario_id` bigint unsigned NOT NULL,
  `lancamento_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fc_unique` (`movimento_bancario_id`,`lancamento_id`),
  KEY `financeiro_conciliacoes_lancamento_id_foreign` (`lancamento_id`),
  KEY `financeiro_conciliacoes_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financeiro_contas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financeiro_contas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('caixa','banco','cartao','pix','outro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `banco` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conta` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saldo_inicial` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `financeiro_contas_empresa_id_foreign` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financeiro_lancamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financeiro_lancamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned DEFAULT NULL,
  `centro_custo_id` bigint unsigned DEFAULT NULL,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `conta_id` bigint unsigned DEFAULT NULL,
  `pedido_id` bigint unsigned DEFAULT NULL,
  `compra_pedido_id` bigint unsigned DEFAULT NULL,
  `pdv_venda_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('receita','despesa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `data_competencia` date NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('pendente','pago','parcial','cancelado','atrasado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `recorrente` tinyint(1) NOT NULL DEFAULT '0',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financeiro_lancamentos_empresa_id_foreign` (`empresa_id`),
  KEY `financeiro_lancamentos_loja_id_foreign` (`loja_id`),
  KEY `financeiro_lancamentos_centro_custo_id_foreign` (`centro_custo_id`),
  KEY `financeiro_lancamentos_categoria_id_foreign` (`categoria_id`),
  KEY `financeiro_lancamentos_conta_id_foreign` (`conta_id`),
  KEY `financeiro_lancamentos_pedido_id_foreign` (`pedido_id`),
  KEY `financeiro_lancamentos_compra_pedido_id_foreign` (`compra_pedido_id`),
  KEY `financeiro_lancamentos_pdv_venda_id_foreign` (`pdv_venda_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `financeiro_movimentos_bancarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financeiro_movimentos_bancarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conta_id` bigint unsigned NOT NULL,
  `lancamento_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('entrada','saida') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `data_movimento` date NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conciliado` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financeiro_movimentos_bancarios_conta_id_foreign` (`conta_id`),
  KEY `financeiro_movimentos_bancarios_lancamento_id_foreign` (`lancamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_cfop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_cfop` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('entrada','saida') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_cfop_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_documento_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_documento_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fiscal_documento_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  `valor_unitario` decimal(12,4) NOT NULL,
  `valor_total` decimal(12,2) NOT NULL,
  `ncm` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfop` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cst_icms_csosn` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aliquota_icms` decimal(7,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  KEY `fiscal_documento_itens_fiscal_documento_id_foreign` (`fiscal_documento_id`),
  KEY `fiscal_documento_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `fornecedor_id` bigint unsigned DEFAULT NULL,
  `tipo` enum('nfe_entrada','nfe_saida','nfce','cancelamento','devolucao') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serie` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chave_acesso` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('rascunho','autorizada','cancelada','rejeitada','inutilizada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `xml_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `emitida_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_documentos_chave_acesso_unique` (`chave_acesso`),
  KEY `fiscal_documentos_loja_id_foreign` (`loja_id`),
  KEY `fiscal_documentos_cliente_id_foreign` (`cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_ncm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_ncm` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_ncm_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_perfis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_perfis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cst_icms_csosn` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aliquota_icms` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `cst_pis` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aliquota_pis` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `cst_cofins` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aliquota_cofins` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `cfop_saida_padrao` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cfop_entrada_padrao` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_regimes_tributarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_regimes_tributarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_regimes_tributarios_nome_unique` (`nome`),
  UNIQUE KEY `fiscal_regimes_tributarios_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fiscal_regras_produto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_regras_produto` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned DEFAULT NULL,
  `fiscal_perfil_id` bigint unsigned NOT NULL,
  `uf_destino` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vigencia_inicio` date NOT NULL,
  `vigencia_fim` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fiscal_regras_produto_produto_variacao_id_foreign` (`produto_variacao_id`),
  KEY `fiscal_regras_produto_loja_id_foreign` (`loja_id`),
  KEY `fiscal_regras_produto_fiscal_perfil_id_foreign` (`fiscal_perfil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `formas_pagamento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formas_pagamento` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('dinheiro','pix','cartao_credito','cartao_debito','voucher','crediario','outro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `taxa_percentual` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `prazo_recebimento_dias` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `formas_pagamento_nome_unique` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `tipo_pessoa` enum('fisica','juridica') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'juridica',
  `razao_social` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj_cpf` varchar(18) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inscricao_estadual` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fornecedores_empresa_id_cnpj_cpf_unique` (`empresa_id`,`cnpj_cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fornecedores_contatos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fornecedores_contatos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fornecedor_id` bigint unsigned NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fornecedores_contatos_fornecedor_id_foreign` (`fornecedor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` longtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lojas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lojas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_interno` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnpj` varchar(18) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo` enum('matriz','filial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'matriz',
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade_id` bigint unsigned NOT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status_operacional` enum('aberta','fechada','manutencao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberta',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lojas_empresa_id_codigo_interno_unique` (`empresa_id`,`codigo_interno`),
  KEY `lojas_cidade_id_foreign` (`cidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lojas_configuracoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lojas_configuracoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `aceita_retirada` tinyint(1) NOT NULL DEFAULT '1',
  `aceita_entrega` tinyint(1) NOT NULL DEFAULT '1',
  `pedido_minimo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `taxa_entrega_base` decimal(10,2) NOT NULL DEFAULT '0.00',
  `raio_entrega_km` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tempo_preparo_padrao_min` int NOT NULL DEFAULT '60',
  `permite_substituicao` tinyint(1) NOT NULL DEFAULT '1',
  `vender_sem_estoque` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lojas_configuracoes_loja_id_unique` (`loja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `marcas_nome_unique` (`nome`),
  UNIQUE KEY `marcas_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marcas_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas_categorias` (
  `marca_id` bigint unsigned NOT NULL,
  `categoria_id` bigint unsigned NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`marca_id`,`categoria_id`),
  KEY `marcas_categorias_categoria_id_foreign` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notificacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `canal` enum('sistema','email','whatsapp','sms') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sistema',
  `titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensagem` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pendente','enviada','lida','erro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `enviada_at` datetime DEFAULT NULL,
  `lida_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notificacoes_usuario_id_foreign` (`usuario_id`),
  KEY `notificacoes_cliente_id_foreign` (`cliente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ofertas_campanhas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ofertas_campanhas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ofertas_campanhas_empresa_id_foreign` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ofertas_campanhas_lojas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ofertas_campanhas_lojas` (
  `campanha_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`campanha_id`,`loja_id`),
  KEY `ofertas_campanhas_lojas_loja_id_foreign` (`loja_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ofertas_produtos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ofertas_produtos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campanha_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `preco_de` decimal(12,2) DEFAULT NULL,
  `preco_por` decimal(12,2) NOT NULL,
  `preco_clube` decimal(12,2) DEFAULT NULL,
  `estoque_promocional` decimal(12,3) DEFAULT NULL,
  `limite_por_cliente` decimal(12,3) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `op_unique` (`campanha_id`,`loja_id`,`produto_variacao_id`),
  KEY `ofertas_produtos_loja_id_foreign` (`loja_id`),
  KEY `ofertas_produtos_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_caixa_movimentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_caixa_movimentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caixa_abertura_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `tipo` enum('sangria','suprimento','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdv_caixa_movimentos_caixa_abertura_id_foreign` (`caixa_abertura_id`),
  KEY `pdv_caixa_movimentos_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_caixas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_caixas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `nome` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pdv_caixas_loja_id_numero_unique` (`loja_id`,`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_caixas_aberturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_caixas_aberturas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caixa_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `status` enum('aberto','fechado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `valor_abertura` decimal(12,2) NOT NULL DEFAULT '0.00',
  `valor_fechamento_informado` decimal(12,2) DEFAULT NULL,
  `valor_fechamento_sistema` decimal(12,2) DEFAULT NULL,
  `diferenca` decimal(12,2) DEFAULT NULL,
  `aberto_at` datetime NOT NULL,
  `fechado_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdv_caixas_aberturas_caixa_id_foreign` (`caixa_id`),
  KEY `pdv_caixas_aberturas_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_devolucao_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_devolucao_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `devolucao_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  `valor_unitario` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pdv_devolucao_itens_devolucao_id_foreign` (`devolucao_id`),
  KEY `pdv_devolucao_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_devolucoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_devolucoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdv_devolucoes_venda_id_foreign` (`venda_id`),
  KEY `pdv_devolucoes_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_venda_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_venda_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `quantidade` decimal(12,3) NOT NULL,
  `preco_unitario` decimal(12,2) NOT NULL,
  `desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_item` decimal(12,2) NOT NULL,
  `cancelado` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pdv_venda_itens_venda_id_foreign` (`venda_id`),
  KEY `pdv_venda_itens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_venda_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_venda_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venda_id` bigint unsigned NOT NULL,
  `forma_pagamento_id` bigint unsigned NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `autorizacao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelas` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `pdv_venda_pagamentos_venda_id_foreign` (`venda_id`),
  KEY `pdv_venda_pagamentos_forma_pagamento_id_foreign` (`forma_pagamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pdv_vendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdv_vendas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `caixa_abertura_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned DEFAULT NULL,
  `fiscal_documento_id` bigint unsigned DEFAULT NULL,
  `status` enum('aberta','concluida','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberta',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `acrescimo` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `finalizada_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pdv_vendas_loja_id_foreign` (`loja_id`),
  KEY `pdv_vendas_caixa_abertura_id_foreign` (`caixa_abertura_id`),
  KEY `pdv_vendas_usuario_id_foreign` (`usuario_id`),
  KEY `pdv_vendas_cliente_id_foreign` (`cliente_id`),
  KEY `pdv_vendas_fiscal_documento_id_foreign` (`fiscal_documento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `endereco_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem` enum('site','app','whatsapp','balcao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'site',
  `tipo_entrega` enum('retirada','entrega') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entrega',
  `status` enum('recebido','confirmado','em_separacao','aguardando_cliente','pronto_retirada','pronto_entrega','saiu_entrega','entregue','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recebido',
  `forma_pagamento_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `desconto` decimal(12,2) NOT NULL DEFAULT '0.00',
  `taxa_entrega` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `observacao_cliente` mediumtext COLLATE utf8mb4_unicode_ci,
  `slot_inicio` datetime DEFAULT NULL,
  `slot_fim` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedidos_codigo_unique` (`codigo`),
  KEY `pedidos_loja_id_foreign` (`loja_id`),
  KEY `pedidos_cliente_id_foreign` (`cliente_id`),
  KEY `pedidos_endereco_id_foreign` (`endereco_id`),
  KEY `pedidos_forma_pagamento_id_foreign` (`forma_pagamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pedidos_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `oferta_produto_id` bigint unsigned DEFAULT NULL,
  `quantidade_solicitada` decimal(12,3) NOT NULL,
  `quantidade_atendida` decimal(12,3) DEFAULT NULL,
  `preco_unitario` decimal(12,2) NOT NULL,
  `total_item` decimal(12,2) NOT NULL,
  `status_item` enum('pendente','separado','faltou','substituido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `substituto_produto_variacao_id` bigint unsigned DEFAULT NULL,
  `observacao_cliente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacao_separador` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_itens_pedido_id_foreign` (`pedido_id`),
  KEY `pedidos_itens_produto_variacao_id_foreign` (`produto_variacao_id`),
  KEY `pedidos_itens_oferta_produto_id_foreign` (`oferta_produto_id`),
  KEY `pedidos_itens_substituto_produto_variacao_id_foreign` (`substituto_produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pedidos_pagamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_pagamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `forma_pagamento_id` bigint unsigned NOT NULL,
  `status` enum('pendente','aprovado','recusado','estornado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `valor` decimal(12,2) NOT NULL,
  `gateway` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transacao_id` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pago_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_pagamentos_pedido_id_foreign` (`pedido_id`),
  KEY `pedidos_pagamentos_forma_pagamento_id_foreign` (`forma_pagamento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pedidos_separacao_itens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_separacao_itens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `separacao_id` bigint unsigned NOT NULL,
  `pedido_item_id` bigint unsigned NOT NULL,
  `quantidade_separada` decimal(12,3) DEFAULT NULL,
  `status` enum('pendente','separado','faltou','substituido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_separacao_itens_separacao_id_foreign` (`separacao_id`),
  KEY `pedidos_separacao_itens_pedido_item_id_foreign` (`pedido_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pedidos_separacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_separacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `separador_id` bigint unsigned DEFAULT NULL,
  `status` enum('aguardando','em_andamento','pausada','finalizada','cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aguardando',
  `inicio_at` datetime DEFAULT NULL,
  `fim_at` datetime DEFAULT NULL,
  `observacao` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedidos_separacoes_pedido_id_unique` (`pedido_id`),
  KEY `pedidos_separacoes_separador_id_foreign` (`separador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pedidos_status_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pedidos_status_historico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `status_anterior` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_novo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedidos_status_historico_pedido_id_foreign` (`pedido_id`),
  KEY `pedidos_status_historico_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `precos_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos_historico` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `preco_anterior` decimal(12,2) NOT NULL,
  `preco_novo` decimal(12,2) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `precos_historico_loja_id_foreign` (`loja_id`),
  KEY `precos_historico_produto_variacao_id_foreign` (`produto_variacao_id`),
  KEY `precos_historico_usuario_id_foreign` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `precos_produtos_lojas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `precos_produtos_lojas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `loja_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `preco_custo` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `custo_medio` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `margem_percentual` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `preco_venda` decimal(12,2) NOT NULL DEFAULT '0.00',
  `preco_atacado` decimal(12,2) DEFAULT NULL,
  `quantidade_min_atacado` decimal(10,3) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `precos_produtos_lojas_loja_id_produto_variacao_id_unique` (`loja_id`,`produto_variacao_id`),
  KEY `precos_produtos_lojas_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto_apresentacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_apresentacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `embalagem_id` bigint unsigned DEFAULT NULL,
  `unidade_medida_id` bigint unsigned NOT NULL,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('estoque','venda','compra','fiscal','multipla') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'multipla',
  `conteudo_quantidade` decimal(12,3) NOT NULL DEFAULT '1.000',
  `fator_conversao_estoque` decimal(14,6) NOT NULL DEFAULT '1.000000',
  `permite_venda` tinyint(1) NOT NULL DEFAULT '1',
  `permite_compra` tinyint(1) NOT NULL DEFAULT '1',
  `controla_estoque` tinyint(1) NOT NULL DEFAULT '1',
  `principal_venda` tinyint(1) NOT NULL DEFAULT '0',
  `principal_compra` tinyint(1) NOT NULL DEFAULT '0',
  `principal_estoque` tinyint(1) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produto_apresentacoes_produto_variacao_id_index` (`produto_variacao_id`),
  KEY `produto_apresentacoes_embalagem_id_index` (`embalagem_id`),
  KEY `produto_apresentacoes_unidade_medida_id_foreign` (`unidade_medida_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto_codigos_barras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_codigos_barras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `produto_apresentacao_id` bigint unsigned DEFAULT NULL,
  `codigo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('ean13','ean8','dun14','interno','balanca') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ean13',
  `descricao` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `produto_codigos_barras_codigo_unique` (`codigo`),
  KEY `produto_codigos_barras_produto_apresentacao_id_index` (`produto_apresentacao_id`),
  KEY `produto_codigos_barras_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto_fornecedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_fornecedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fornecedor_id` bigint unsigned NOT NULL,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `produto_apresentacao_compra_id` bigint unsigned DEFAULT NULL,
  `codigo_fornecedor` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custo_ultima_compra` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `quantidade_minima_compra` decimal(12,3) DEFAULT NULL,
  `prazo_entrega_dias` int NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `produto_fornecedores_fornecedor_id_produto_variacao_id_unique` (`fornecedor_id`,`produto_variacao_id`),
  KEY `produto_fornecedores_produto_apresentacao_compra_id_index` (`produto_apresentacao_compra_id`),
  KEY `produto_fornecedores_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto_imagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_imagens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordem` int NOT NULL DEFAULT '0',
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `produto_imagens_produto_variacao_id_foreign` (`produto_variacao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto_variacao_atributos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_variacao_atributos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_variacao_id` bigint unsigned NOT NULL,
  `atributo_id` bigint unsigned NOT NULL,
  `valor_texto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_numero` decimal(14,4) DEFAULT NULL,
  `valor_booleano` tinyint(1) DEFAULT NULL,
  `valor_data` date DEFAULT NULL,
  `valor_json` json DEFAULT NULL,
  `unidade_medida_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pva_unique` (`produto_variacao_id`,`atributo_id`),
  KEY `produto_variacao_atributos_atributo_id_valor_texto_index` (`atributo_id`,`valor_texto`),
  KEY `produto_variacao_atributos_atributo_id_valor_numero_index` (`atributo_id`,`valor_numero`),
  KEY `produto_variacao_atributos_unidade_medida_id_foreign` (`unidade_medida_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produto_variacoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produto_variacoes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `produto_base_id` bigint unsigned NOT NULL,
  `marca_id` bigint unsigned DEFAULT NULL,
  `unidade_medida_id` bigint unsigned NOT NULL,
  `embalagem_id` bigint unsigned DEFAULT NULL,
  `nome_completo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conteudo_quantidade` decimal(10,3) NOT NULL DEFAULT '1.000',
  `pesavel` tinyint(1) NOT NULL DEFAULT '0',
  `fracionado` tinyint(1) NOT NULL DEFAULT '0',
  `quantidade_minima_venda` decimal(10,3) NOT NULL DEFAULT '1.000',
  `passo_venda` decimal(10,3) NOT NULL DEFAULT '1.000',
  `ncm` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cest` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origem_mercadoria` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `foto_capa_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `produto_variacoes_slug_unique` (`slug`),
  KEY `produto_variacoes_produto_base_id_foreign` (`produto_base_id`),
  KEY `produto_variacoes_marca_id_foreign` (`marca_id`),
  KEY `produto_variacoes_unidade_medida_id_foreign` (`unidade_medida_id`),
  KEY `produto_variacoes_embalagem_id_foreign` (`embalagem_id`),
  FULLTEXT KEY `produto_variacoes_nome_completo_fulltext` (`nome_completo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produtos_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos_base` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned NOT NULL,
  `nome` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` mediumtext COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `produtos_base_slug_unique` (`slug`),
  KEY `produtos_base_categoria_id_foreign` (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` mediumtext COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `temas_sistema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temas_sistema` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '0',
  `cor_primaria` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1d4ed8',
  `cor_secundaria` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0f766e',
  `cor_header` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#ffffff',
  `cor_sidebar` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#132033',
  `cor_botao_primario` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#1d4ed8',
  `cor_sucesso` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#15803d',
  `cor_alerta` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#b45309',
  `cor_erro` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#b91c1c',
  `cor_fundo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#f4f6f8',
  `cor_texto` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#172033',
  `layout_sidebar_modo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'hover',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inicio_em` datetime DEFAULT NULL,
  `fim_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `temas_sistema_nome_unique` (`nome`),
  KEY `temas_sistema_ativo_inicio_em_fim_em_index` (`ativo`,`inicio_em`,`fim_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `unidades_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades_medida` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sigla` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permite_decimal` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidades_medida_sigla_unique` (`sigla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `whatsapp` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ultimo_login_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_whatsapp_unique` (`whatsapp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuarios_lojas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios_lojas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned NOT NULL,
  `papel_id` bigint unsigned NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_lojas_usuario_id_loja_id_papel_id_unique` (`usuario_id`,`loja_id`,`papel_id`),
  KEY `usuarios_lojas_loja_id_foreign` (`loja_id`),
  KEY `usuarios_lojas_papel_id_foreign` (`papel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuarios_permissoes_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios_permissoes_extras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned NOT NULL,
  `loja_id` bigint unsigned DEFAULT NULL,
  `permissao_id` bigint unsigned NOT NULL,
  `efeito` enum('permitir','negar') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `upe_unique` (`usuario_id`,`loja_id`,`permissao_id`),
  KEY `usuarios_permissoes_extras_loja_id_foreign` (`loja_id`),
  KEY `usuarios_permissoes_extras_permissao_id_foreign` (`permissao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `vw_ofertas_ativas`;
/*!50001 DROP VIEW IF EXISTS `vw_ofertas_ativas`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_ofertas_ativas` AS SELECT 
 1 AS `oferta_produto_id`,
 1 AS `loja_id`,
 1 AS `loja`,
 1 AS `produto_variacao_id`,
 1 AS `nome_completo`,
 1 AS `produto_base`,
 1 AS `categoria`,
 1 AS `preco_de`,
 1 AS `preco_por`,
 1 AS `preco_clube`,
 1 AS `campanha`,
 1 AS `data_inicio`,
 1 AS `data_fim`,
 1 AS `foto_capa_url`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `vw_produtos_vitrine`;
/*!50001 DROP VIEW IF EXISTS `vw_produtos_vitrine`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vw_produtos_vitrine` AS SELECT 
 1 AS `produto_variacao_id`,
 1 AS `produto_base_id`,
 1 AS `produto_base`,
 1 AS `nome_completo`,
 1 AS `marca`,
 1 AS `categoria`,
 1 AS `loja_id`,
 1 AS `loja`,
 1 AS `preco_venda`,
 1 AS `quantidade_atual`,
 1 AS `quantidade_reservada`,
 1 AS `quantidade_disponivel`,
 1 AS `foto_capa_url`,
 1 AS `pesavel`,
 1 AS `fracionado`*/;
SET character_set_client = @saved_cs_client;
/*!50001 DROP VIEW IF EXISTS `vw_ofertas_ativas`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_ofertas_ativas` AS select `op`.`id` AS `oferta_produto_id`,`op`.`loja_id` AS `loja_id`,`l`.`nome` AS `loja`,`op`.`produto_variacao_id` AS `produto_variacao_id`,`pv`.`nome_completo` AS `nome_completo`,`pb`.`nome` AS `produto_base`,`c`.`nome` AS `categoria`,`op`.`preco_de` AS `preco_de`,`op`.`preco_por` AS `preco_por`,`op`.`preco_clube` AS `preco_clube`,`oc`.`nome` AS `campanha`,`oc`.`data_inicio` AS `data_inicio`,`oc`.`data_fim` AS `data_fim`,`pv`.`foto_capa_url` AS `foto_capa_url` from (((((`ofertas_produtos` `op` join `ofertas_campanhas` `oc` on((`oc`.`id` = `op`.`campanha_id`))) join `lojas` `l` on((`l`.`id` = `op`.`loja_id`))) join `produto_variacoes` `pv` on((`pv`.`id` = `op`.`produto_variacao_id`))) join `produtos_base` `pb` on((`pb`.`id` = `pv`.`produto_base_id`))) join `categorias` `c` on((`c`.`id` = `pb`.`categoria_id`))) where ((`op`.`ativo` = 1) and (`oc`.`ativo` = 1) and (now() between `oc`.`data_inicio` and `oc`.`data_fim`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!50001 DROP VIEW IF EXISTS `vw_produtos_vitrine`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vw_produtos_vitrine` AS select `pv`.`id` AS `produto_variacao_id`,`pb`.`id` AS `produto_base_id`,`pb`.`nome` AS `produto_base`,`pv`.`nome_completo` AS `nome_completo`,`m`.`nome` AS `marca`,`c`.`nome` AS `categoria`,`l`.`id` AS `loja_id`,`l`.`nome` AS `loja`,`ppl`.`preco_venda` AS `preco_venda`,`es`.`quantidade_atual` AS `quantidade_atual`,`es`.`quantidade_reservada` AS `quantidade_reservada`,(`es`.`quantidade_atual` - `es`.`quantidade_reservada`) AS `quantidade_disponivel`,`pv`.`foto_capa_url` AS `foto_capa_url`,`pv`.`pesavel` AS `pesavel`,`pv`.`fracionado` AS `fracionado` from ((((((`produto_variacoes` `pv` join `produtos_base` `pb` on((`pb`.`id` = `pv`.`produto_base_id`))) join `categorias` `c` on((`c`.`id` = `pb`.`categoria_id`))) left join `marcas` `m` on((`m`.`id` = `pv`.`marca_id`))) join `precos_produtos_lojas` `ppl` on(((`ppl`.`produto_variacao_id` = `pv`.`id`) and (`ppl`.`ativo` = 1)))) join `lojas` `l` on(((`l`.`id` = `ppl`.`loja_id`) and (`l`.`ativo` = 1)))) left join `estoque_saldos` `es` on(((`es`.`produto_variacao_id` = `pv`.`id`) and (`es`.`loja_id` = `l`.`id`)))) where ((`pv`.`ativo` = 1) and (`pb`.`ativo` = 1) and (`c`.`ativo` = 1)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_06_20_152349_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_06_20_153000_add_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_06_20_153001_create_erp_geo_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_06_20_153002_create_erp_company_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_06_20_153003_create_erp_user_scope_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_06_20_153004_create_erp_customer_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_06_20_153005_create_erp_catalog_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_06_20_153006_create_erp_fiscal_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_06_20_153007_create_erp_pricing_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_06_20_153008_create_erp_inventory_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_06_20_153009_create_erp_supplier_purchase_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_06_20_153010_create_erp_pdv_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_06_20_153011_create_erp_order_delivery_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_06_20_153012_create_erp_financial_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_06_20_153013_create_erp_crm_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_06_20_153014_create_erp_system_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_06_20_153015_create_erp_views',1);
