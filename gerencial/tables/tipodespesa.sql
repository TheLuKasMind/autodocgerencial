-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 28/04/2026 às 09:11
-- Versão do servidor: 8.0.45-36
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `luca5858_geral2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipodespesa`
--

CREATE TABLE `tipodespesa` (
  `idEmpresa` int NOT NULL,
  `Categoria` int NOT NULL,
  `Acao` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `Inativo` int NOT NULL,
  `id` int NOT NULL,
  `Descricao` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `ValorBase` decimal(10,2) NOT NULL,
  `Codigo` int NOT NULL,
  `Nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `DataAlt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipodespesa`
--

INSERT INTO `tipodespesa` (`idEmpresa`, `Categoria`, `Acao`, `Inativo`, `id`, `Descricao`, `ValorBase`, `Codigo`, `Nome`, `DataAlt`) VALUES
(11, 1, '-1', 0, 20, 'ALUGUEL', 1518.00, 1, 'ALUGUEL', '2026-03-03 23:21:32'),
(11, 1, '-1', 0, 22, 'INTERNET', 0.00, 2, 'INTERNET', '2026-03-09 22:16:27'),
(11, 1, '-1', 0, 23, 'LUZ', 0.00, 3, 'LUZ', '2026-03-09 22:16:33'),
(11, 1, '-1', 0, 24, 'SALÁRIO LUIZ', 0.00, 4, 'SALÁRIO LUIZ', '2026-03-09 22:16:39'),
(11, 1, '-1', 0, 25, 'SALÁRIO TON', 11000.00, 5, 'SALÁRIO TON', '2026-03-09 22:16:50'),
(11, 1, '-1', 0, 26, 'SALÁRIO THIAGO', 11000.00, 6, 'SALÁRIO THIAGO', '2026-03-09 22:16:59'),
(11, 2, '-1', 0, 27, 'GASOLINA', 0.00, 7, 'GASOLINA', '2026-03-09 22:17:48'),
(11, 1, '-1', 0, 28, 'CRDD', 0.00, 8, 'CRDD', '2026-03-09 22:20:24'),
(11, 2, '-1', 0, 29, 'MARKETING', 0.00, 9, 'MARKETING', '2026-03-09 22:20:42'),
(11, 1, '-1', 0, 30, 'ESCRITÓRIO', 0.00, 10, 'ESCRITÓRIO', '2026-03-09 22:20:54'),
(11, 2, '-1', 0, 31, 'MERCADO', 0.00, 11, 'MERCADO', '2026-03-09 22:21:00'),
(11, 2, '-1', 0, 32, 'GRÁFICA', 0.00, 12, 'GRÁFICA', '2026-03-09 22:21:11'),
(11, 1, '-1', 0, 33, 'ÁGUA', 0.00, 13, 'ÁGUA', '2026-03-09 22:22:17'),
(11, 1, '-1', 0, 34, 'LIMPEZA', 0.00, 14, 'LIMPEZA', '2026-03-09 22:22:31'),
(11, 2, '-1', 0, 35, 'TAXA CRVA', 0.00, 15, 'TAXA CRVA', '2026-04-01 14:26:15'),
(11, 2, '0', 0, 36, 'TAXA CARTÓRIO', 0.00, 16, 'TAXA CARTÓRIO', '2026-03-14 14:42:01'),
(11, 1, '-1', 0, 37, 'IPTU', 63.43, 17, 'IPTU', '2026-04-02 09:50:13'),
(11, 2, '-1', 0, 38, 'GRT', 0.00, 18, 'GRT', '2026-04-02 09:50:35'),
(11, 2, '-1', 0, 39, 'PERDA', 0.00, 19, 'PERDA', '2026-04-02 09:50:42'),
(11, 1, '-1', 0, 40, 'IMPOSTOS', 0.00, 20, 'IMPOSTOS', '2026-04-06 16:40:36'),
(11, 1, '-1', 0, 41, 'CARTAO', 0.00, 21, 'CARTAO', '2026-04-06 16:41:33'),
(11, 1, '-1', 0, 42, 'SISTEMA', 0.00, 22, 'SISTEMA', '2026-04-07 14:35:13'),
(11, 1, '-1', 0, 43, 'CORREIO', 0.00, 23, 'CORREIO', '2026-04-10 10:20:54'),
(11, 1, '-1', 0, 44, 'CONSERTO MOTO', 0.00, 24, 'CONSERTO MOTO', '2026-04-16 10:51:30'),
(16, 2, '-1', 0, 45, 'ABERTURAS DETRAN', 0.00, 1, 'ABERTURAS DETRAN', '2026-04-20 17:13:30'),
(16, 1, '-1', 0, 46, 'CONTA TELEFONE', 0.00, 2, 'CONTA TELEFONE', '2026-04-20 17:19:09'),
(16, 2, '-1', 0, 47, 'RECARGA SISTEMA DESP', 0.00, 3, 'RECARGA SISTEMA DESP', '2026-04-20 17:20:04'),
(16, 2, '-1', 0, 48, 'ALMOÇO TIA', 0.00, 4, 'ALMOÇO TIA', '2026-04-20 17:20:14'),
(16, 2, '-1', 0, 49, 'COMBUSTIVEL', 0.00, 5, 'COMBUSTIVEL', '2026-04-20 17:20:24'),
(16, 2, '-1', 0, 50, 'CORREIOS', 0.00, 6, 'CORREIOS', '2026-04-20 17:20:34'),
(16, 2, '-1', 0, 51, 'INSUMOS ESCRITÓRIO LIMPEZA/ALIMENTO', 0.00, 7, 'INSUMOS ESCRITÓRIO LIMPEZA/ALIMENTO', '2026-04-20 17:20:59'),
(16, 2, '-1', 0, 52, 'UNIFORMES', 0.00, 8, 'UNIFORMES', '2026-04-20 17:21:46'),
(16, 2, '-1', 0, 53, 'MANUTENÇÃO PC', 0.00, 9, 'MANUTENÇÃO PC', '2026-04-20 17:22:10'),
(16, 2, '-1', 0, 54, 'ESTRUTURA ESCRITÓRIO', 0.00, 10, 'ESTRUTURA ESCRITÓRIO', '2026-04-20 17:22:44'),
(16, 2, '-1', 0, 55, 'ALVARA', 0.00, 11, 'ALVARA', '2026-04-20 17:22:56'),
(16, 1, '-1', 0, 56, 'LUZ', 0.00, 12, 'LUZ', '2026-04-20 17:23:03'),
(16, 2, '-1', 0, 57, 'VISTORIAS', 0.00, 13, 'VISTORIAS', '2026-04-20 17:35:40'),
(16, 2, '-1', 0, 58, 'SIAC', 0.00, 14, 'SIAC', '2026-04-21 14:58:20'),
(16, 2, '-1', 0, 59, 'DIGITAL', 0.00, 15, 'DIGITAL', '2026-04-21 15:01:44'),
(16, 2, '-1', 0, 60, 'GRT', 0.00, 16, 'GRT', '2026-04-21 15:02:23'),
(16, 2, '-1', 0, 61, 'SALARIO ANDRIA', 0.00, 17, 'SALARIO ANDRIA', '2026-04-21 15:04:38'),
(16, 2, '-1', 0, 62, 'SALARIO MICHEL', 0.00, 18, 'SALARIO MICHEL', '2026-04-21 15:04:50'),
(16, 2, '-1', 0, 63, 'SALARIO MAURO', 0.00, 19, 'SALARIO MAURO', '2026-04-21 15:05:01'),
(16, 2, '-1', 0, 64, 'PRÓ-LABORE ', 0.00, 20, 'PRÓ-LABORE ', '2026-04-21 15:05:29'),
(16, 2, '-1', 0, 65, 'CRDD', 0.00, 21, 'CRDD', '2026-04-21 15:49:30'),
(11, 1, '-1', 0, 66, 'Manutenção Motos', 0.00, 25, 'Manutenção Motos', '2026-04-23 08:51:40');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tipodespesa`
--
ALTER TABLE `tipodespesa`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tipodespesa`
--
ALTER TABLE `tipodespesa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
