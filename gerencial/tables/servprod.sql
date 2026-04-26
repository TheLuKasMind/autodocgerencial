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
-- Estrutura para tabela `servprod`
--

CREATE TABLE `servprod` (
  `id` int(11) NOT NULL,
  `Nome` varchar(400) NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Inativo` int(11) NOT NULL,
  `Descricao` varchar(350) NOT NULL,
  `Unidade` varchar(20) NOT NULL,
  `ValorCusto` decimal(10,2) NOT NULL,
  `ValorVenda` decimal(10,2) NOT NULL,
  `idEmpresa` int(11) NOT NULL,
  `Codigo` int(11) NOT NULL,
  `DataAlt` datetime DEFAULT NULL,
  `SolicitaVeiculo` int(11) NOT NULL,
  `MetaMensal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `servprod`
--

INSERT INTO `servprod` (`id`, `Nome`, `Tipo`, `Inativo`, `Descricao`, `Unidade`, `ValorCusto`, `ValorVenda`, `idEmpresa`, `Codigo`, `DataAlt`, `SolicitaVeiculo`, `MetaMensal`) VALUES
(1, 'IPVA', 1, 1, 'IPVA REVENDA', '1', 700.00, 1200.00, 1, 1, '2026-02-20 17:24:58', 1, 0),
(22, 'Licenciamento Novo', 1, 0, '', '1', 35.40, 120.00, 1, 1, '2026-02-27 11:14:25', 0, 30),
(23, 'Taxa Detran', 2, 1, '', '3', 129.00, 2122.44, 1, 2, '2026-02-14 20:46:03', 0, 0),
(24, 'Comunicação de Venda', 1, 0, '', '2', 40.00, 120.00, 1, 3, '2026-02-16 17:32:00', 1, 2),
(26, 'Transferência', 1, 0, 'Transferência de veículo', '1', 1400.00, 1900.00, 1, 4, '2026-02-15 13:02:37', 0, 0),
(27, 'CONSULTORIA TRANSFERÊNCIA', 1, 0, '', '3', 0.00, 100.00, 1, 5, '2026-02-18 18:16:36', 0, 0),
(28, 'SERVIÇOS GERAIS', 1, 0, 'Serviços gerais... . . . ', '3', 450.00, 129.99, 1, 6, '2026-02-16 17:36:19', 0, 15),
(29, 'EMPLACAMENTO', 1, 0, '', '1', 90.00, 180.00, 1, 7, '2026-02-24 08:17:23', 0, 2),
(30, 'FILIAL DOIS', 1, 0, 'aa', '1', 120.00, 150.00, 2, 1, '2026-03-01 16:26:53', 1, 13);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `servprod`
--
ALTER TABLE `servprod`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `servprod`
--
ALTER TABLE `servprod`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
