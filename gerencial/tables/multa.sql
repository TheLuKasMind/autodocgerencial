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
-- Estrutura para tabela `multa`
--

CREATE TABLE `multa` (
  `id` int NOT NULL,
  `idEmpresa` int NOT NULL,
  `Forcli` int NOT NULL,
  `DataCadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DataAlt` datetime DEFAULT NULL,
  `SerieMulta` varchar(50) NOT NULL,
  `CodigoProcesso` varchar(50) DEFAULT NULL,
  `OrgaoFiscalizador` varchar(100) DEFAULT NULL,
  `PlacaVeiculo` varchar(20) NOT NULL,
  `PlacasAdicionais` text,
  `RegistroCNH` varchar(30) DEFAULT NULL,
  `PrazoDefesa` date DEFAULT NULL,
  `AutoSuspensiva` tinyint(1) DEFAULT '0',
  `RecursoMulta` tinyint(1) DEFAULT '0',
  `StatusMulta` int DEFAULT '0',
  `Observacao` text,
  `Inativo` tinyint(1) DEFAULT '0',
  `idUser` int DEFAULT NULL,
  `UserAlt` int DEFAULT NULL,
  `EnviarLembrete` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `multa`
--

INSERT INTO `multa` (`id`, `idEmpresa`, `Forcli`, `DataCadastro`, `DataAlt`, `SerieMulta`, `CodigoProcesso`, `OrgaoFiscalizador`, `PlacaVeiculo`, `PlacasAdicionais`, `RegistroCNH`, `PrazoDefesa`, `AutoSuspensiva`, `RecursoMulta`, `StatusMulta`, `Observacao`, `Inativo`, `idUser`, `UserAlt`, `EnviarLembrete`) VALUES
(5, 11, 161, '2026-05-05 00:00:00', '2026-05-05 00:05:27', 'IDX938POP3', '129217472', 'PRF', 'IXT-90566', '', '3292758238', '2026-05-19', 1, 1, 4, 'TESTE DO LUCAS', 0, 32, 32, 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `multa`
--
ALTER TABLE `multa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Forcli` (`Forcli`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `multa`
--
ALTER TABLE `multa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `multa`
--
ALTER TABLE `multa`
  ADD CONSTRAINT `multa_ibfk_1` FOREIGN KEY (`Forcli`) REFERENCES `forcli` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
