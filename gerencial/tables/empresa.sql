-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 05/05/2026 às 00:32
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
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id` int NOT NULL,
  `Nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Documento` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Telefone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Plano` int DEFAULT NULL,
  `Status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'PENDENTE',
  `DataCadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `ValidadePlano` date DEFAULT NULL,
  `MetaMensal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `MetaDiaria` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id`, `Nome`, `Documento`, `Telefone`, `Email`, `Plano`, `Status`, `DataCadastro`, `ValidadePlano`, `MetaMensal`, `MetaDiaria`) VALUES
(10, 'AUTODOC BRASIL LTDA', '62.424.394/0001-62', '', 'autodocva@gmail.com', 3, 'ATIVA', '2026-03-03 16:54:45', '2026-04-02', 0.00, 0.00),
(11, 'Escritório e Despachante de Trânsito Allgayer', '019.850.840-90', '', 'despachanteallgayer@gmail.com', 3, 'ATIVA', '2026-03-03 22:37:55', '2100-01-01', 48000.00, 2400.00),
(14, 'Matheus Portaluppi', '043.093.000-30', '(51) 99543-2617', 'matheusportaluppi09@gmail.com', 1, 'PENDENTE', '2026-03-18 10:31:43', NULL, 0.00, 0.00),
(16, 'JET DESPACHANTE', '41.444.657/0001-25', '(51) 98636-4373', 'jetdespachanters@gmail.com', 2, 'ATIVA', '2026-04-20 12:34:15', '2026-05-20', 0.00, 0.00);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
