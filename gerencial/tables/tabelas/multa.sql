-- TABELA multa

DROP TABLE IF EXISTS `multa`;
CREATE TABLE `multa` (
  `id` int NOT NULL AUTO_INCREMENT,
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
  `EnviarLembrete` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Forcli` (`Forcli`),
  CONSTRAINT `multa_ibfk_1` FOREIGN KEY (`Forcli`) REFERENCES `forcli` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `multa` (`id`,`idEmpresa`,`Forcli`,`DataCadastro`,`DataAlt`,`SerieMulta`,`CodigoProcesso`,`OrgaoFiscalizador`,`PlacaVeiculo`,`PlacasAdicionais`,`RegistroCNH`,`PrazoDefesa`,`AutoSuspensiva`,`RecursoMulta`,`StatusMulta`,`Observacao`,`Inativo`,`idUser`,`UserAlt`,`EnviarLembrete`) VALUES ('5','11','161','2026-05-05 00:00:00','2026-05-05 01:28:43','IDX938POP3','129217472','PRF','IXT-90566','','3292758238','2026-05-19','1','1','4','TESTE DO LUCAS','0','32','32','1');
INSERT INTO `multa` (`id`,`idEmpresa`,`Forcli`,`DataCadastro`,`DataAlt`,`SerieMulta`,`CodigoProcesso`,`OrgaoFiscalizador`,`PlacaVeiculo`,`PlacasAdicionais`,`RegistroCNH`,`PrazoDefesa`,`AutoSuspensiva`,`RecursoMulta`,`StatusMulta`,`Observacao`,`Inativo`,`idUser`,`UserAlt`,`EnviarLembrete`) VALUES ('9','16','194','2026-05-06 00:00:00','2026-05-06 12:58:09','DL00337626','','','PYG5656','','','2026-05-11','0','0','0','RECCORER NA CNH DA JACQUELINE TERESINHA GONCALVES CPF:910.668.750-49','0','30','30','1');
