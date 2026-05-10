-- TABELA tipodespesa

DROP TABLE IF EXISTS `tipodespesa`;
CREATE TABLE `tipodespesa` (
  `idEmpresa` int NOT NULL,
  `Categoria` int NOT NULL,
  `Acao` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `Inativo` int NOT NULL,
  `id` int NOT NULL AUTO_INCREMENT,
  `Descricao` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `ValorBase` decimal(10,2) NOT NULL,
  `Codigo` int NOT NULL,
  `Nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `DataAlt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','20','ALUGUEL','1518.00','1','ALUGUEL','2026-03-03 23:21:32');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','22','INTERNET','0.00','2','INTERNET','2026-03-09 22:16:27');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','23','LUZ','0.00','3','LUZ','2026-03-09 22:16:33');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','24','SALÁRIO LUIZ','0.00','4','SALÁRIO LUIZ','2026-03-09 22:16:39');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','25','SALÁRIO TON','11000.00','5','SALÁRIO TON','2026-03-09 22:16:50');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','26','SALÁRIO THIAGO','11000.00','6','SALÁRIO THIAGO','2026-03-09 22:16:59');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','27','GASOLINA','0.00','7','GASOLINA','2026-03-09 22:17:48');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','28','CRDD','0.00','8','CRDD','2026-03-09 22:20:24');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','29','MARKETING','0.00','9','MARKETING','2026-03-09 22:20:42');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','30','ESCRITÓRIO','0.00','10','ESCRITÓRIO','2026-03-09 22:20:54');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','31','MERCADO','0.00','11','MERCADO','2026-03-09 22:21:00');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','32','GRÁFICA','0.00','12','GRÁFICA','2026-03-09 22:21:11');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','33','ÁGUA','0.00','13','ÁGUA','2026-05-10 17:26:58');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','34','LIMPEZA','0.00','14','LIMPEZA','2026-03-09 22:22:31');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','35','TAXA CRVA','0.00','15','TAXA CRVA','2026-04-01 14:26:15');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','0','0','36','TAXA CARTÓRIO','0.00','16','TAXA CARTÓRIO','2026-03-14 14:42:01');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','37','IPTU','63.43','17','IPTU','2026-04-02 09:50:13');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','38','GRT','0.00','18','GRT','2026-04-02 09:50:35');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','2','-1','0','39','PERDA','0.00','19','PERDA','2026-04-02 09:50:42');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','40','IMPOSTOS','0.00','20','IMPOSTOS','2026-04-06 16:40:36');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','41','CARTAO','0.00','21','CARTAO','2026-04-06 16:41:33');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','42','SISTEMA','0.00','22','SISTEMA','2026-04-07 14:35:13');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','43','CORREIO','0.00','23','CORREIO','2026-04-10 10:20:54');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','44','CONSERTO MOTO','0.00','24','CONSERTO MOTO','2026-04-16 10:51:30');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','45','ABERTURAS DETRAN','0.00','1','ABERTURAS DETRAN','2026-04-20 17:13:30');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','1','-1','0','46','CONTA TELEFONE','0.00','2','CONTA TELEFONE','2026-04-20 17:19:09');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','47','RECARGA SISTEMA DESP','0.00','3','RECARGA SISTEMA DESP','2026-04-20 17:20:04');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','48','ALMOÇO TIA','0.00','4','ALMOÇO TIA','2026-04-20 17:20:14');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','49','COMBUSTIVEL','0.00','5','COMBUSTIVEL','2026-04-20 17:20:24');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','50','CORREIOS','0.00','6','CORREIOS','2026-04-20 17:20:34');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','51','INSUMOS ESCRITÓRIO LIMPEZA/ALIMENTO','0.00','7','INSUMOS ESCRITÓRIO LIMPEZA/ALIMENTO','2026-04-20 17:20:59');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','52','UNIFORMES','0.00','8','UNIFORMES','2026-04-20 17:21:46');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','53','MANUTENÇÃO PC','0.00','9','MANUTENÇÃO PC','2026-04-20 17:22:10');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','54','ESTRUTURA ESCRITÓRIO','0.00','10','ESTRUTURA ESCRITÓRIO','2026-04-20 17:22:44');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','55','ALVARA','0.00','11','ALVARA','2026-04-20 17:22:56');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','1','-1','0','56','LUZ','0.00','12','LUZ','2026-04-20 17:23:03');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','57','VISTORIAS','0.00','13','VISTORIAS','2026-04-20 17:35:40');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','58','SIAC','0.00','14','SIAC','2026-04-21 14:58:20');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','60','GRT','0.00','16','GRT','2026-04-21 15:02:23');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','61','Fixo Andria','0.00','17','Fixo Andria','2026-05-01 13:27:03');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','62','Salário Michel','0.00','18','Salário Michel','2026-05-01 13:47:47');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','63','Salário Mauro','0.00','19','Salário Mauro','2026-05-01 13:37:00');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','64','PRÓ-LABORE ','0.00','20','PRÓ-LABORE ','2026-04-21 15:05:29');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','65','CRDD','0.00','21','CRDD','2026-04-21 15:49:30');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','66','Manutenção Motos','0.00','25','Manutenção Motos','2026-04-23 08:51:40');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','68','GASOLINA','0.00','22','GASOLINA','2026-04-28 15:00:22');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','69','Digital','50.00','23','Digital','2026-05-04 15:45:31');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','70','FATURA CARTÃO PJ','0.00','24','FATURA CARTÃO PJ','2026-04-29 07:30:31');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','71','AGUA CORSAN','0.00','25','AGUA CORSAN','2026-04-29 07:38:47');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','72','UBER','0.00','26','UBER','2026-04-29 07:46:25');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','73','INTERNET ESCRITÓRIO','0.00','27','INTERNET ESCRITÓRIO','2026-04-29 07:47:14');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','74','IPVA','0.00','26','IPVA','2026-04-30 13:40:53');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','75','Comissões Andria','0.00','28','Comissões Andria','2026-05-01 13:27:37');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','1','-1','0','76','Aluguel','0.00','29','Aluguel','2026-05-04 18:31:02');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','77','Investimento','0.00','30','Investimento','2026-05-06 18:31:15');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('16','2','-1','0','78','Tráfego Pago','0.00','31','Tráfego Pago','2026-05-06 18:32:53');
INSERT INTO `tipodespesa` (`idEmpresa`,`Categoria`,`Acao`,`Inativo`,`id`,`Descricao`,`ValorBase`,`Codigo`,`Nome`,`DataAlt`) VALUES ('11','1','-1','0','79','JANTA','0.00','27','JANTA','2026-05-08 08:50:14');
