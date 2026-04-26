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
-- Estrutura para tabela `tipodespesa`
--

CREATE TABLE `tipodespesa` (
  `idEmpresa` int(11) NOT NULL,
  `Categoria` int(11) NOT NULL,
  `Acao` varchar(11) NOT NULL,
  `Inativo` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `Descricao` varchar(200) NOT NULL,
  `ValorBase` decimal(10,2) NOT NULL,
  `Codigo` int(11) NOT NULL,
  `Nome` varchar(100) NOT NULL,
  `DataAlt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipodespesa`
--

INSERT INTO `tipodespesa` (`idEmpresa`, `Categoria`, `Acao`, `Inativo`, `id`, `Descricao`, `ValorBase`, `Codigo`, `Nome`, `DataAlt`) VALUES
(1, 1, '1', 0, 1, 'Aluguel', 100.00, 1, 'Aluguel', '2026-02-23 08:25:16'),
(1, 3, '-1', 0, 3, 'Internet', 100.00, 4, 'Internet', '2026-02-28 18:30:48'),
(1, 4, '1', 0, 11, 'Cabelereira', 300.00, 5, 'Cabelereira', '2026-02-13 15:42:22'),
(1, 3, '-1', 1, 14, 'Taxa do Taxadd', 299.89, 5, 'Taxa do Taxadd', '2026-02-14 15:55:09'),
(1, 3, '-1', 0, 15, 'Taxa do Lula', 21.50, 6, 'Taxa do Lula', '2026-02-28 18:31:38'),
(1, 1, '-1', 0, 16, 'Taxa do Bolsonarp', 0.90, 7, 'Taxa do Bolsonarp', '2026-02-28 18:30:42'),
(2, 1, '-1', 0, 17, 'Lucas Renan - Compumate', 0.00, 1, 'Lucas Renan - Compumate', '2026-02-19 21:58:21'),
(1, 1, '-1', 0, 18, 'GASTOS GERAIS', 10.00, 8, 'GASTOS GERAIS', '2026-02-23 17:08:11');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
