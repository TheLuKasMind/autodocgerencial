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
-- Estrutura para tabela `movimentocc`
--

CREATE TABLE `movimentocc` (
  `idEmpresa` int(11) NOT NULL,
  `TipoDespesa` int(11) NOT NULL,
  `Descricao` varchar(300) NOT NULL,
  `Valor` decimal(11,2) NOT NULL,
  `Data` datetime NOT NULL,
  `idForcli` int(11) NOT NULL,
  `Controle` int(11) NOT NULL,
  `idServProd` int(11) NOT NULL,
  `DataPgto` datetime NOT NULL,
  `ValorPgto` decimal(10,2) NOT NULL,
  `UserAlt` int(11) NOT NULL,
  `TipoMov` varchar(20) DEFAULT 'SAIDA',
  `CaixaGeral` tinyint(1) NOT NULL,
  `ControleOrigem` varchar(300) NOT NULL,
  `DataAlt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimentocc`
--

INSERT INTO `movimentocc` (`idEmpresa`, `TipoDespesa`, `Descricao`, `Valor`, `Data`, `idForcli`, `Controle`, `idServProd`, `DataPgto`, `ValorPgto`, `UserAlt`, `TipoMov`, `CaixaGeral`, `ControleOrigem`, `DataAlt`) VALUES
(1, 0, 'ENTRADA DE CAIXA', 122.00, '2026-02-18 16:14:42', 0, 1, 0, '2026-02-18 16:14:42', 0.00, 0, 'ENTRADA', 1, '', '0000-00-00 00:00:00'),
(1, 0, 'AJUSTE DE CAIXA', 20.00, '2026-02-18 16:19:30', 0, 2, 0, '2026-02-18 16:19:30', 0.00, 0, 'AJUSTE', 0, '', '0000-00-00 00:00:00'),
(1, 0, 'ENTRADA', 200.00, '2026-02-18 16:28:18', 0, 3, 0, '2026-02-18 16:28:18', 0.00, 0, 'ENTRADA', 1, '', '0000-00-00 00:00:00'),
(1, 0, 'SAÍDA IMPOSTO IR', 100.00, '2026-02-18 16:29:30', 0, 4, 0, '2026-02-18 16:29:30', 0.00, 0, 'SAIDA', 1, '', '0000-00-00 00:00:00'),
(1, 0, 'SAÍDA', 22.00, '2026-02-18 17:04:36', 0, 5, 0, '2026-02-18 17:04:36', 0.00, 0, 'SAIDA', 1, '', '0000-00-00 00:00:00'),
(1, 0, 'SAÍDA', 0.00, '2026-02-19 16:26:17', 0, 6, 0, '2026-02-19 16:26:17', 0.00, 0, 'ENTRADA', 1, '', '0000-00-00 00:00:00'),
(2, 0, '323', 3323.00, '2026-02-20 13:50:16', 0, 7, 0, '2026-02-20 13:50:16', 0.00, 0, 'ENTRADA', 1, '', '0000-00-00 00:00:00'),
(1, 0, 'ENTRADA DE CAIXA 2', 300.00, '2026-02-22 17:17:37', 0, 8, 0, '2026-02-22 17:17:37', 300.00, 3, 'ENTRADA', 1, '0', '2026-02-22 17:17:37'),
(1, 0, 'AA', 100.00, '2026-02-22 17:18:00', 0, 9, 0, '2026-02-22 17:18:00', 100.00, 3, 'SAIDA', 1, '0', '2026-02-22 17:18:00'),
(2, 0, 'AA', 100.00, '2026-02-22 19:32:06', 0, 10, 0, '2026-02-22 19:32:06', 100.00, 12, 'ENTRADA', 1, '0', '2026-02-22 19:32:06'),
(2, 1, 'VENDA PEDIDO', 100.00, '2026-02-22 00:00:00', 0, 16, 0, '2026-02-22 00:00:00', 100.00, 12, 'ENTRADA', 0, '128', '2026-02-22 20:08:28'),
(1, 1, 'VENDA PEDIDO', 100.00, '2026-02-22 00:00:00', 0, 17, 0, '2026-02-22 00:00:00', 100.00, 3, 'ENTRADA', 0, '129', '2026-02-22 20:08:40'),
(2, 1, 'VENDA PEDIDO', 100.00, '2026-02-22 20:59:44', 0, 18, 0, '2026-02-22 00:00:00', 100.00, 12, 'ENTRADA', 0, '130', '2026-02-22 20:59:44'),
(1, 1, 'VENDA PEDIDO', 249.99, '2026-02-23 17:07:27', 0, 53, 0, '2026-02-23 00:00:00', 249.99, 3, 'ENTRADA', 0, '132', '2026-02-23 17:07:27'),
(1, 1, 'VENDA PEDIDO', 100.00, '2026-02-23 17:07:41', 0, 54, 0, '2026-02-23 00:00:00', 100.00, 3, 'ENTRADA', 0, '133', '2026-02-23 17:07:41'),
(1, 18, 'ERVA MATE', 25.00, '2026-02-23 00:00:00', 0, 55, 0, '2026-02-23 00:00:00', 25.00, 3, 'SAIDA', 0, '0', '2026-02-23 17:08:26'),
(1, 18, 'ESTACIONAMENTO', 15.00, '2026-02-23 00:00:00', 0, 56, 0, '2026-02-23 00:00:00', 15.00, 3, 'SAIDA', 0, '0', '2026-02-23 17:08:47'),
(2, 1, 'VENDA PEDIDO', 800.00, '2026-02-24 15:25:17', 0, 57, 0, '2026-02-24 00:00:00', 800.00, 12, 'ENTRADA', 0, '134', '2026-02-24 15:25:17'),
(1, 1, 'VENDA PEDIDO', 2000.00, '2026-02-25 13:53:05', 0, 58, 0, '2026-02-25 00:00:00', 2000.00, 3, 'ENTRADA', 0, '135', '2026-02-25 13:53:05'),
(1, 0, 'TESTEE', 500.00, '2026-02-27 11:46:04', 0, 59, 0, '2026-02-27 11:46:04', 500.00, 3, 'ENTRADA', 1, '0', '2026-02-27 11:46:04'),
(1, 1, 'VENDA PEDIDO', 1900.00, '2026-02-27 11:46:16', 0, 60, 0, '2026-02-27 00:00:00', 1900.00, 3, 'ENTRADA', 0, '136', '2026-02-27 11:46:16'),
(1, 11, 'Cabelereira', 300.00, '2026-02-28 00:00:00', 0, 62, 0, '2026-02-28 00:00:00', 300.00, 3, 'ENTRADA', 0, '0', '2026-02-28 14:28:55'),
(1, 0, 'ENTRADA PIX', 25.00, '2026-02-28 14:29:14', 0, 63, 0, '2026-02-28 14:29:14', 25.00, 3, 'ENTRADA', 1, '0', '2026-02-28 14:29:14'),
(1, 1, 'VENDA PEDIDO', 309.99, '2026-02-28 18:23:10', 0, 66, 0, '2026-02-28 00:00:00', 309.99, 3, 'ENTRADA', 0, '140', '2026-02-28 18:23:10'),
(1, 18, 'GASTOS GERAIS', 10.00, '2026-02-28 00:00:00', 0, 67, 0, '2026-02-28 00:00:00', 10.00, 3, 'SAIDA', 0, '0', '2026-02-28 18:33:36'),
(1, 18, 'GASTOS GERAIS', 150.00, '2026-02-28 18:35:42', 0, 68, 0, '2026-02-28 18:35:42', 150.00, 3, 'SAIDA', 0, '0', '2026-02-28 18:35:42'),
(1, 1, 'VENDA PEDIDO', 120.00, '2026-02-28 19:42:51', 0, 69, 0, '2026-02-28 00:00:00', 120.00, 3, 'ENTRADA', 0, '141', '2026-02-28 19:42:51'),
(2, 1, 'VENDA PEDIDO', 150.00, '2026-02-28 19:45:01', 0, 70, 0, '2026-02-28 00:00:00', 150.00, 12, 'ENTRADA', 0, '142', '2026-02-28 19:45:01'),
(2, 1, 'VENDA PEDIDO', 150.00, '2026-02-28 19:45:13', 0, 71, 0, '2026-02-28 00:00:00', 150.00, 12, 'ENTRADA', 0, '143', '2026-02-28 19:45:13'),
(1, 1, 'VENDA PEDIDO', 240.00, '2026-02-28 20:24:50', 0, 79, 0, '2026-02-28 00:00:00', 240.00, 3, 'ENTRADA', 0, '147', '2026-02-28 20:24:50'),
(1, 1, 'VENDA PEDIDO', 180.00, '2026-02-28 20:33:33', 0, 82, 0, '2026-02-28 00:00:00', 180.00, 3, 'ENTRADA', 0, '151', '2026-02-28 20:33:33'),
(1, 1, 'VENDA PEDIDO', 180.00, '2026-02-28 21:12:37', 0, 83, 0, '2026-02-28 00:00:00', 180.00, 3, 'ENTRADA', 0, '152', '2026-02-28 21:12:37'),
(1, 1, 'VENDA PEDIDO', 129.99, '2026-03-01 23:58:38', 0, 98, 0, '2026-03-04 23:58:38', 129.99, 3, 'ENTRADA', 0, '163', '2026-03-01 23:58:38'),
(1, 1, 'VENDA PEDIDO', 2000.00, '2026-03-02 00:13:14', 0, 103, 0, '2026-03-02 00:13:14', 2000.00, 3, 'ENTRADA', 0, '164', '2026-03-02 00:13:14'),
(1, 1, 'VENDA PEDIDO', 120.00, '2026-03-02 15:47:43', 0, 105, 0, '2026-03-02 15:47:43', 120.00, 3, 'ENTRADA', 0, '165', '2026-03-02 15:47:43'),
(1, 18, 'GASTOS GERAIS', 10.00, '2026-03-02 15:51:52', 0, 106, 0, '2026-03-02 15:51:52', 10.00, 3, 'SAIDA', 0, '0', '2026-03-02 15:51:52'),
(1, 0, 'Internet', 100.00, '2026-03-02 16:44:33', 0, 107, 0, '2026-03-02 16:44:33', 100.00, 3, 'ENTRADA', 1, '0', '2026-03-02 16:44:33');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `movimentocc`
--
ALTER TABLE `movimentocc`
  ADD PRIMARY KEY (`Controle`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `movimentocc`
--
ALTER TABLE `movimentocc`
  MODIFY `Controle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
