-- TABELA patrimonio

DROP TABLE IF EXISTS `patrimonio`;
CREATE TABLE `patrimonio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int NOT NULL,
  `descricao` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `valor` decimal(12,2) NOT NULL,
  `dataCompra` date NOT NULL,
  `dataAlt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idEmpresa` (`idEmpresa`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('10','11','Moto vermelha','4000.00','2026-03-01','2026-03-09 15:12:40');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('11','11','Moto 2026','16500.00','2026-03-09','2026-03-09 15:12:53');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('12','11','Mesas Novas','2200.00','2026-03-12','2026-03-16 17:16:56');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('13','16','Sofá Recepcão','962.03','2026-05-02','2026-05-02 18:55:19');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('14','16','Porta de Vidro','1950.00','2025-12-02','2026-05-02 18:57:54');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('15','16','Ar Condicionado Principal','2500.00','2026-01-03','2026-05-02 18:58:26');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('16','16','Cadeiras','1680.00','2025-12-09','2026-05-02 18:59:30');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('17','16','Pelicula Porta de Vidro','500.00','2026-12-18','2026-05-02 19:00:40');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('18','16','2 Computadores ','1977.89','2025-12-18','2026-05-02 19:01:08');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('19','16','Scanner e 2 Monitores','2568.78','2025-12-18','2026-05-02 19:01:47');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('20','16','Cameras e Alarme','1271.65','2026-01-13','2026-05-02 19:04:04');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('21','16','Balcão Pia ','200.00','2026-01-21','2026-05-02 19:05:02');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('22','16','Frigobar','759.00','2026-01-31','2026-05-02 19:05:22');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('23','16','Cafeteria + Estrutura Banheiro','1227.73','2026-05-02','2026-05-02 19:06:09');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('24','16','Fachada ACM','4345.00','2026-01-04','2026-05-02 19:07:05');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('25','16','Móveis Escritório','11000.00','2026-12-05','2026-05-02 19:09:55');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('26','16','2 monitores','1095.02','2026-04-05','2026-05-02 19:11:15');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('27','16','Computador Yan','1500.00','2026-04-05','2026-05-02 19:11:38');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('28','16','Corsa Prata MBK5J73','13900.00','2026-05-02','2026-05-02 19:12:46');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('29','16','CG 150 Vermelha 2009 IPS8I35','10900.00','2026-05-02','2026-05-02 19:13:23');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('30','11','Moto ton','3000.00','2026-05-04','2026-05-04 23:14:12');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('31','11','Fit','35000.00','2026-05-04','2026-05-04 23:14:40');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('32','11','Parati','8000.00','2026-05-04','2026-05-04 23:15:03');
INSERT INTO `patrimonio` (`id`,`idEmpresa`,`descricao`,`valor`,`dataCompra`,`dataAlt`) VALUES ('33','11','Uno escritório','10000.00','2026-05-04','2026-05-04 23:15:19');
