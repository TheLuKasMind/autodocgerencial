-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/03/2026 às 20:58
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `geral2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `movimentoitem`
--

CREATE TABLE `movimentoitem` (
  `id` int(11) NOT NULL,
  `idEmpresa` int(11) NOT NULL,
  `ControleMovimento` varchar(100) NOT NULL,
  `ServProd` int(11) NOT NULL,
  `Descricao` varchar(300) NOT NULL,
  `Qtd` int(11) NOT NULL,
  `Valor` decimal(10,2) NOT NULL,
  `TotalItem` decimal(10,2) NOT NULL,
  `DataAlt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentoitem`
--

INSERT INTO `movimentoitem` (`id`, `idEmpresa`, `ControleMovimento`, `ServProd`, `Descricao`, `Qtd`, `Valor`, `TotalItem`, `DataAlt`) VALUES
(180, 1, '106', 24, 'Comunicação de Venda', 8, 100.00, 800.00, '2026-02-16 13:58:17'),
(183, 1, '108', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-16 15:25:56'),
(184, 1, '108', 22, 'Licenciamento Novo', 1, 120.00, 120.00, '2026-02-16 15:25:56'),
(185, 1, '108', 1, 'IPVA', 5, 1200.00, 6000.00, '2026-02-16 15:25:56'),
(186, 1, '108', 28, 'SERVIÇOS GERAIS', 3, 129.99, 389.97, '2026-02-16 15:25:56'),
(187, 1, '108', 27, 'CONSULTORIA TRANSFERÊNCIA', 2, 100.00, 200.00, '2026-02-16 15:25:56'),
(188, 1, '108', 24, 'Comunicação de Venda', 1, 120.00, 120.00, '2026-02-16 15:25:56'),
(189, 1, '108', 24, 'Comunicação de Venda', 6, 120.00, 720.00, '2026-02-16 15:25:56'),
(190, 1, '108', 1, 'IPVA', 7, 1200.00, 8400.00, '2026-02-16 15:25:56'),
(191, 1, '108', 24, 'Comunicação de Venda', 1, 120.00, 120.00, '2026-02-16 15:25:56'),
(192, 1, '108', 28, 'SERVIÇOS GERAIS', 1, 129.99, 129.99, '2026-02-16 15:25:56'),
(193, 1, '108', 28, 'SERVIÇOS GERAIS', 8, 129.99, 1039.92, '2026-02-16 15:25:56'),
(196, 1, '107', 28, 'SERVIÇOS GERAIS', 2, 129.99, 259.98, '2026-02-16 15:37:39'),
(197, 1, '107', 24, 'Comunicação de Venda', 1, 120.00, 120.00, '2026-02-16 15:37:39'),
(239, 1, '109', 22, 'Licenciamento Novo', 1, 120.00, 120.00, '2026-02-18 12:14:55'),
(240, 1, '109', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-18 12:14:55'),
(241, 1, '109', 28, 'SERVIÇOS GERAIS', 1, 129.99, 129.99, '2026-02-18 12:14:55'),
(242, 1, '109', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-18 12:14:55'),
(243, 1, '110', 26, 'Transferência', 1, 1900.00, 1900.00, '2026-02-18 15:52:18'),
(244, 1, '110', 22, 'Licenciamento Novo', 1, 120.00, 120.00, '2026-02-18 15:52:18'),
(245, 1, '110', 1, 'IPVA', 1, 1200.00, 1200.00, '2026-02-18 15:52:18'),
(246, 1, '110', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-18 15:52:18'),
(247, 1, '111', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-18 18:20:33'),
(248, 2, '112', 1, 'IPVA', 1, 1200.00, 1200.00, '2026-02-19 22:19:33'),
(249, 2, '112', 24, 'Comunicação de Venda', 1, 120.00, 120.00, '2026-02-19 22:19:33'),
(250, 2, '112', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-19 22:19:33'),
(251, 2, '112', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-19 22:19:33'),
(262, 2, '116', 30, 'FILIA DOIS', 2, 100.00, 200.00, '2026-02-20 14:26:48'),
(263, 2, '117', 30, 'FILIA DOIS', 4, 100.00, 400.00, '2026-02-20 17:13:23'),
(264, 2, '117', 30, 'FILIA DOIS', 3, 100.00, 300.00, '2026-02-20 17:13:23'),
(265, 2, '117', 30, 'FILIA DOIS', 2, 100.00, 200.00, '2026-02-20 17:13:23'),
(266, 1, '118', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-20 17:43:53'),
(267, 1, '118', 26, 'Transferência', 1, 1900.00, 1900.00, '2026-02-20 17:43:53'),
(285, 2, '128', 30, 'FILIA DOIS', 1, 100.00, 100.00, '2026-02-22 20:08:28'),
(286, 1, '129', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-22 20:08:40'),
(287, 2, '130', 30, 'FILIA DOIS', 1, 100.00, 100.00, '2026-02-22 20:59:44'),
(288, 2, '131', 30, 'FILIAL DOIS', 1, 150.00, 150.00, '2026-02-23 08:05:50'),
(289, 1, '132', 28, 'SERVIÇOS GERAIS', 1, 129.99, 129.99, '2026-02-23 17:07:27'),
(290, 1, '132', 22, 'Licenciamento Novo', 1, 120.00, 120.00, '2026-02-23 17:07:27'),
(291, 1, '133', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-23 17:07:41'),
(293, 1, '135', 26, 'Transferência', 1, 1900.00, 1900.00, '2026-02-25 13:53:05'),
(294, 1, '135', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-02-25 13:53:05'),
(295, 1, '119', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-25 13:53:35'),
(296, 1, '136', 26, 'Transferência', 1, 1900.00, 1900.00, '2026-02-27 11:46:16'),
(299, 2, '134', 30, 'FILIAL DOIS', 1, 800.00, 800.00, '2026-02-28 16:18:13'),
(300, 2, '134', 30, 'FILIAL DOIS', 1, 150.00, 150.00, '2026-02-28 16:18:13'),
(306, 1, '140', 28, 'SERVIÇOS GERAIS', 1, 129.99, 129.99, '2026-02-28 18:23:10'),
(307, 1, '140', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-28 18:23:10'),
(309, 1, '141', 24, 'Comunicação de Venda', 1, 120.00, 120.00, '2026-02-28 19:42:57'),
(312, 2, '143', 30, 'FILIAL DOIS', 1, 150.00, 150.00, '2026-02-28 20:10:39'),
(313, 2, '142', 30, 'FILIAL DOIS', 1, 150.00, 150.00, '2026-02-28 20:11:16'),
(319, 1, '147', 22, 'Licenciamento Novo', 2, 120.00, 240.00, '2026-02-28 20:24:50'),
(321, 1, '151', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-28 20:33:33'),
(322, 1, '152', 29, 'EMPLACAMENTO', 1, 180.00, 180.00, '2026-02-28 21:12:37'),
(350, 1, '163', 28, 'SERVIÇOS GERAIS', 1, 129.99, 129.99, '2026-03-01 23:58:38'),
(359, 1, '164', 26, 'Transferência', 1, 1900.00, 1900.00, '2026-03-02 00:13:14'),
(360, 1, '164', 27, 'CONSULTORIA TRANSFERÊNCIA', 1, 100.00, 100.00, '2026-03-02 00:13:14'),
(363, 1, '165', 22, 'Licenciamento Novo', 1, 120.00, 120.00, '2026-03-02 15:47:43');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `movimentoitem`
--
ALTER TABLE `movimentoitem`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `movimentoitem`
--
ALTER TABLE `movimentoitem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=364;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
