-- TABELA planos

DROP TABLE IF EXISTS `planos`;
CREATE TABLE `planos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Valor` decimal(10,2) DEFAULT NULL,
  `Periodo` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Descricao` text COLLATE utf8mb4_general_ci,
  `Status` tinyint DEFAULT '1',
  `DataCadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `planos` (`id`,`Nome`,`Valor`,`Periodo`,`Descricao`,`Status`,`DataCadastro`) VALUES ('1','Plano Mensal','49.90','MENSAL','Acesso completo ao sistema com cobrança mensal.','1','2026-02-22 14:30:10');
INSERT INTO `planos` (`id`,`Nome`,`Valor`,`Periodo`,`Descricao`,`Status`,`DataCadastro`) VALUES ('2','Plano Trimestral','135.90','TRIMESTRAL','Plano com melhor custo-benefício para 3 meses.','1','2026-02-22 14:30:10');
INSERT INTO `planos` (`id`,`Nome`,`Valor`,`Periodo`,`Descricao`,`Status`,`DataCadastro`) VALUES ('3','Plano Anual','480.00','ANUAL','Maior economia com acesso por 12 meses.','1','2026-02-22 14:30:10');
