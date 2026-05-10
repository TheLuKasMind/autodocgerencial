-- TABELA userpermissoes

DROP TABLE IF EXISTS `userpermissoes`;
CREATE TABLE `userpermissoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `idEmpresa` int NOT NULL,
  `idUsuario` int NOT NULL,
  `pagina` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

