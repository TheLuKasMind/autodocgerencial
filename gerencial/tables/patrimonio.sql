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
-- Estrutura para tabela `patrimonio`
--

CREATE TABLE `patrimonio` (
  `id` int NOT NULL,
  `idEmpresa` int NOT NULL,
  `descricao` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `dataCompra` date NOT NULL,
  `dataAlt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `patrimonio`
--

INSERT INTO `patrimonio` (`id`, `idEmpresa`, `descricao`, `valor`, `dataCompra`, `dataAlt`) VALUES
(10, 11, 'Moto vermelha', 4000.00, '2026-03-01', '2026-03-09 15:12:40'),
(11, 11, 'Moto 2026', 16500.00, '2026-03-09', '2026-03-09 15:12:53'),
(12, 11, 'Mesas Novas', 2200.00, '2026-03-12', '2026-03-16 17:16:56'),
(13, 16, 'Sofá Recepcão', 962.03, '2026-05-02', '2026-05-02 18:55:19'),
(14, 16, 'Porta de Vidro', 1950.00, '2025-12-02', '2026-05-02 18:57:54'),
(15, 16, 'Ar Condicionado Principal', 2500.00, '2026-01-03', '2026-05-02 18:58:26'),
(16, 16, 'Cadeiras', 1680.00, '2025-12-09', '2026-05-02 18:59:30'),
(17, 16, 'Pelicula Porta de Vidro', 500.00, '2026-12-18', '2026-05-02 19:00:40'),
(18, 16, '2 Computadores ', 1977.89, '2025-12-18', '2026-05-02 19:01:08'),
(19, 16, 'Scanner e 2 Monitores', 2568.78, '2025-12-18', '2026-05-02 19:01:47'),
(20, 16, 'Cameras e Alarme', 1271.65, '2026-01-13', '2026-05-02 19:04:04'),
(21, 16, 'Balcão Pia ', 200.00, '2026-01-21', '2026-05-02 19:05:02'),
(22, 16, 'Frigobar', 759.00, '2026-01-31', '2026-05-02 19:05:22'),
(23, 16, 'Cafeteria + Estrutura Banheiro', 1227.73, '2026-05-02', '2026-05-02 19:06:09'),
(24, 16, 'Fachada ACM', 4345.00, '2026-01-04', '2026-05-02 19:07:05'),
(25, 16, 'Móveis Escritório', 11000.00, '2026-12-05', '2026-05-02 19:09:55'),
(26, 16, '2 monitores', 1095.02, '2026-04-05', '2026-05-02 19:11:15'),
(27, 16, 'Computador Yan', 1500.00, '2026-04-05', '2026-05-02 19:11:38'),
(28, 16, 'Corsa Prata MBK5J73', 13900.00, '2026-05-02', '2026-05-02 19:12:46'),
(29, 16, 'CG 150 Vermelha 2009 IPS8I35', 10900.00, '2026-05-02', '2026-05-02 19:13:23'),
(30, 11, 'Moto ton', 3000.00, '2026-05-04', '2026-05-04 23:14:12'),
(31, 11, 'Fit', 35000.00, '2026-05-04', '2026-05-04 23:14:40'),
(32, 11, 'Parati', 8000.00, '2026-05-04', '2026-05-04 23:15:03'),
(33, 11, 'Uno escritório', 10000.00, '2026-05-04', '2026-05-04 23:15:19');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `patrimonio`
--
ALTER TABLE `patrimonio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idEmpresa` (`idEmpresa`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `patrimonio`
--
ALTER TABLE `patrimonio`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
