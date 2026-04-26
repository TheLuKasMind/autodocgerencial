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
-- Estrutura para tabela `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `Nome` varchar(100) DEFAULT NULL,
  `Cargo` varchar(70) DEFAULT NULL,
  `Email` varchar(60) DEFAULT NULL,
  `Senha` varchar(700) NOT NULL,
  `Inativo` tinyint(1) DEFAULT 0,
  `DataUtimoAcesso` datetime DEFAULT NULL,
  `IPUltimoAcesso` varchar(500) DEFAULT NULL,
  `Tipo` int(11) NOT NULL,
  `idEmpresa` int(11) NOT NULL,
  `AdminGeral` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `user`
--

INSERT INTO `user` (`id`, `Nome`, `Cargo`, `Email`, `Senha`, `Inativo`, `DataUtimoAcesso`, `IPUltimoAcesso`, `Tipo`, `idEmpresa`, `AdminGeral`) VALUES
(3, 'Lucas', '', 'lucas@email.com', '$2y$10$UckZQ/C6uZNDZ2EUxbfDS.zOsLV/sZwF4cJFILdWKkvaJHpy3m4pG', 0, NULL, NULL, 2, 1, 0),
(11, 'Lukinhas', 'Cargo', 'lukinhas@gmail.com', '$2y$10$vo.arJXTAtEtgoNX.fxqGeb29laoT.YqsfHz49dIqDphBTklxALJK', 0, NULL, NULL, 1, 1, 0),
(12, 'JET PRINCIPAL', NULL, 'jetee@gmail.com', '$2y$10$KBec8kkidQgdBY17N2L1teav3InQj7CjkIKOG2L9AfJ9nFoy.bCCK', 0, NULL, NULL, 2, 2, 1),
(13, 'Thiago Allgayer', NULL, 'thiagoallgayer@gmail.com', '$2y$10$Dz/N6GPdY.PasR0VlQt.3OTjkbqy9Vn6NNjOG2xGS0vY9kFQV.CJy', 0, NULL, NULL, 1, 3, 0),
(14, 'Thiago Allgayer2', NULL, 'thiagoallgayer2@gmail.com', '$2y$10$YcNeXBMSWn5P/HDGPdn8NOmmw2M78oo.dPFE4aevHvaRXAYeQ/J0W', 0, NULL, NULL, 1, 4, 0),
(15, 'Thiago Allgayer2', NULL, 'thiagoallgayer2@gmail.com', '$2y$10$4TJosF.ORRJksW16EAXzVe3Szs25gGzaLD4aST2ocoiiFm8f8U1l6', 0, NULL, NULL, 1, 5, 0),
(16, 'CERTIFICADO', NULL, 'asdada@gmail.com', '$2y$10$uuhiUQYzl8IVCrkA9n1bt.R5N3Cqhg5nYNVjx8E2zW.yjD2SXzSai', 1, NULL, NULL, 1, 6, 0),
(17, 'CERTIFICADO', NULL, 'asdada@gmail.com', '$2y$10$O8OeF.YvCYARsI7qeA9Ed.gnD8BKcNNsmAYHpL1l6.TRabYT5NZJy', 1, NULL, NULL, 1, 7, 0),
(18, 'CERTIFICADO', NULL, 'asdada@gmail.com', '$2y$10$l.KsKmQsO1EbQvntjYj5Pejt7ZDUCPJeGHJ3JP5sHG7OWKRBE/N8.', 0, NULL, NULL, 1, 8, 0),
(21, 'joaozinho', 'Dono', 'joao@gmail.com', '$2y$10$Ot/.Lf56Ki8LXHPZChOiDezLqI3J0p45j37n96WthNQiJpgelD2ry', 0, NULL, NULL, 0, 2, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
