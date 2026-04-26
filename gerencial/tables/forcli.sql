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
-- Estrutura para tabela `forcli`
--

CREATE TABLE `forcli` (
  `id` int(11) NOT NULL,
  `Nome` varchar(120) NOT NULL,
  `RazaoSocial` varchar(120) NOT NULL,
  `Tipo` int(11) NOT NULL,
  `TipoDocumento` int(11) NOT NULL,
  `Documento` varchar(50) NOT NULL,
  `Telefone` varchar(50) NOT NULL,
  `Email` varchar(75) NOT NULL,
  `Obs` varchar(150) NOT NULL,
  `CEP` varchar(20) NOT NULL,
  `Rua` varchar(70) NOT NULL,
  `NumeroEndereco` varchar(25) NOT NULL,
  `Bairro` varchar(40) NOT NULL,
  `Cidade` varchar(70) NOT NULL,
  `UF` varchar(30) NOT NULL,
  `Inativo` int(11) NOT NULL,
  `DataCadastro` datetime NOT NULL,
  `idEmpresa` int(11) NOT NULL,
  `Codigo` int(11) NOT NULL,
  `DataAlt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `forcli`
--

INSERT INTO `forcli` (`id`, `Nome`, `RazaoSocial`, `Tipo`, `TipoDocumento`, `Documento`, `Telefone`, `Email`, `Obs`, `CEP`, `Rua`, `NumeroEndereco`, `Bairro`, `Cidade`, `UF`, `Inativo`, `DataCadastro`, `idEmpresa`, `Codigo`, `DataAlt`) VALUES
(25, 'Lucas Renan - Compumate', 'Lucas Corporations', 1, 0, '039.915.150-83', '519994343434', '0lucasrenan@compumate.com.br', '', '93537-410', 'Rua Leopoldo Guilherme Bauer', '222', 'São Jorge', 'NOVO HAMBURGO', 'RS', 0, '2026-03-01 16:53:46', 1, 1, '2026-03-01 16:53:46'),
(27, 'Robertinho Da Zete', 'Robertinho Corporation', 1, 0, '039.915.150-83', '519994343434', '00lucasrenan@compumate.com.br', '', '93537-410', 'Rua Leopoldo Guilherme Bauer', '222', 'São Jorge', 'NOVO HAMBURGO', 'RS', 0, '2026-02-12 08:27:30', 1, 16, '2026-02-12 08:27:30'),
(28, 'Revenda do Cleber', 'Revenda do Cleber', 1, 0, '039.915.150-83', '51 999487401', '000010lucass@gmail.com', '', '95800-000', 'Rua do Cocôs', '222', 'CentroB', 'Venâncio Aires', 'RS', 0, '2026-03-01 16:53:51', 1, 17, '2026-03-01 16:53:51'),
(29, 'AAAAAAAAAAAAAAA', 'Lucas Corporations LTDA.', 1, 18, '039.915.150-83', '51 999487401', '010lucass@gmail.com', '', '95800-000', 'Rua do Cocôs', '222', 'CentroB', 'Venâncio Aires', 'RS', 0, '0000-00-00 00:00:00', 1, 18, '2026-02-11 16:56:31'),
(30, 'Compumate Softrwares ', 'Compumate Softwares Corporativos LTDA', 3, 19, '92.343.375/0001-72', '5137413793', '0compumate@compumate.com.br', 'Esse é çasda', '95800-000', 'Rua do Cocôs', '222', 'CentroB', 'Venâncio Aires', 'RS', 0, '0000-00-00 00:00:00', 1, 19, '2026-02-14 20:32:20'),
(31, 'Alexia Weinig', 'Alexia Weinig', 1, 20, '92.343.375/0001-72', '5137413793', '0compumate@compumate.com.br', '', '95800-000', 'Rua do Cocôs', '222', 'CentroB', 'Venâncio Aires', 'RS', 0, '0000-00-00 00:00:00', 1, 20, '2026-02-15 13:51:08'),
(32, 'Thiago', 'Thiago', 1, 21, '92.343.375/0001-72', '5137413793', '0compumate@compumate.com.br', '', '95800-000', 'Rua do Cocôs', '222', 'CentroB', 'Venâncio Aires', 'RS', 0, '0000-00-00 00:00:00', 1, 21, '2026-02-15 18:10:26'),
(33, 'Teste Email', 'adsad', 1, 0, '039.915.150-83', '51999487401', '0lucasbugatib0@gmail.com', '', '68745-210', 'Alameda Carlos Gomes', '111', 'Santa Lídia', 'CASTANHAL', '', 0, '2026-03-01 16:26:56', 2, 1, '2026-03-01 16:26:56'),
(34, 'Robo Gamer', 'Robo Gamer ', 1, 22, '60.327.422/0001-06', '(32) 3452-42', '0roboo@gmail.com', '', '', '', '', '', '', '', 0, '0000-00-00 00:00:00', 1, 22, '2026-03-01 14:43:57');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `forcli`
--
ALTER TABLE `forcli`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `forcli`
--
ALTER TABLE `forcli`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
