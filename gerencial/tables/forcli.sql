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
-- Estrutura para tabela `forcli`
--

CREATE TABLE `forcli` (
  `id` int NOT NULL,
  `Nome` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `RazaoSocial` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `Tipo` int NOT NULL,
  `TipoDocumento` int NOT NULL,
  `Documento` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Telefone` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(75) COLLATE utf8mb4_general_ci NOT NULL,
  `Obs` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `CEP` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `Rua` varchar(70) COLLATE utf8mb4_general_ci NOT NULL,
  `NumeroEndereco` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `Bairro` varchar(40) COLLATE utf8mb4_general_ci NOT NULL,
  `Cidade` varchar(70) COLLATE utf8mb4_general_ci NOT NULL,
  `UF` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `Inativo` int NOT NULL,
  `DataCadastro` datetime NOT NULL,
  `idEmpresa` int NOT NULL,
  `Codigo` int NOT NULL,
  `DataAlt` datetime DEFAULT NULL,
  `Grupo` int DEFAULT NULL,
  `EstadoCivil` int DEFAULT NULL,
  `Profissao` varchar(70) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `forcli`
--

INSERT INTO `forcli` (`id`, `Nome`, `RazaoSocial`, `Tipo`, `TipoDocumento`, `Documento`, `Telefone`, `Email`, `Obs`, `CEP`, `Rua`, `NumeroEndereco`, `Bairro`, `Cidade`, `UF`, `Inativo`, `DataCadastro`, `idEmpresa`, `Codigo`, `DataAlt`, `Grupo`, `EstadoCivil`, `Profissao`) VALUES
(36, 'AIRTON L. FERREIRA & CIA LTDA', 'AIRTON L. FERREIRA & CIA LTDA', 2, 0, '17.476.008/0001-21', '5137411727', 'paulomorsch@viavale.com.br', '', '', 'Rua General Osorio', '955', 'Centro', 'Venâncio Aires', 'RS', 0, '2026-05-04 21:40:39', 11, 1, '2026-05-04 21:40:39', 12, 0, ''),
(37, 'LEHMEN VEICULOS', 'MAICON ALEX LEHMEN & CIA LTDA', 2, 0, '33.657.794/0001-10', '5183243550', 'lehmenmaicon@gmail.com', '', '', 'Rua 15 de Novembro', '1635', 'Centro', 'Venâncio Aires', 'RS', 0, '2026-03-19 22:59:13', 11, 2, '2026-03-19 22:59:13', 12, NULL, NULL),
(38, 'FINAL', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-27 17:55:52', 11, 3, '2026-04-27 17:55:52', 16, NULL, NULL),
(39, 'LW VEICULOS', 'LW VEICULOS LTDA', 2, 0, '44.013.193/0001-90', '5196118804', 'wagferrei@yahoo.com', '', '', 'Rua Dr Armando Ruschel', '1701', 'Bela Vista', 'Venâncio Aires', 'RS', 0, '2026-03-19 22:59:23', 11, 4, '2026-03-19 22:59:23', 12, NULL, NULL),
(40, 'WEHNER MOTORS', 'WEHNER MOTORS COMERCIO DE VEICULOS LTDA', 2, 0, '60.747.047/0001-45', '5139190919', 'societario@escritorioatena.com.br', '', '', 'Rua Aurino Guterres de Carvalho', '1920', 'Bela Vista', 'Venâncio Aires', 'RS', 0, '2026-03-19 23:00:51', 11, 5, '2026-03-19 23:00:51', 12, NULL, NULL),
(41, 'GUILHERME MENZEL', '', 2, 0, '012.439.240-71', '(51) 99842-0713', '', '', '95800-000', '', '', '', '', '', 0, '2026-03-19 22:59:03', 11, 6, '2026-03-19 22:59:03', 12, NULL, NULL),
(42, 'AR MULTIMARCAS', '', 2, 0, '45.510.139/0001-13', '5139190919', 'societario@escritorioatena.com.br', '', '', 'Rua General Osorio', '765', 'Aviacao', 'Venâncio Aires', 'RS', 0, '2026-03-19 22:57:46', 11, 7, '2026-03-19 22:57:46', 12, NULL, NULL),
(43, 'ARTHUR HENRIQUE WENZEL', '', 2, 0, '016.989.010-46', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 13:29:08', 11, 8, '2026-05-04 13:29:08', 12, 0, ''),
(44, 'ASTERIO LUIS KIST', '', 2, 0, '713.998.870-68', '', '', '', '', '', '', '', '', '', 0, '2026-05-01 17:50:17', 11, 9, '2026-05-01 17:50:17', 12, 0, ''),
(45, 'ERLI SOARES DE OLIVEIRA', '', 2, 0, '463.484.840-68', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 22:58:40', 11, 10, '2026-03-19 22:58:40', 12, NULL, NULL),
(46, 'DANIEL GOMES', '', 2, 0, '011.413.810-95', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 22:58:14', 11, 11, '2026-03-19 22:58:14', 12, NULL, NULL),
(47, 'DINAMICA CAR', 'DINAMICA CAR COMERCIO DE VEICULOS LTDA', 2, 0, '07.669.240/0001-22', '5137415368', 'dinamicacarrs@gmail.com', '', '', 'Rua General Osorio', '648', 'Aviacao', 'Venâncio Aires', 'RS', 0, '2026-03-09 15:49:30', 11, 12, '2026-03-09 15:49:30', NULL, NULL, NULL),
(48, 'RIEDEL MUTIMARCAS ', 'EDUARDO JOSE RIEDEL', 2, 0, '50.117.853/0001-86', '5137415333', 'sagrasel@gmail.com', '', '', 'Rua Professor Jose Loebens', '55', 'Cidade Nova', 'Venâncio Aires', 'RS', 0, '2026-03-19 22:59:52', 11, 13, '2026-03-19 22:59:52', 12, NULL, NULL),
(49, 'ELIAS RICARDO BITTENCOURT', '', 2, 0, '933.937.090-20', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 22:58:23', 11, 14, '2026-03-19 22:58:23', 12, NULL, NULL),
(50, 'ELITE VEICULOS', 'RT MOHR COMERCIO DE VEICULOS LTDA', 2, 0, '33.236.192/0001-99', '5198337938', '', '', '', 'Rua Voluntarios da Patria', '2206', 'Cruzeiro', 'Venâncio Aires', 'RS', 0, '2026-03-19 22:58:32', 11, 15, '2026-03-19 22:58:32', 12, NULL, NULL),
(51, 'FABIANO MULTIMARCAS', 'FABIANO DA SILVA', 2, 0, '40.089.199/0001-90', '5198257811', 'ale.contabilidadedigital@gmail.com', '', '', 'Rua General Osorio', '477', 'Centro', 'Venâncio Aires', 'RS', 0, '2026-04-01 10:27:29', 11, 16, '2026-04-01 10:27:29', 12, NULL, NULL),
(52, 'GILSON ANDRE SODA', '', 2, 0, '015.726.380-01', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 22:58:56', 11, 17, '2026-03-19 22:58:56', 12, NULL, NULL),
(53, 'OLAIR MACHADO DE BITTENCOURT', '', 2, 0, '268.484.060-15', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 22:59:32', 11, 18, '2026-03-19 22:59:32', 12, NULL, NULL),
(54, 'PROCAR MULTIMARCAS', 'PROCAR MULTIMARCAS LTDA', 2, 0, '47.442.516/0001-50', '5137415333', 'sagrasel@gmail.com', '', '', 'Rua Salvador Stein Goulart', '1971', 'Macedo', 'Venâncio Aires', 'RS', 0, '2026-04-01 10:27:17', 11, 19, '2026-04-01 10:27:17', 12, NULL, NULL),
(55, 'RONAN GIL FERREIRA', '', 2, 0, '034.317.770-66', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 23:00:02', 11, 20, '2026-03-19 23:00:02', 12, NULL, NULL),
(56, 'SILVIO ROBERTO DE SAIBRO', '', 2, 0, '035.168.940-08', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 23:00:14', 11, 21, '2026-03-19 23:00:14', 12, NULL, NULL),
(57, 'TIAGO AUTOMÓVEIS', '', 2, 0, '004.421.650-56', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 23:00:25', 11, 22, '2026-03-19 23:00:25', 12, NULL, NULL),
(58, 'VALMOR LUIS RIEGER', '', 2, 0, '735.883.770-34', '', '', '', '', '', '', '', '', '', 0, '2026-03-19 23:00:35', 11, 23, '2026-03-19 23:00:35', 12, NULL, NULL),
(59, 'W S MULTIMARCAS LTDA', 'W S MULTIMARCAS LTDA', 2, 0, '48.416.100/0001-20', '5198442071', 'wsmultimarcasrs@gmail.com', '', '', 'Rua Barao do Triunfo', '1562', 'Centro', 'Venâncio Aires', 'RS', 0, '2026-04-01 10:27:04', 11, 24, '2026-04-01 10:27:04', 12, NULL, NULL),
(69, 'EURIPEDES VIEIRA DA SILVA', '', 2, 0, '350.025.146-34', '', '', '', '', '', '', '', '', '', 0, '2026-04-01 10:27:38', 11, 26, '2026-04-01 10:27:38', 0, NULL, NULL),
(70, 'DANIEL MARTINS', '', 1, 0, '030.797.560-61', '', '', '', '', '', '', '', '', '', 0, '2026-04-01 15:11:03', 11, 27, '2026-04-01 15:11:03', 0, NULL, NULL),
(71, 'JUNIOR WACHOLZ', '', 1, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-02 14:01:19', 11, 28, '2026-04-02 14:01:19', 0, NULL, NULL),
(72, 'VANDRE STEDTEN PEREIRA', '', 1, 0, '822.349.220-20', '', '', '', '', '', '', '', '', '', 0, '2026-04-02 17:05:24', 11, 29, '2026-04-02 17:05:24', 0, NULL, NULL),
(75, 'TRANSPORTADORA AUGUSTA', 'TRANSPORTADORA AUGUSTA SP LTDA', 1, 0, '94.193.257/0001-50', '5137413104', '', '', '', 'Rua Americo Vespucio', '381', 'Higienopolis', 'Porto Alegre', 'RS', 0, '2026-04-17 10:34:48', 11, 31, '2026-04-17 10:34:48', 0, NULL, NULL),
(76, 'Repasul Veiculos', 'REPASUL VEICULOS LTDA', 2, 0, '51.731.732/0001-92', '5134693650', 'lucas-piletti@hotmail.com', '', '', 'Avenida Dorival Candido Luz de Oliveira', '7050', 'Bom Principio', 'Gravataí', 'RS', 0, '2026-04-20 13:18:26', 16, 1, '2026-04-20 13:18:26', 0, NULL, NULL),
(79, 'ANDRE LUIS GASPERIN DE JESUS', 'DE MOTOS', 4, 0, '829.536.270-49', '(51) 98573-4901', '', '', '94130-180', 'Rua Mário Quintana', '150', 'Bom Sucesso', 'Gravataí', 'RS', 0, '2026-04-20 18:11:52', 16, 3, '2026-04-20 18:11:52', 13, NULL, NULL),
(80, 'SABRINA AGUERO CARDOSO', '', 4, 0, '036.167.670-08', '', '', '', '', '', '', '', '', '', 0, '2026-04-22 07:47:47', 16, 4, '2026-04-22 07:47:47', 0, NULL, NULL),
(81, 'FRAGA CONSTRUCOES E INCORPORACOES LTDA', 'FRAGA CONSTRUCOES E INCORPORACOES LTDA', 1, 0, '15.514.027/0001-70', '5134701631', '', '', '', 'Rua Cai', '540', 'Vila Princesa Izabel', 'Cachoeirinha', 'RS', 0, '2026-04-22 08:22:51', 16, 5, '2026-04-22 08:22:51', 0, NULL, NULL),
(82, 'CARLOS ALBERTO DA SILVEIRA', '', 1, 0, '453.506.120-34', '', '', '', '', '', '', '', '', '', 0, '2026-04-22 08:35:16', 16, 6, '2026-04-22 08:35:16', 0, NULL, NULL),
(83, 'MARCOS FERNANDO AGNE DE OLIVEIRA', '', 1, 0, '819.120.640-49', '', '', '', '', '', '', '', '', '', 0, '2026-04-22 10:00:35', 16, 7, '2026-04-22 10:00:35', 0, NULL, NULL),
(84, 'SILVANA NUNES CABRAL', '', 1, 0, '021.085.370-07', '', '', '', '', '', '', '', '', '', 0, '2026-04-22 12:19:03', 16, 8, '2026-04-22 12:19:03', 0, NULL, NULL),
(85, 'JOAO VITOR DOS ANJOS GABARRUS', '', 4, 0, '009.490.830-38', '(51) 99599-4059', '', '', '', '', '', '', '', '', 0, '2026-04-22 13:55:27', 16, 9, '2026-04-22 13:55:27', 0, NULL, NULL),
(86, 'DANIELLY FERNANDA BATALHA', '', 4, 0, '600.987.300-29', '(51) 99212-0254', '', '', '94960-482', 'Rua Guatambu', '155', 'Jardim do Bosque', 'Cachoeirinha', 'RS', 0, '2026-04-22 14:02:54', 16, 10, '2026-04-22 14:02:54', 15, NULL, NULL),
(87, 'SELTON REINHEIMER SCHWANCK', '', 4, 0, '010.116.410-62', '(51) 99793-6430', '', '', '94960-814', 'Rua Nildo Hainzereder Schutz', '701', 'Parque Granja Esperança', 'Cachoeirinha', 'RS', 0, '2026-04-22 14:15:11', 16, 11, '2026-04-22 14:15:11', 0, NULL, NULL),
(88, 'AMANDA DENISE BISCAGLIA DOS SANTOS', '', 4, 0, '600.427.380-59', '(51) 98188-6573', '', '', '94050-210', 'Rua Icara', '5', 'COHAB A', 'Gravataí', 'RS', 0, '2026-04-22 14:28:56', 16, 12, '2026-04-22 14:28:56', 0, NULL, NULL),
(90, 'WALACI DOS SANTOS GONCALVES', '', 1, 0, '034.740.910-50', '', '', '', '', '', '', '', '', '', 0, '2026-04-23 10:43:12', 16, 14, '2026-04-23 10:43:12', 0, NULL, NULL),
(91, 'EDUARDO CARDOSO NUNES', '', 1, 0, '602.934.290-81', '', '', '', '', '', '', '', '', '', 0, '2026-04-23 10:46:26', 16, 15, '2026-04-23 10:46:26', 0, NULL, NULL),
(92, 'VALMO CORREA DE CAMARGO JUNIOR', '', 4, 0, '392.060.330-34', '(51) 99677-5353', '', '', '94060-425', 'Rua José Azevedo Ussan', '235', 'São Vicente', 'Gravataí', 'RS', 0, '2026-04-23 13:20:39', 16, 16, '2026-04-23 13:20:39', 0, NULL, NULL),
(93, 'ANDERSON GOMES EVALDT', '', 2, 0, '014.606.390-22', '(51) 99492-8771', '', '', '94950-001', 'Avenida General Flores da Cunha', '3520', 'Vila Bom Princípio', 'Cachoeirinha', 'RS', 0, '2026-04-23 13:32:04', 16, 17, '2026-04-23 13:32:04', 0, NULL, NULL),
(94, 'Swift Car', 'SWIFT CAR COMERCIO E INTERMEDIACOES DE VEICULOS LTDA', 2, 0, '58.147.917/0001-01', '5199906383', 'swiftcargravatai@gmail.com', '', '94175-032', 'Estrada Vânius Abílio dos Santos', '435', 'Recanto Corcunda', 'Gravataí', 'RS', 0, '2026-04-23 13:35:11', 16, 18, '2026-04-23 13:35:11', 0, NULL, NULL),
(95, 'VICTOR LUCAS SORMANI GARCIA', '', 4, 0, '050.810.030-50', '(51) 98616-5479', '', '', '', '', '', '', '', '', 0, '2026-04-23 14:06:17', 16, 19, '2026-04-23 14:06:17', 0, NULL, NULL),
(96, 'JOSE LEONAN DA SILVA CARVALHO', '', 4, 0, '126.683.397-82', '(22) 99811-1411', '', '', '', '', '', '', '', '', 0, '2026-04-23 14:17:32', 16, 20, '2026-04-23 14:17:32', 15, NULL, NULL),
(97, 'ANDERSON DE FREITAS RAEL', '', 1, 0, '007.559.950-31', '', '', '', '94955-190', 'Rua Jacarandá', '71', 'Vila Anair', 'Cachoeirinha', 'RS', 0, '2026-04-23 14:26:41', 16, 21, '2026-04-23 14:26:41', 15, NULL, NULL),
(98, 'EDERSON OLIVEIRA DA SILVA', '', 2, 0, '022.780.700-61', '(51) 98627-0804', '', '', '94070-001', 'Avenida Dorival Cândido Luz de Oliveira', '7871', 'Bom Princípio', 'Gravataí', 'RS', 0, '2026-04-23 14:42:48', 16, 22, '2026-04-23 14:42:48', 0, NULL, NULL),
(99, 'CAIO ANDRES CLAVARIO', '', 4, 0, '025.036.530-81', '(51) 99758-3026', '', '', '', '', '', '', '', '', 0, '2026-04-24 11:59:36', 16, 23, '2026-04-24 11:59:36', 0, NULL, NULL),
(101, 'MICHEL DAMASCENO DA SILVA', '', 2, 0, '008.870.930-27', '', '', '', '', '', '', '', '', '', 0, '2026-04-24 12:06:08', 16, 25, '2026-04-24 12:06:08', 0, NULL, NULL),
(102, 'ADIMILSON DE SOUZA', '', 4, 0, '002.936.770-00', '', '', '', '', '', '', '', '', '', 0, '2026-04-24 12:12:41', 16, 26, '2026-04-24 12:12:41', 0, NULL, NULL),
(103, 'VAGNER VR', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-24 12:15:08', 16, 27, '2026-04-24 12:15:08', 0, NULL, NULL),
(104, 'CARLOS HENRIQUE DICK', '', 4, 0, '601.877.370-88', '(51) 99348-6223', '', '', '', '', '', '', '', '', 0, '2026-04-24 12:22:07', 16, 28, '2026-04-24 12:22:07', 0, NULL, NULL),
(105, 'JEAN CLEBERSON DOS SANTOS RAMBORGER', '', 4, 0, '012.054.520-95', '(51) 99667-9398', '', '', '', '', '', '', '', '', 0, '2026-04-24 12:29:29', 16, 29, '2026-04-24 12:29:29', 0, NULL, NULL),
(106, 'ISAIAS ALVES DE SOUZA', '', 4, 0, '033.708.290-10', '(51) 99364-3038', '', '', '94075-000', 'Avenida Senador Teotônio Vilela', '180', 'Parque Florido', 'Gravataí', 'RS', 0, '2026-04-24 16:08:50', 16, 30, '2026-04-24 16:08:50', 0, NULL, NULL),
(107, 'DANIEL DOS SANTOS LINCK', '', 2, 0, '019.605.180-03', '(51) 99689-6532', '', '', '', '', '', '', '', '', 0, '2026-04-24 16:11:03', 16, 31, '2026-04-24 16:11:03', 0, NULL, NULL),
(108, 'MARCIO JOSEFINO EUGENIO', '', 4, 0, '951.119.210-87', '(51) 98184-6367', '', '', '', '', '', '', '', 'SC', 0, '2026-04-24 16:15:09', 16, 32, '2026-04-24 16:15:09', 0, NULL, NULL),
(109, 'JEFFERSON DOS SANTOS ABLO', '', 4, 0, '013.570.230-57', '(51) 98440-0850', '', '', '94960-170', 'Rua Dona Isaura', '232', 'Parque Granja Esperança', 'Cachoeirinha', 'RS', 0, '2026-04-24 16:18:57', 16, 33, '2026-04-24 16:18:57', 0, NULL, NULL),
(110, 'DOUGLAS BERTOL ZAMBIASI', '', 4, 0, '834.306.950-15', '(51) 98496-0352', '', '', '94080-430', 'Rua Acelino de Carvalho', '46', 'Morada do Vale I', 'Gravataí', 'RS', 0, '2026-04-24 17:29:58', 16, 34, '2026-04-24 17:29:58', 0, NULL, NULL),
(111, 'JOSE ROBERTO MASIEL SOUZA', '', 4, 0, '034.933.640-75', '(51) 98958-1061', '', '', '94950-280', 'Rua Monteiro Lobato', '717', 'Parque da Matriz', 'Cachoeirinha', 'RS', 0, '2026-04-24 17:32:54', 16, 35, '2026-04-24 17:32:54', 0, NULL, NULL),
(112, 'ALEXSANDER PRESTES MORAIS', '', 4, 0, '017.735.820-38', '(51) 99524-9980', '', '', '94070-290', 'Rua Palmares', '204', 'Loteamento Vila Rica', 'Gravataí', 'RS', 0, '2026-04-24 17:36:28', 16, 36, '2026-04-24 17:36:28', 0, NULL, NULL),
(113, 'DANIELA TONI SCHEFFER', '', 4, 0, '962.271.330-00', '(51) 99453-9018', '', '', '90020-013', 'Rua dos Andradas', '1739', 'Centro Histórico', 'Porto Alegre', 'RS', 0, '2026-04-24 18:17:51', 16, 37, '2026-04-24 18:17:51', 15, NULL, NULL),
(114, 'PATRICIA FRANCO DE QUADROS', '', 4, 0, '935.255.160-53', '(51) 99287-5012', '', '', '', '', '', '', '', '', 0, '2026-04-24 17:42:54', 16, 38, '2026-04-24 17:42:54', 15, NULL, NULL),
(115, 'GLEICON DE OLIVEIRA PAIVA', '', 4, 0, '986.365.960-68', '(51) 99183-2169', '', '', '94065-200', 'Rua Guilherme Schmitz', '2301', 'Parque Olinda', 'Gravataí', 'RS', 0, '2026-04-24 17:45:08', 16, 39, '2026-04-24 17:45:08', 15, NULL, NULL),
(116, 'MATHEUS DE OLIVEIRA SARAIVA', '', 4, 0, '600.063.530-37', '(51) 99849-1470', '', '', '91778-050', 'Rua das Espatódeas', '434', 'Ponta Grossa', 'Porto Alegre', 'RS', 0, '2026-04-24 17:46:41', 16, 40, '2026-04-24 17:46:41', 15, NULL, NULL),
(117, 'LEONARDO DA SILVA BANDEIRA', '', 4, 0, '035.894.890-81', '(51) 98499-9679', '', '', '94070-040', 'Rua Alagoinhas', '299', 'Bom Princípio', 'Gravataí', 'RS', 0, '2026-04-24 17:48:17', 16, 41, '2026-04-24 17:48:17', 15, NULL, NULL),
(118, 'OSMAR CARDOSO PIECHA', '', 4, 0, '006.080.910-81', '(51) 98158-5591', '', '', '94960-854', 'Rua José Amaro', '73', 'Morada do Bosque', 'Cachoeirinha', 'RS', 0, '2026-04-24 17:49:46', 16, 42, '2026-04-24 17:49:46', 0, NULL, NULL),
(119, 'LEONARDO MACEDO SOUZA', '', 4, 0, '015.997.860-28', '(51) 99163-5960', '', '', '', '', '', '', '', '', 0, '2026-04-24 17:53:07', 16, 43, '2026-04-24 17:53:07', 0, NULL, NULL),
(120, 'GILMAR JOSE BORBA VIEIRA', '', 4, 0, '395.700.960-04', '(51) 99955-9209', '', '', '94950-080', 'Rua Botafogo', '1158', 'Parque da Matriz', 'Cachoeirinha', 'RS', 0, '2026-04-24 17:56:16', 16, 44, '2026-04-24 17:56:16', 0, NULL, NULL),
(121, 'MARIANNA DE AGUIAR TESCH', '', 4, 0, '047.538.980-83', '(51) 99714-0646', '', '', '94110-030', 'Rua Adams Filho', '21', 'Parque Ipiranga', 'Gravataí', 'RS', 0, '2026-04-24 17:58:00', 16, 45, '2026-04-24 17:58:00', 15, NULL, NULL),
(122, 'JOBEM BLADIMIR AVILA DE SOUZA', '', 4, 0, '973.794.680-49', '(51) 99100-4692', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:07:29', 16, 46, '2026-04-24 18:07:29', 0, NULL, NULL),
(123, 'ELOIR FLORES DA SILVA', '', 4, 0, '993.819.230-00', '(51) 99168-0097', '', '', '94100-650', 'Rua Floriano Garcia', '184', 'Águas Mortas', 'Gravataí', 'RS', 0, '2026-04-24 18:09:06', 16, 47, '2026-04-24 18:09:06', 15, NULL, NULL),
(124, 'JANINE STEFANY OLIVEIRA DE SOUZA', '', 4, 0, '052.332.925-30', '(51) 99105-9396', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:10:23', 16, 48, '2026-04-24 18:10:23', 15, NULL, NULL),
(125, 'CLEBER PEREIRA EINSFELDT ', '', 4, 0, '600.866.700-00', '(51) 99363-1835', '', '', '94960-410', 'Rua Mário Teixeira de Souza', '280', 'Parque Granja Esperança', 'Cachoeirinha', 'RS', 0, '2026-04-24 18:14:59', 16, 49, '2026-04-24 18:14:59', 0, NULL, NULL),
(126, 'GUILHERME MENDES DA SILVA', '', 4, 0, '601.072.630-17', '(51) 98400-0102', '', '', '94240-606', 'Rua Vicente Celestino', '126', 'Rural Palermo (Itacolomi)', 'Gravataí', 'RS', 0, '2026-04-24 18:19:47', 16, 50, '2026-04-24 18:19:47', 0, NULL, NULL),
(127, 'GELSON CORREA DE BORBA', '', 4, 0, '041.524.430-73', '(51) 99134-1866', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:24:37', 16, 51, '2026-04-24 18:24:37', 0, NULL, NULL),
(128, 'WELINTON DA SILVA VIEIRA', '', 4, 0, '042.901.220-94', '(51) 99181-5907', '', '', '94967-388', 'Rua Vitória Régia', '37', 'Chácara das Rosas', 'Cachoeirinha', 'RS', 0, '2026-04-24 18:26:35', 16, 52, '2026-04-24 18:26:35', 15, NULL, NULL),
(129, 'JANAINA MARCELINO FARIAS', '', 4, 0, '002.053.830-84', '(51) 98923-7226', '', '', '94960-555', 'Rua Alamo', '80 C 2', 'Jardim do Bosque', 'Cachoeirinha', 'RS', 0, '2026-04-24 18:28:08', 16, 53, '2026-04-24 18:28:08', 15, NULL, NULL),
(130, 'JAIRO DA FONSECA LIMA', '', 4, 0, '379.477.700-00', '(51) 98050-9245', '', '', '94060-600', 'Rua Tibúrcio Oliveira', '360', 'Novo Mundo', 'Gravataí', 'RS', 0, '2026-04-24 18:29:39', 16, 54, '2026-04-24 18:29:39', 15, NULL, NULL),
(131, 'DIONATAN LEMES ZALEVSKI', '', 4, 0, '601.491.490-00', '(51) 99811-2423', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:30:35', 16, 55, '2026-04-24 18:30:35', 0, NULL, NULL),
(132, 'PABLO RENAN ALMEIDA VIEIRA', '', 4, 0, '039.498.250-96', '(51) 99776-1635', '', '', '', '', '', 'MORUNGAVA', '', '', 0, '2026-04-24 18:31:54', 16, 56, '2026-04-24 18:31:54', 0, NULL, NULL),
(133, 'RENATO ARGENTA', '', 2, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:32:23', 16, 57, '2026-04-24 18:32:23', 0, NULL, NULL),
(134, 'CARLOS', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:34:05', 16, 58, '2026-04-24 18:34:05', 0, NULL, NULL),
(135, 'MARCELO', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-24 18:34:14', 16, 59, '2026-04-24 18:34:14', 0, NULL, NULL),
(136, 'JOAO VITOR BACK DE OLIVEIRA', '', 4, 0, '050.723.900-80', '(51) 98224-7607', '', '', '', '', '', '', '', '', 0, '2026-04-26 16:54:49', 16, 60, '2026-04-26 16:54:49', 0, NULL, NULL),
(137, 'JOAO VITOR NUNES DOS PASSOS', '', 4, 0, '047.035.980-35', '(51) 99500-9835', '', '', '94150-650', 'Rua América', '180', 'São Geraldo', 'Gravataí', 'RS', 0, '2026-04-26 17:18:18', 16, 61, '2026-04-26 17:18:18', 0, NULL, NULL),
(138, 'LUIZA WARTTMANN BAPTISTA WACHTEL', '', 1, 0, '062.504.940-30', '', '', '', '', '', '', '', '', '', 0, '2026-04-27 09:12:53', 16, 62, '2026-04-27 09:12:53', 0, NULL, NULL),
(139, 'LUIS ANTONIO RODRIGUES DE AQUINO', '', 1, 0, '738.654.270-49', '', '', '', '', '', '', '', '', '', 0, '2026-04-27 09:15:31', 16, 63, '2026-04-27 09:15:31', 0, NULL, NULL),
(140, 'EVERTON MACHADO SEBASTIAO ', '', 2, 0, '003.020.130-67', '(51) 99238-9647', '', '', '', '', '', '', '', '', 0, '2026-04-27 09:18:25', 16, 64, '2026-04-27 09:18:25', 0, NULL, NULL),
(141, 'FERNANDA GONZALES DE JESUS', '', 1, 0, '814.367.340-53', '', '', '', '', '', '', '', '', '', 0, '2026-04-27 10:09:03', 16, 65, '2026-04-27 10:09:03', 0, NULL, NULL),
(142, 'LUANA FRIEDRICHS SALLA', '', 4, 0, '041.402.590-35', '', '', '', '94170-052', 'Rua Antares', '54', 'Santa Cruz', 'Gravataí', 'RS', 0, '2026-04-27 10:36:59', 16, 66, '2026-04-27 10:36:59', 15, NULL, NULL),
(143, 'KADU CONSTRUCOES E EMPREENDIMENTOS LTDA', 'KADU CONSTRUCOES E EMPREENDIMENTOS LTDA', 1, 0, '20.096.941/0001-88', '5199999999', 'email@email.com', '', '', 'Rua Dezesseis de Fevereiro', '85', 'Ponta Pora', 'Cachoeirinha', 'RS', 0, '2026-04-27 11:46:17', 16, 67, '2026-04-27 11:46:17', 0, NULL, NULL),
(144, 'BRUNO TRAJANO GUIMARAES DOS PASSOS', '', 4, 0, '040.578.523-28', '(51) 99797-2067', '', '', '', '', '', '', '', '', 0, '2026-04-27 17:00:58', 16, 68, '2026-04-27 17:00:58', 0, NULL, NULL),
(145, 'FABRICIO PALIO LEILAO', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-27 17:04:08', 16, 69, '2026-04-27 17:04:08', 0, NULL, NULL),
(146, 'GABRIEL PENTZ', '', 4, 0, '865.152.410-20', '(51) 98910-1515', '', '', '90010-260', 'Rua Caldas Júnior', '48-44', 'Centro Histórico', 'Porto Alegre', 'RS', 0, '2026-04-27 17:37:36', 16, 70, '2026-04-27 17:37:36', 0, NULL, NULL),
(147, 'SILVIO CESAR MELLO', '', 2, 0, '487.757.380-15', '(51) 99114-7127', '', '', '', '', '', '', '', '', 0, '2026-04-27 17:39:27', 16, 71, '2026-04-27 17:39:27', 0, NULL, NULL),
(148, 'PRISCILA NAVEGANTES NUNES DE LIMA', '', 4, 0, '807.429.550-87', '(51) 98227-8854', '', '', '94100-170', 'Rua Maranhão', '63', 'Neópolis', 'Gravataí', 'RS', 0, '2026-04-27 17:49:55', 16, 72, '2026-04-27 17:49:55', 0, NULL, NULL),
(149, 'LUIZA WARTTMANN BAPTISTA WACHTEL', '', 4, 0, '062.504.940-30', '(51) 99331-8605', '', '', '92480-000', 'VENEZE', '1184', 'BERTO CIRIO', 'Nova Santa Rita', 'RS', 0, '2026-04-27 17:56:16', 16, 73, '2026-04-27 17:56:16', 15, NULL, NULL),
(150, 'FERNANDA LORENA PINHEIRO SENA', '', 4, 0, '073.115.232-81', '(51) 99380-3839', '', '', '', '', '', '', '', '', 0, '2026-04-27 19:30:32', 16, 74, '2026-04-27 19:30:32', 15, NULL, NULL),
(151, 'CHRISTHOFER DA SILVA E SILVA', '', 4, 0, '032.006.340-22', '(51) 99127-8148', '', '', '94035-130', 'Travessa Lindóia', '139', 'Passo das Pedras', 'Gravataí', 'RS', 0, '2026-04-27 19:31:57', 16, 75, '2026-04-27 19:31:57', 15, NULL, NULL),
(161, 'Compumate Softwares Corporativos', 'COMPUMATE SOFTWARES CORPORATIVOS LTDA', 4, 0, '92.343.375/0001-72', '5137415144', 'asconass@viavale.com.br', 'TESTE DO LUCAS', '', 'Rua Emilio Selbach', '825', 'Centro', 'Venâncio Aires', 'RS', 0, '2026-05-05 00:25:54', 11, 32, '2026-05-05 00:25:54', 0, 4, 'Presidente do Brasil'),
(162, 'ANDRESSA SANTOS DA SILVA', '', 1, 0, '026.394.170-10', '', '', '', '', '', '', '', '', '', 0, '2026-04-28 08:57:35', 16, 76, '2026-04-28 08:57:35', 0, NULL, NULL),
(164, 'DAVID DA SILVA DUARTE', '', 4, 0, '038.410.400-20', '(51) 99736-8527', '', '', '94090-050', 'Rua Elvis Presley', '214', 'Vera Cruz', 'Gravataí', 'RS', 0, '2026-04-28 19:00:02', 16, 78, '2026-04-28 19:00:02', 0, 0, ''),
(166, 'RODRIGO LUIZ DUARTE DOS SANTOS', '', 4, 0, '958.403.200-30', '', '', '', '', '', '', '', '', '', 0, '2026-04-28 19:29:18', 16, 80, '2026-04-28 19:29:18', 0, 0, ''),
(167, 'ANDRE OLIVEIRA SEVERO', '', 4, 0, '026.061.160-38', '(51) 98137-2907', '', '', '94035-210', 'Travessa Herbert', '26 TORRE 3 AP 306', 'Passo das Pedras', 'Gravataí', 'RS', 0, '2026-04-29 19:08:23', 16, 81, '2026-04-29 19:08:23', 0, 0, ''),
(168, 'GABRIEL ROSA DOS SANTOS', '', 1, 0, '047.311.030-02', '', '', '', '', '', '', '', '', '', 0, '2026-04-29 21:23:07', 16, 82, '2026-04-29 21:23:07', 0, 0, ''),
(169, 'SILVANA ALVES MAIER', '', 4, 0, '962.818.630-20', '(51) 99784-1706', '', '', '94080-180', 'Rua Alcides Maia', '295', 'Morada do Vale III', 'Gravataí', 'RS', 0, '2026-04-29 21:45:28', 16, 83, '2026-04-29 21:45:28', 0, 0, ''),
(170, 'ANDERSON DOS SANTOS SELVA', '', 4, 0, '042.112.390-74', '(51) 99708-8196', '', '', '94070-300', 'Rua Lagoa dos Patos', '160', 'Loteamento Vila Rica', 'Gravataí', 'RS', 0, '2026-04-29 21:51:21', 16, 84, '2026-04-29 21:51:21', 15, 0, ''),
(174, 'DIOGENES DIAS MARCON', '', 4, 0, '031.733.890-00', '', '', '', '', '', '', '', '', '', 0, '2026-04-30 18:27:17', 16, 85, '2026-04-30 18:27:17', 0, 0, ''),
(175, 'ALEXANDRE HENRIQUE NASCIMENTO', '', 4, 0, '918.136.467-91', '', '', '', '', '', '', '', '', '', 0, '2026-04-30 18:34:24', 16, 86, '2026-04-30 18:34:24', 0, 0, ''),
(176, 'LUIS HENRIQUE DIEDRICH', '', 4, 0, '517.349.580-49', '(51) 99503-9117', '', '', '', '', '', '', '', '', 0, '2026-04-30 19:31:54', 16, 87, '2026-04-30 19:31:54', 0, 0, ''),
(177, 'NYKOLLAS CARDOSO DA SILVA', '', 1, 0, '', '(51) 98107-9310', '', '', '', '', '', '', '', '', 0, '2026-04-30 19:39:17', 16, 88, '2026-04-30 19:39:17', 0, 0, ''),
(178, 'ANGELA DESP TOYOTA SC', '', 1, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-04-30 20:01:06', 16, 89, '2026-04-30 20:01:06', 0, 0, ''),
(179, 'CLEBER CELTA', '', 1, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 11:02:55', 16, 90, '2026-05-04 11:02:55', 0, 0, ''),
(180, 'CLEBER CELTA', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 11:03:23', 16, 91, '2026-05-04 11:03:23', 0, 0, ''),
(181, 'CLEBER CELTA', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 11:03:47', 16, 92, '2026-05-04 11:03:47', 0, 0, ''),
(182, 'CLEBER CELTA', '', 4, 0, '023.772.590-81', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 11:04:04', 16, 93, '2026-05-04 11:04:04', 0, 0, ''),
(183, 'CLEBER CELTA', '', 4, 0, '023.772.590-81', '', '', '', '94480-620', 'Rua Recreio', '56', 'Santa Isabel', 'Viamão', 'RS', 0, '2026-05-04 11:04:45', 16, 94, '2026-05-04 11:04:45', 0, 1, 'AUTONOMO'),
(184, 'CLEBER CELTA', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 11:05:16', 16, 95, '2026-05-04 11:05:16', 0, 0, ''),
(185, 'CLEBER CELTA', '', 4, 0, '023.772.590-81', '', '', '', '94480-620', 'Rua Recreio', '56', 'Santa Isabel', 'Viamão', 'RS', 0, '2026-05-04 11:05:24', 16, 96, '2026-05-04 11:05:24', 0, 1, 'AUTONOMO'),
(186, 'CLEBER CELTA', '', 4, 0, '', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 11:07:23', 16, 97, '2026-05-04 11:07:23', 0, 0, ''),
(187, 'CLEBER LEAL', '', 1, 0, '011.898.410-10', '(51) 99632-7804', '', '', '', '', '', '', '', '', 0, '2026-05-04 12:26:07', 16, 98, '2026-05-04 12:26:07', 0, 0, ''),
(188, 'CLEBER LEAL', '', 1, 0, '011.898.410-10', '', '', '', '', '', '', '', '', '', 0, '2026-05-04 18:32:49', 16, 99, '2026-05-04 18:32:49', 0, 0, '');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
