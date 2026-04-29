-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 29/04/2026 às 00:26
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
-- Estrutura para tabela `grupos`
--

CREATE TABLE `grupos` (
  `Id` int NOT NULL,
  `Nome` varchar(150) DEFAULT NULL,
  `Tipo` char(1) DEFAULT NULL,
  `Inativo` tinyint(1) DEFAULT NULL,
  `idEmpresa` int DEFAULT NULL,
  `DataCadastro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `grupos`
--

INSERT INTO `grupos` (`Id`, `Nome`, `Tipo`, `Inativo`, `idEmpresa`, `DataCadastro`) VALUES
(8, 'TRANSFERÊNCIAS GERAL', 'P', 0, 11, '2026-03-19 22:52:35'),
(10, '2° VIA GERAL', 'P', 0, 11, '2026-03-19 22:55:22'),
(11, 'SOLICITAÇÃO DE VISTORIA GERAL', 'P', 0, 11, '2026-03-19 22:56:00'),
(12, 'GRUPO DAS REVENDAS', 'C', 0, 11, '2026-03-19 22:57:27'),
(14, 'MULTAS', 'P', 0, 16, '2026-04-22 07:49:49'),
(15, 'CLIENTE REPASUL', 'C', 0, 16, '2026-04-22 14:02:35'),
(16, 'FINAL', 'C', 0, 11, '2026-04-27 17:55:37');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `grupos`
--
ALTER TABLE `grupos`
  MODIFY `Id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
