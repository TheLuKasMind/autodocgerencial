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
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `Nome` varchar(150) DEFAULT NULL,
  `Documento` varchar(30) DEFAULT NULL,
  `Telefone` varchar(30) DEFAULT NULL,
  `Email` varchar(150) DEFAULT NULL,
  `Plano` int(20) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'PENDENTE',
  `DataCadastro` datetime DEFAULT current_timestamp(),
  `ValidadePlano` date DEFAULT NULL,
  `MetaMensal` decimal(10,2) NOT NULL,
  `MetaDiaria` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id`, `Nome`, `Documento`, `Telefone`, `Email`, `Plano`, `Status`, `DataCadastro`, `ValidadePlano`, `MetaMensal`, `MetaDiaria`) VALUES
(1, 'COMPUMATE SOFTWARES CORPORATIVOS', '92.343.375/0001-72', '513741 3900', 'compumate@compumate.com.br', 1, 'ATIVA', '2026-02-19 21:32:27', '2026-04-22', 90000.00, 5000.00),
(2, 'JET CONSULTORIA EM TI', '34.305.025/0001-15', '37413900', 'jettt@gmail.com', 1, 'ATIVA', '2026-02-19 21:34:50', '2026-03-22', 12000.00, 200.00),
(3, 'DESPACHANTE ALLGAYER', '54.857.617/0001-57', '(51) 99822-4128', 'allgayer@gmail.com', 2, 'INATIVA', '2026-02-20 14:57:03', '2026-03-22', 0.00, 0.00),
(4, 'DESPACHANTE ALLGAYER2', '54.857.617/0001-52', '(51) 99822-4122', 'lucas2@email.com', 2, 'ATIVA', '2026-02-22 15:35:02', '2026-03-24', 0.00, 0.00),
(5, 'DESPACHANTE ALLGAYER2', '54.857.617/0001-52', '(51) 99822-4122', 'lucas@email.com', 3, 'ATIVA', '2026-02-22 16:03:55', '2026-03-24', 0.00, 0.00),
(6, 'TESTE 1', '92.343.375/0001-71', '', 'asdasda@gmail.com', 3, 'PENDENTE', '2026-03-01 01:39:40', NULL, 0.00, 0.00),
(7, 'TESTE 1', '92.343.375/0001-73', '', 'asdasda@gmail.com', 2, 'PENDENTE', '2026-03-01 01:41:19', NULL, 0.00, 0.00),
(8, 'TESTE 1', '92.343.375/0001-77', '', 'lucasbugatib0@gmail.com', 2, 'INATIVA', '2026-03-01 01:45:03', '2026-03-31', 0.00, 0.00);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
