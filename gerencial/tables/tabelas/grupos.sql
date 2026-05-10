-- TABELA grupos

DROP TABLE IF EXISTS `grupos`;
CREATE TABLE `grupos` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(150) DEFAULT NULL,
  `Tipo` char(1) DEFAULT NULL,
  `Inativo` tinyint(1) DEFAULT NULL,
  `idEmpresa` int DEFAULT NULL,
  `DataCadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('8','TRANSFERÊNCIAS GERAL','P','0','11','2026-03-19 22:52:35');
INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('10','2° VIA GERAL','P','0','11','2026-03-19 22:55:22');
INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('11','SOLICITAÇÃO DE VISTORIA GERAL','P','0','11','2026-03-19 22:56:00');
INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('12','GRUPO DAS REVENDAS','C','0','11','2026-03-19 22:57:27');
INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('14','MULTAS','P','0','16','2026-04-22 07:49:49');
INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('15','CLIENTE REPASUL','C','0','16','2026-04-22 14:02:35');
INSERT INTO `grupos` (`Id`,`Nome`,`Tipo`,`Inativo`,`idEmpresa`,`DataCadastro`) VALUES ('16','FINAL','C','0','11','2026-04-27 17:55:37');
