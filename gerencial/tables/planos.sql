-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 28/04/2026 às 09:12
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
-- Estrutura para tabela `planos`
--

CREATE TABLE `planos` (
  `id` int NOT NULL,
  `Nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Valor` decimal(10,2) DEFAULT NULL,
  `Periodo` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Descricao` text COLLATE utf8mb4_general_ci,
  `Status` tinyint DEFAULT '1',
  `DataCadastro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `planos`
--

INSERT INTO `planos` (`id`, `Nome`, `Valor`, `Periodo`, `Descricao`, `Status`, `DataCadastro`) VALUES
(1, 'Plano Mensal', 49.90, 'MENSAL', 'Acesso completo ao sistema com cobrança mensal.', 1, '2026-02-22 14:30:10'),
(2, 'Plano Trimestral', 135.90, 'TRIMESTRAL', 'Plano com melhor custo-benefício para 3 meses.', 1, '2026-02-22 14:30:10'),
(3, 'Plano Anual', 480.00, 'ANUAL', 'Maior economia com acesso por 12 meses.', 1, '2026-02-22 14:30:10');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
