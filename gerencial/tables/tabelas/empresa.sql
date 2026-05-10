-- TABELA empresa

DROP TABLE IF EXISTS `empresa`;
CREATE TABLE `empresa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Documento` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Telefone` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Plano` int DEFAULT NULL,
  `Status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'PENDENTE',
  `DataCadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `ValidadePlano` date DEFAULT NULL,
  `MetaMensal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `MetaDiaria` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `empresa` (`id`,`Nome`,`Documento`,`Telefone`,`Email`,`Plano`,`Status`,`DataCadastro`,`ValidadePlano`,`MetaMensal`,`MetaDiaria`) VALUES ('10','AUTODOC BRASIL LTDA','62.424.394/0001-62','','autodocva@gmail.com','3','ATIVA','2026-03-03 16:54:45','2026-04-02','0.00','0.00');
INSERT INTO `empresa` (`id`,`Nome`,`Documento`,`Telefone`,`Email`,`Plano`,`Status`,`DataCadastro`,`ValidadePlano`,`MetaMensal`,`MetaDiaria`) VALUES ('11','Escritório e Despachante de Trânsito Allgayer','019.850.840-90','','despachanteallgayer@gmail.com','3','ATIVA','2026-03-03 22:37:55','2100-01-01','48000.00','2400.00');
INSERT INTO `empresa` (`id`,`Nome`,`Documento`,`Telefone`,`Email`,`Plano`,`Status`,`DataCadastro`,`ValidadePlano`,`MetaMensal`,`MetaDiaria`) VALUES ('14','Matheus Portaluppi','043.093.000-30','(51) 99543-2617','matheusportaluppi09@gmail.com','1','PENDENTE','2026-03-18 10:31:43',NULL,'0.00','0.00');
INSERT INTO `empresa` (`id`,`Nome`,`Documento`,`Telefone`,`Email`,`Plano`,`Status`,`DataCadastro`,`ValidadePlano`,`MetaMensal`,`MetaDiaria`) VALUES ('16','JET DESPACHANTE','41.444.657/0001-25','(51) 98636-4373','jetdespachanters@gmail.com','2','ATIVA','2026-04-20 12:34:15','2026-05-20','0.00','0.00');
INSERT INTO `empresa` (`id`,`Nome`,`Documento`,`Telefone`,`Email`,`Plano`,`Status`,`DataCadastro`,`ValidadePlano`,`MetaMensal`,`MetaDiaria`) VALUES ('17','Lulu10','025.742.990-51','(51) 99605-3990','luana_schwengber@outlook.com','1','ATIVA','2026-05-05 22:47:40','2026-06-04','0.00','0.00');
