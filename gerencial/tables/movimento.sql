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
-- Estrutura para tabela `movimento`
--

CREATE TABLE `movimento` (
  `id` int(11) NOT NULL,
  `Forcli` int(11) NOT NULL,
  `ForcliRepasse` int(11) NOT NULL,
  `idEmpresa` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `CondPgto` int(11) NOT NULL,
  `Data` datetime NOT NULL,
  `CorVeiculo` varchar(30) NOT NULL,
  `ModeloVeiculo` varchar(300) NOT NULL,
  `PlacaVeiculo` varchar(300) NOT NULL,
  `DataAlt` datetime NOT NULL,
  `DataPgto` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `movimento`
--

INSERT INTO `movimento` (`id`, `Forcli`, `ForcliRepasse`, `idEmpresa`, `Status`, `CondPgto`, `Data`, `CorVeiculo`, `ModeloVeiculo`, `PlacaVeiculo`, `DataAlt`, `DataPgto`) VALUES
(106, 28, 0, 1, 1, 2, '2026-02-16 00:00:00', 'Branco', 'GOL GT 1.8', 'IBN7856', '2026-02-16 13:58:17', NULL),
(107, 27, 0, 1, 2, 7, '2026-02-16 00:00:00', 'Branco', 'CHEVROLET CAMARO', 'IJK3443', '2026-02-16 15:37:39', NULL),
(108, 27, 0, 1, 1, 2, '2026-02-17 00:00:00', 'Branco', 'GOL GT 1.8', 'IBN7856', '2026-02-16 15:25:56', NULL),
(109, 25, 0, 1, 1, 6, '2026-02-18 00:00:00', 'Branco', '', '', '2026-02-18 12:14:55', NULL),
(110, 27, 0, 1, 2, 1, '2026-02-18 00:00:00', 'Branco', 'GOL GT', 'IBN7856', '2026-02-18 15:52:18', NULL),
(111, 29, 0, 1, 1, 1, '2026-02-19 00:00:00', 'Branco', '', '', '2026-02-18 18:20:33', NULL),
(112, 30, 0, 2, 1, 1, '2026-02-19 00:00:00', 'Branco', 'CHEVROLET CAMARO', '3234432', '2026-02-19 22:19:33', NULL),
(116, 33, 0, 2, 1, 1, '2026-02-20 00:00:00', 'Branco', '', '', '2026-02-20 14:26:48', NULL),
(117, 33, 0, 2, 1, 1, '2026-02-20 00:00:00', 'Branco', '', '', '2026-02-20 17:13:23', NULL),
(118, 25, 31, 1, 0, 1, '2026-02-20 00:00:00', 'Branco', '', '', '2026-02-20 17:43:53', NULL),
(119, 27, 28, 1, 1, 1, '2026-02-20 00:00:00', 'Branco', '', '', '2026-02-25 13:53:35', NULL),
(128, 33, 0, 2, 1, 1, '2026-02-22 00:00:00', 'Branco', '', '', '2026-02-22 20:08:28', NULL),
(129, 29, 0, 1, 1, 1, '2026-02-22 00:00:00', 'Branco', '', '', '2026-02-22 20:08:40', NULL),
(130, 33, 0, 2, 1, 1, '2026-02-22 00:00:00', 'Branco', '', '', '2026-02-22 20:59:44', NULL),
(131, 33, 0, 2, 1, 1, '2026-02-23 00:00:00', 'Branco', '', '', '2026-02-23 08:05:50', NULL),
(132, 25, 0, 1, 1, 1, '2026-02-23 00:00:00', 'Branco', '', '', '2026-02-23 17:07:27', NULL),
(133, 30, 0, 1, 1, 1, '2026-02-23 00:00:00', 'Branco', '', '', '2026-02-23 17:07:41', NULL),
(134, 33, 0, 2, 1, 1, '2026-02-24 00:00:00', 'Branco', '', '', '2026-02-28 16:18:13', NULL),
(135, 27, 0, 1, 1, 1, '2026-02-25 00:00:00', 'Branco', '', '', '2026-02-25 13:53:05', NULL),
(136, 27, 0, 1, 1, 1, '2026-02-27 00:00:00', 'Branco', '', '', '2026-02-27 11:46:16', NULL),
(140, 28, 0, 1, 1, 1, '2026-02-28 00:00:00', 'Branco', '', '', '2026-02-28 18:23:10', NULL),
(141, 28, 0, 1, 1, 1, '2026-02-28 00:00:00', 'Verde', 'GOL GT', 'IJK3443', '2026-02-28 19:42:57', NULL),
(142, 33, 0, 2, 1, 1, '2026-02-28 00:00:00', 'Vermelho', 'GOL GT', 'KLKL7933', '2026-02-28 20:11:16', NULL),
(143, 33, 0, 2, 1, 1, '2026-02-28 00:00:00', 'Verde', 'GOL GT', 'IJK3443', '2026-02-28 20:10:39', NULL),
(145, 27, 31, 1, 0, 1, '2026-02-28 00:00:00', '', '', '', '2026-02-28 20:17:11', NULL),
(147, 28, 0, 1, 1, 1, '2026-02-28 00:00:00', '', '', '', '2026-02-28 20:24:50', NULL),
(151, 28, 28, 1, 0, 1, '2026-02-28 00:00:00', '', '', '', '2026-02-28 20:33:33', NULL),
(152, 28, 28, 1, 0, 1, '2026-02-28 00:00:00', '', '', '', '2026-02-28 21:12:37', NULL),
(163, 27, 31, 1, 1, 1, '2026-03-01 00:00:00', '', '', '', '2026-03-01 23:58:38', '2026-03-04 23:58:38'),
(164, 29, 0, 1, 1, 1, '2026-03-02 00:00:00', '', '', '', '2026-03-02 00:13:14', '2026-03-02 00:13:14'),
(165, 27, 34, 1, 1, 1, '2026-03-02 00:00:00', '', '', '', '2026-03-02 15:47:43', '2026-03-02 15:47:43');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `movimento`
--
ALTER TABLE `movimento`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `movimento`
--
ALTER TABLE `movimento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
