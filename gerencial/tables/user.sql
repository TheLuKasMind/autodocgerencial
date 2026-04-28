-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 28/04/2026 às 09:11
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
-- Estrutura para tabela `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `Nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Cargo` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT ' ',
  `Email` varchar(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Senha` varchar(700) COLLATE utf8mb4_general_ci NOT NULL,
  `Inativo` tinyint(1) DEFAULT '0',
  `DataUtimoAcesso` datetime DEFAULT NULL,
  `IPUltimoAcesso` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Tipo` int NOT NULL DEFAULT '0',
  `idEmpresa` int NOT NULL,
  `AdminGeral` int NOT NULL DEFAULT '0',
  `tokenRecuperar` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT ' ',
  `tokenExpira` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `user`
--

INSERT INTO `user` (`id`, `Nome`, `Cargo`, `Email`, `Senha`, `Inativo`, `DataUtimoAcesso`, `IPUltimoAcesso`, `Tipo`, `idEmpresa`, `AdminGeral`, `tokenRecuperar`, `tokenExpira`) VALUES
(23, 'Thiago Allgayer Ebertz', ' Dono', 'despachanteallgayer@gmail.com', '$2y$10$Vi38obXy71Znmj17CdS62urDphTF0H8Youyt1ZVAUsVInEma6YnTK', 0, NULL, NULL, 2, 11, 1, NULL, NULL),
(26, 'Matheus Portaluppi', ' ', 'matheusportaluppi09@gmail.com', '$2y$10$fJIK7GZGGblI.PR7dOZzoOR3PSsqSXYTTuomwFrRITYETokZqDgRK', 1, NULL, NULL, 2, 14, 0, ' ', '2026-03-18 10:31:43'),
(29, 'Wellington Allgayer', 'Proprietário', 'w.allgayer@hotmail.com', '$2y$10$taxaZFrxQ19MboGxnFRObenKKOO79zBwpakfxY3dTU599pUNxq8B6', 0, NULL, NULL, 0, 11, 0, ' ', '2026-03-23 22:57:19'),
(30, 'YAN BORBA SCHEFFER', ' ', 'YANSCHEFFER@GMAIL.COM', '$2y$10$WdE7N0PKvZx4ijgTrmh6MeDJwLvwD77cPzdIUvsvv3rSPRZg9zULK', 0, NULL, NULL, 2, 16, 0, ' ', '2026-04-20 12:34:15'),
(31, 'USUÁRIO TESTE', 'Teste', 'teste@gmail.com', '$2y$10$g93hHESGIjwePt9uXy9v8e7eGDPLtUVHgeBs4mucZBBWdOE1vBZOy', 0, NULL, NULL, 0, 16, 0, ' ', '2026-04-20 15:13:56'),
(32, 'Lucas Teste', '', 'lucasbugatib0@gmail.com', '$2y$10$etUNILqi.UXRUy.oJmffgOwVpH20hK59engJN1kquGbXuU8I3pHKW', 0, NULL, NULL, 1, 11, 1, ' ', '2026-04-22 17:18:08');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
