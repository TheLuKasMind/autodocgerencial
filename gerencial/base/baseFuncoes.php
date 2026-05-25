<?php
require_once 'connection.php'; 
require_once __DIR__ . '/../libs/mail/Exception.php';
require_once __DIR__ . '/../libs/mail/PHPMailer.php';
require_once __DIR__ . '/../libs/mail/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//=================PARA LOG DE ERROS=================
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// ATALHO PARA COMENTAR / DESCOMENTAR = CNTRL + ;
function ExSqlNET($sql, $conn = null, $params = [])
{
    // Se não vier conexão, usa conexão global
    if ($conn === null) {
        global $dbGeralNET;
        $conn = $dbGeralNET;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//EXEMPLO DE -------------SELECT-------------
//$dados = ExSqlNET("SELECT * from User");

//PARA TESTE DO EXSQLNET
//echo "<pre>";
//print_r($dados);
//echo "</pre>";

// =====================================================================================================
function retornaProximoCod($tabela){ 
    global $dbGeralNET;

    $idEmpresa = (int) $_SESSION['idEmpresa'];

    $dados = ExSqlNET("SELECT MAX(Codigo) AS Codigo 
                       FROM {$tabela} 
                       WHERE idEmpresa = {$idEmpresa}");

    $dados = $dados[0] ?? null;

    if (!$dados || $dados['Codigo'] === null) {
        return 1;
    }

    $codigoAtual = (int) $dados['Codigo'];

    return $codigoAtual + 1;
}

// =====================================================================================================
function formataValorGravacao($valor){
    if (!empty($valor)) {
        // Remove R$, espaços normais, não-quebráveis e pontos de milhar
        $valor = str_replace(['R$', ' ', '.', "\xc2\xa0"], '', $valor);

        // Troca vírgula decimal por ponto
        $valor = str_replace(',', '.', $valor);

        // Converte para número float
        $valor = floatval($valor);
    } else {
        $valor = 0;
    }
    return $valor;
}

// =====================================================================================================
function Despesa($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){
        $sql = "INSERT INTO `tipodespesa` 
        (idEmpresa, Categoria, Acao, Inativo, Descricao, ValorBase, Codigo, Nome, DataAlt)
        VALUES (:idEmpresa, :Categoria, :Acao, :Inativo, :Descricao, :ValorBase, :Codigo, :Nome,:DataAlt)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Categoria' => $dados['Categoria'],
                ':Acao' => $dados['Acao'],
                ':Inativo' => $dados['Inativo'],
                ':Descricao' => $dados['Descricao'],
                ':ValorBase' => $dados['ValorBase'],
                ':Codigo' => $dados['Codigo'],
                ':Nome' => $dados['Nome'],
                ':DataAlt' => $dataAlt,
                ]
            );

            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){

        $sql = "UPDATE `tipodespesa`
        SET Categoria = :Categoria,
        Acao = :Acao,
        Inativo = :Inativo,
        Descricao = :Descricao,
        ValorBase = :ValorBase,
        Nome = :Nome,
        DataAlt = :DataAlt
        WHERE id = :Id AND idEmpresa = :idEmpresa";

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Categoria' => $dados['Categoria'],
                ':Acao' => $dados['Acao'],
                ':Inativo' => $dados['Inativo'],
                ':Descricao' => $dados['Descricao'],
                ':ValorBase' => $dados['ValorBase'],
                ':Nome' => $dados['Nome'],
                ':Id' => $dados['id'],
                ':DataAlt' => $dataAlt,
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){

        try {
            $sql = "DELETE FROM `tipodespesa` WHERE id = :Id AND idEmpresa = :idEmpresa";;

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Id' =>$dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
                return ""; 
        } catch (PDOException $e) {
            return $e; 
        }

    }
}

// =====================================================================================================
function Forcli($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');
    $dataCadastro = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

    $sql = "INSERT INTO forcli 
    (idEmpresa, Codigo, Nome, Documento, RazaoSocial, Tipo, TipoDocumento ,Inativo, Email,
    Telefone, DataCadastro, CEP, UF, Bairro, Cidade, Rua, NumeroEndereco, Obs, DataAlt, Grupo,
    EstadoCivil, Profissao, DataNasc, NumeroCNH)
    VALUES 
    (:idEmpresa, :Codigo, :Nome, :Documento, :RazaoSocial, :Tipo, :TipoDocumento,
     :Inativo, :Email, :Telefone, :DataCadastro, :CEP, :UF, :Bairro, :Cidade, 
     :Rua, :NumeroEndereco, :Obs, :DataAlt, :Grupo, :EstadoCivil,
     :Profissao, :DataNasc, :NumeroCNH)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Codigo' => $dados['Codigo'],
                ':Nome' => $dados['Nome'],
                ':Documento' => $dados['Documento'],
                ':RazaoSocial' => $dados['RazaoSocial'],
                ':Tipo' => $dados['Tipo'],
                ':TipoDocumento' => $dados['TipoDocumento'],
                ':Inativo' => $dados['Inativo'],
                ':Email' => $dados['Email'],
                ':Telefone' => $dados['Telefone'],
                ':DataCadastro' => $dados['DataCadastro'],
                ':CEP' => $dados['CEP'],
                ':UF' => $dados['UF'],
                ':Bairro' => $dados['Bairro'],
                ':Cidade' => $dados['Cidade'],
                ':Rua' => $dados['Rua'],
                ':NumeroEndereco' => $dados['NumeroEndereco'],
                ':Obs' => $dados['Obs'],
                ':Grupo' => $dados['Grupo'],
                ':EstadoCivil' => $dados['EstadoCivil'],
                ':Profissao' => $dados['Profissao'],
                ':DataNasc' => $dados['DataNasc'],
                ':NumeroCNH' => $dados['NumeroCNH'],
                ':DataAlt' => $dataAlt,
                ]
            );

            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){

        $sql = "UPDATE  `forcli` SET
            idEmpresa = :idEmpresa,
            Nome = :Nome ,
            Documento = :Documento , 
            RazaoSocial = :RazaoSocial ,
            Tipo = :Tipo ,
            TipoDocumento = :TipoDocumento ,
            Inativo = :Inativo ,
            Email = :Email ,
            Telefone = :Telefone , 
            DataCadastro = :DataCadastro , 
            CEP = :CEP , 
            UF = :UF , 
            Bairro = :Bairro , 
            Cidade = :Cidade ,
            Rua = :Rua , 
            NumeroEndereco = :NumeroEndereco ,
            Obs = :Obs ,
            Grupo = :Grupo ,
            EstadoCivil = :EstadoCivil,
            Profissao = :Profissao,
            DataNasc = :DataNasc,
            NumeroCNH = :NumeroCNH,
            DataAlt = :DataAlt
            WHERE id = :Id AND idEmpresa = :idEmpresa"; 

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Nome' => $dados['Nome'],
                ':Documento' => $dados['Documento'],
                ':RazaoSocial' => $dados['RazaoSocial'],
                ':Tipo' => $dados['Tipo'],
                ':TipoDocumento' => $dados['TipoDocumento'],
                ':Inativo' => $dados['Inativo'],
                ':Email' => $dados['Email'],
                ':Telefone' => $dados['Telefone'],
                ':DataCadastro' => $dataCadastro,
                ':CEP' => $dados['CEP'],
                ':UF' => $dados['UF'],
                ':Bairro' => $dados['Bairro'],
                ':Cidade' => $dados['Cidade'],
                ':Rua' => $dados['Rua'],
                ':NumeroEndereco' => $dados['NumeroEndereco'],
                ':Obs' => $dados['Obs'],
                ':Id'  => $dados['id'],
                ':idEmpresa' => $dados['idEmpresa'],
                ':Grupo' => $dados['Grupo'],
                ':EstadoCivil' => $dados['EstadoCivil'],
                ':Profissao' => $dados['Profissao'],
                ':DataNasc' => $dados['DataNasc'],
                ':NumeroCNH' => $dados['NumeroCNH'],
                ':DataAlt' => $dataAlt,
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){
        try {
            $sql = "DELETE FROM `forcli` WHERE id = :Id AND idEmpresa = :idEmpresa";;

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Id' =>$dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
            return ""; 
        } catch (PDOException $e) {
            return $e; 
        }
    }

}

// =====================================================================================================
function ServProd($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');
    $dataCadastro = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

        $sql = "INSERT INTO `servprod` 
        (idEmpresa, Codigo, Descricao, Inativo, Nome, Tipo, Unidade, ValorCusto, ValorVenda, DataAlt, SolicitaVeiculo, MetaMensal, Grupo)
        VALUES (:idEmpresa, :Codigo, :Descricao, :Inativo, :Nome, :Tipo, :Unidade, :ValorCusto,:ValorVenda,
        :DataAlt, :SolicitaVeiculo, :MetaMensal, :Grupo)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Codigo' => $dados['Codigo'],
                ':Descricao' => $dados['Descricao'],
                ':Inativo' => $dados['Inativo'],
                ':SolicitaVeiculo' => $dados['SolicitaVeiculo'],
                ':Nome' => $dados['Nome'],
                ':Tipo' => $dados['Tipo'],
                ':Unidade' => $dados['Unidade'],
                ':ValorCusto' => $dados['ValorCusto'],
                ':ValorVenda' => $dados['ValorVenda'],
                ':MetaMensal' => $dados['MetaMensal'],
                ':Grupo' => $dados['Grupo'],
                ':DataAlt' => $dataAlt,
                ]
            );
            $idGerado = $dbGeralNET->lastInsertId();
            $_SESSION['idGerado'] = $idGerado;
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){
        $sql = "UPDATE  `servprod` SET
            Descricao = :Descricao, 
            Inativo = :Inativo,
            Nome = :Nome,
            Tipo = :Tipo,
            Unidade = :Unidade,
            ValorCusto = :ValorCusto,
            ValorVenda = :ValorVenda, 
            DataAlt = :DataAlt,
            SolicitaVeiculo = :SolicitaVeiculo,
            MetaMensal = :MetaMensal,
            Grupo = :Grupo
            WHERE id = :Id AND idEmpresa = :idEmpresa"; 

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Descricao' => $dados['Descricao'],
                ':Inativo' => $dados['Inativo'],
                ':SolicitaVeiculo' => $dados['SolicitaVeiculo'],
                ':Nome' => $dados['Nome'],
                ':Tipo' => $dados['Tipo'],
                ':Unidade' => $dados['Unidade'],
                ':ValorCusto' => $dados['ValorCusto'],
                ':ValorVenda' => $dados['ValorVenda'],
                ':MetaMensal' => $dados['MetaMensal'],
                ':Grupo' => $dados['Grupo'],
                ':Id' =>$dados['id'],
                ':DataAlt' => $dataAlt,
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){
        try {
            $sql = "DELETE FROM `servprod` WHERE id = :Id AND idEmpresa = :idEmpresa";;

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Id' =>$dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
                return ""; 
        } catch (PDOException $e) {
            return $e; 
        }
    }

}

function ServProdCusto($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

        $sql = "INSERT INTO `servprodcusto` 
        (idEmpresa, Data, ServProd, Unidade, ValorCusto, ValorVenda, DataAlt)
        VALUES (:idEmpresa, :Data, :ServProd, :Unidade, :ValorCusto, :ValorVenda, :DataAlt)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Data' => $dataAlt,
                ':ServProd' => $dados['ServProd'],
                ':Unidade' => $dados['Unidade'],
                ':ValorCusto' => $dados['ValorCusto'],
                ':ValorVenda' => $dados['ValorVenda'],
                ':DataAlt' => $dataAlt,
                ]
            );
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){
        $sql = "UPDATE  `servprodcusto` SET
            idEmpresa = :idEmpresa,
            Data = :Data,
            ServProd = :ServProd,
            Unidade = :Unidade,
            ValorCusto = :ValorCusto, 
            ValorVenda = :ValorVenda, 
            DataAlt = :DataAlt
            WHERE id = :Id AND idEmpresa = :idEmpresa"; 

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Data' => $dataAlt,
                ':ServProd' => $dados['ServProd'],
                ':Unidade' => $dados['Unidade'],
                ':ValorCusto' => $dados['ValorCusto'],
                ':ValorVenda' => $dados['ValorVenda'],
                ':Id' =>$dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
                ':DataAlt' => $dataAlt,
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){
        try {
            $sql = "DELETE FROM `servprodcusto` WHERE servprod = :servprod AND idEmpresa = :idEmpresa";;

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':servprod' =>$dados['servprod'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
                return ""; 
        } catch (PDOException $e) {
            return $e; 
        }
    }

}

// =====================================================================================================
function Movimento($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');
    $dataCadastro = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

        $sql = "INSERT INTO `movimento` 
        (idEmpresa, Forcli, ForcliRepasse, Data, Status, CondPgto, CorVeiculo, ModeloVeiculo,
        PlacaVeiculo, DataAlt, DataPgto, Obs, idUser, StatusProcesso)
        VALUES (:idEmpresa, :Forcli, :ForcliRepasse, :Data, :Status, :CondPgto, :CorVeiculo, 
        :ModeloVeiculo, :PlacaVeiculo, :DataAlt, :DataPgto, :Obs, :idUser, :StatusProcesso)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Forcli' => $dados['Forcli'],
                ':ForcliRepasse' => $dados['ForcliRepasse'],
                ':Data' => $dados['Data'],
                ':Status' => $dados['Status'],
                ':CondPgto' => $dados['CondPgto'],
                ':CorVeiculo' => $dados['CorVeiculo'],
                ':ModeloVeiculo' => $dados['ModeloVeiculo'],
                ':PlacaVeiculo' => $dados['PlacaVeiculo'],
                ':DataAlt' => $dataAlt,
                ':DataPgto' => $dados['DataPgto'],
                ':Obs' => $dados['Obs'],
                ':idUser' => $dados['idUser'],
                ':StatusProcesso' => $dados['StatusProcesso'],
                ]
            );
            $idGerado = $dbGeralNET->lastInsertId();
            $_SESSION['idGerado'] = $idGerado;
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){
        $sql = "UPDATE  `movimento` SET
            Forcli = :Forcli, 
            ForcliRepasse = :ForcliRepasse,
            Data = :Data,
            Status = :Status,
            CondPgto = :CondPgto,
            CorVeiculo = :CorVeiculo,
            ModeloVeiculo = :ModeloVeiculo, 
            PlacaVeiculo = :PlacaVeiculo,
            DataAlt = :DataAlt,
            DataPgto = :DataPgto,
            Obs = :Obs,
            idUser = :idUser,
            StatusProcesso = :StatusProcesso
            WHERE id = :Id AND idEmpresa = :idEmpresa"; 

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Id'  => $dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
                ':Forcli' => $dados['Forcli'],
                ':ForcliRepasse' => $dados['ForcliRepasse'],
                ':Data' => $dados['Data'],
                ':Status' => $dados['Status'],
                ':CondPgto' => $dados['CondPgto'],
                ':CorVeiculo' => $dados['CorVeiculo'],
                ':ModeloVeiculo' => $dados['ModeloVeiculo'],
                ':PlacaVeiculo' => $dados['PlacaVeiculo'],
                ':DataAlt' => $dataAlt,
                ':DataPgto' => $dados['DataPgto'],
                ':Obs' => $dados['Obs'],
                ':idUser' => $dados['idUser'],
                ':StatusProcesso' => $dados['StatusProcesso'],
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){
        try {
            $sql = "DELETE FROM `movimento` WHERE id = :Id AND idEmpresa = :idEmpresa";
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':Id' =>$dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
                return ""; 
        } catch (PDOException $e) {
            return $e; 
        }
    }

}

// =====================================================================================================
function MovimentoItem($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');
    $dataCadastro = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

        $sql = "INSERT INTO `movimentoitem` 
        (ControleMovimento, idEmpresa, Descricao, DataAlt, Qtd, ServProd, TotalItem, Valor, ValorCusto)
        VALUES (:ControleMovimento, :idEmpresa, :Descricao, :DataAlt, :Qtd, :ServProd, 
        :TotalItem, :Valor, :ValorCusto)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':ControleMovimento' => $dados['ControleMovimento'],
                ':Descricao' => $dados['Descricao'],
                ':Qtd' => $dados['Qtd'],
                ':ServProd' => $dados['ServProd'],
                ':TotalItem' => $dados['TotalItem'],
                ':Valor' => $dados['Valor'],
                ':DataAlt' => $dataAlt,
                ':ValorCusto' => $dados['ValorCusto'],
                ]
            );
            $idGerado = $dbGeralNET->lastInsertId();
            $_SESSION['idGerado'] = $idGerado;
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){
        $sql = "UPDATE  `movimentoitem` SET
            ControleMovimento = :ControleMovimento, 
            Descricao = :Descricao,
            Qtd = :Qtd,
            ServProd = :ServProd,
            TotalItem = :TotalItem,
            Valor = :Valor,
            DataAlt = :DataAlt,
            ValorCusto = :ValorCusto
            WHERE id = :Id AND idEmpresa = :idEmpresa"; 

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':idEmpresa'  => $dados['idEmpresa'],
                ':ControleMovimento' => $dados['ControleMovimento'],
                ':Descricao' => $dados['Descricao'],
                ':Qtd' => $dados['Qtd'],
                ':ServProd' => $dados['ServProd'],
                ':TotalItem' => $dados['TotalItem'],
                ':Valor' => $dados['Valor'],
                ':DataAlt' => $dataAlt,
                ':ValorCusto' => $dados['ValorCusto'],
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){
        try {
            $sql = "DELETE FROM `movimentoitem` WHERE ControleMovimento = :Id AND idEmpresa = :idEmpresa";;

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Id' =>$dados['id'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
                return ""; 
        } catch (PDOException $e) {
            return $e; 
        }
    }

}

// =====================================================================================================
function MovimentoCC($dados, $acao){ //1 = CADASTRAR, 2 = ATUALIZAR, 3 = EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');
    $dataCadastro = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

        $sql = "INSERT INTO `movimentocc` 
        (idEmpresa, TipoDespesa, Descricao, Valor, Data, idForcli,
        idServProd, DataPgto, ValorPgto, UserAlt, TipoMov, CaixaGeral, ControleOrigem,
        DataAlt, idUser)
        VALUES (:idEmpresa, :TipoDespesa, :Descricao, :Valor, :Data, :idForcli,
        :idServProd, :DataPgto, :ValorPgto, :UserAlt, :TipoMov, :CaixaGeral, :ControleOrigem,
        :DataAlt, :idUser)";

        try {

            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa' => $dados['idEmpresa'],
                ':TipoDespesa' => $dados['TipoDespesa'],
                ':Descricao' => $dados['Descricao'],
                ':Valor' => $dados['Valor'],
                ':Data' => $dados['Data'],
                ':idForcli' => $dados['idForcli'],
                ':idServProd' => $dados['idServProd'],
                ':DataPgto' => $dados['DataPgto'], 
                ':ValorPgto' => $dados['ValorPgto'],
                ':UserAlt' => $dados['UserAlt'],
                ':TipoMov' => $dados['TipoMov'],
                ':CaixaGeral' => $dados['CaixaGeral'],
                ':ControleOrigem' => $dados['ControleOrigem'],
                ':DataAlt' => $dataAlt,
                ':idUser' => $dados['idUser'],
                ]
            );
            $idGerado = $dbGeralNET->lastInsertId();
            
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "ATUALIZAR"){
        $sql = "UPDATE  `movimentocc` SET
            idEmpresa = :idEmpresa,
            TipoDespesa = :TipoDespesa,
            Descricao = :Descricao,
            Valor = :Valor,
            Data = :Data,
            idForcli = :idForcli,
            Controle = :Controle,                  
            idServProd = :idServProd,
            DataPgto = :DataPgto,
            ValorPgto = :ValorPgto,
            UserAlt = :UserAlt,
            TipoMov = :TipoMov,
            CaixaGeral = :CaixaGeral,
            ControleOrigem = :ControleOrigem,
            DataAlt = :DataAlt,
            idUser = :idUser
            WHERE Controle = :Controle AND idEmpresa = :idEmpresa"; 

        try {
            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':idEmpresa' => $dados['idEmpresa'],
                ':TipoDespesa' => $dados['TipoDespesa'],
                ':Descricao' => $dados['Descricao'],
                ':Valor' => $dados['Valor'],
                ':Data' => $dados['Data'],
                ':idForcli' => $dados['idForcli'],
                ':Controle' => $dados['Controle'],
                ':idServProd' => $dados['idServProd'],
                ':DataPgto' => $dados['DataPgto'], 
                ':ValorPgto' => $dados['ValorPgto'],
                ':UserAlt' => $dados['UserAlt'],
                ':TipoMov' => $dados['TipoMov'],
                ':CaixaGeral' => $dados['CaixaGeral'],
                ':ControleOrigem' => $dados['ControleOrigem'],
                ':DataAlt' => $dataAlt,
                ':idUser' => $dados['idUser'],
            ]);
            return ""; 
        }catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIR"){
        try {
            $sql = "DELETE FROM `movimentocc` WHERE ControleOrigem = :Controle AND idEmpresa = :idEmpresa";

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':Controle' =>$dados['ControleOrigem'],
                ':idEmpresa'  => $dados['idEmpresa'],
            ]);

            //echo $stmt->rowCount();
            return ""; 
        } catch (PDOException $e) {
            return $e; 
        }
    }else if($acao === "EXCLUIRCONTROLE"){
        try {
            //  $sql = "DELETE FROM `movimentocc` WHERE Controle = :Controle AND idEmpresa = :idEmpresa";

            // $sql = "DELETE FROM `movimentocc` WHERE Controle = :Controle AND idEmpresa = :idEmpresa";
     
            // $stmt = $dbGeralNET->prepare($sql);

            // $stmt->execute([
            //     ':Controle' =>$dados['Controle'],
            //     ':idEmpresa'  => $dados['idEmpresa'],
            // ]);

            // return ""; 
            
            $ids = $dados['Controle'];
            
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            
            $params = [
                ':idEmpresa' => $dados['idEmpresa']
            ];
            
            $placeholders = [];
            
            foreach ($ids as $i => $id) {
            
                $ph = ":Controle$i";
            
                $placeholders[] = $ph;
            
                $params[$ph] = $id;
            }
            
            $sql = "DELETE FROM movimentocc 
                    WHERE Controle IN (" . implode(',', $placeholders) . ")
                    AND idEmpresa = :idEmpresa";
            
            $stmt = $dbGeralNET->prepare($sql);
            
            $stmt->execute($params);
            
            return "";

        } catch (PDOException $e) {
            return $e; 
        }
    }
}

//=============================EMAIL - CADASTRO APROVADO=============================
function enviaEmailCadastroAprovado($emailRecebe) { 
    // $nomeSistema = "Autodoc";
    // $linkSistema = "http://localhost/autodocgerencial/index.php";
    // $nomeEnvia = "Autodoc";
    // $emailEnvia = "lucasbugatib0@gmail.com";
    // $senhaApp = "bcss fsze wihz iauz";

    require_once __DIR__ . '/globals.php';

    $config = $GLOBALS['EMAIL_SMTP'];

    $nomeSistema = $config['remetente_nome'];
    $linkSistema = $config['link_sistema'];
    $nomeEnvia = $config['remetente_nome'];
    $emailEnvia = $config['usuario'];
    $senhaApp = $config['senha_app'];

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $emailEnvia;
        $mail->Password   = $senhaApp;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($emailEnvia, $nomeEnvia);
        $mail->addAddress($emailRecebe);

        $msg = "
        <div style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 30px; border-radius: 10px;'>
            <div style='background-color: #ffffff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
                
                <h2 style='color:#ff6600; text-align:center;'>
                    ✅ Cadastro Aprovado!
                </h2>

                <p style='font-size:16px; color:#333; text-align:center;'>
                    Parabéns! O cadastro da sua <strong style='color:#ff6600;'>empresa foi aprovado</strong> com sucesso.
                </p>

                <p style='font-size:16px; color:#555; text-align:center;'>
                    Seu acesso já está liberado e você pode entrar no sistema normalmente.
                </p>

                <div style='text-align:center; margin-top:25px;'>
                    <a href='".$linkSistema."' 
                        style='background-color:#ff6600; color:#fff; text-decoration:none; 
                               padding:12px 25px; border-radius:6px; font-weight:bold;'>
                        Acessar o Sistema
                    </a>
                </div>

                <p style='font-size:13px; color:#777; text-align:center; margin-top:30px;'>
                    Sistema <strong>".$nomeSistema."</strong> 🚗
                </p>

            </div>
        </div>
        ";

        $mail->isHTML(true);
        $mail->Subject = 'CADASTRO DA EMPRESA APROVADO!';
        $mail->Body = $msg;
        $mail->send();
        return true;

    } catch (Exception $e) {
        file_put_contents( '../logs/erro_email_CadastroAprovado.txt', "Erro: {$mail->ErrorInfo}");
        return false;
    }
}


//-----------------------------BRASIL API----------------------
//============================CONSULTAR CEP====================
function consultarCep($cep) {
    // Remove caracteres não numéricos
    $cep = preg_replace('/\D/', '', $cep);
    global $ambiente;
    
    if (strlen($cep) !== 8) {
        return [
            'status_code' => 400,
            'body' => ['erro' => 'CEP inválido. Deve conter exatamente 8 dígitos numéricos.']
        ];
    }

    $url = "https://brasilapi.com.br/api/cep/v2/{$cep}";

    $headers = [
        'Accept: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // if ($ambiente == 1) {
    //     curl_setopt($ch, CURLOPT_SSLCERT, $pfxPath);
    //     curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12"); 
    //     curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $certificadoSenhaAPI);
    // }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $erro = curl_error($ch);
        curl_close($ch);
        return [
            'status_code' => 0,
            'body' => ['erro' => $erro]
        ];
    }

    curl_close($ch);

    $body = json_decode($response, true);

    if ($httpCode !== 200 || !$body) {
        return [
            'status_code' => $httpCode,
            'body' => ['erro' => 'CEP não encontrado ou resposta inválida.']
        ];
    }

    return [
        'status_code' => $httpCode,
        'body' => $body
    ];
}

//============================ENVIA EMAIL RECUPERA SENHA====================
function enviarRecuperacaoSenha($email)
{
    global $dbGeralNET;

    require_once __DIR__ . '/globals.php';


    $smtp = $GLOBALS['EMAIL_SMTP'];

    $sql = "SELECT id, nome, email FROM user WHERE email = ? AND inativo = 0 LIMIT 1";
    $res = ExSqlNET($sql, null, [$email]);

    if (empty($res)) {
        return true;
    }

    $user = $res[0];

    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $dbGeralNET->prepare("
        UPDATE user
        SET tokenRecuperar = ?, tokenExpira = ?
        WHERE id = ?
    ");

    $stmt->execute([$token, $expira, $user['id']]);

    $link = "https://".$smtp['link_sistema']."/gerencial/base/frmRecuperaSenha.php?token=".$token;

    $assunto = "Recuperação de senha";

    $mensagem = "
        <div style='background:#f5f5f5;padding:40px 0;font-family:Arial,Helvetica,sans-serif;'>
        
        <table align='center' width='100%' cellpadding='0' cellspacing='0' style='max-width:500px;background:white;border-radius:8px;overflow:hidden;'>
        
        <tr>
        <td style='background:#f97316;color:white;padding:20px;text-align:center;font-size:20px;font-weight:bold;'>
        Recuperação de senha
        </td>
        </tr>
        
        <tr>
        <td style='padding:30px;color:#333;font-size:15px;line-height:1.6;'>
        
        Olá <strong>{$user['nome']}</strong>,<br><br>
        
        Recebemos uma solicitação para redefinir sua senha.<br>
        Clique no botão abaixo para criar uma nova senha:
        
        <br><br>
        
        <div style='text-align:center;'>
        
        <a href='{$link}' 
        style='
        background:#f97316;
        color:white;
        padding:14px 26px;
        text-decoration:none;
        border-radius:6px;
        font-weight:bold;
        display:inline-block;
        '>
        Redefinir senha
        </a>
        
        </div>
        
        <br><br>
        
        Se você não solicitou essa alteração, pode ignorar este e-mail com segurança.
        
        <br><br>
        
        <span style='color:#777;font-size:13px;'>
        Este link expira automaticamente por segurança.
        </span>
        
        </td>
        </tr>
        
        <tr>
        <td style='background:#fafafa;padding:15px;text-align:center;font-size:12px;color:#999;'>
        
        © ".date('Y')." {$smtp['remetente_nome']}
        
        </td>
        </tr>
        
        </table>
        
        </div>
    ";

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    
    try {

        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp['usuario'];
        $mail->Password   = $smtp['senha_app'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = $smtp['porta'];

        $mail->setFrom($smtp['usuario'], $smtp['remetente_nome']);
        $mail->addAddress($user['email'], $user['nome']);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem;

        $mail->send();

    } catch (Exception $e) {
        error_log("Erro ao enviar email: ".$mail->ErrorInfo);
    }

    return true;
}

// ==========================================================================================
function Arquivo($dados, $acao){ // CADASTRAR | ATUALIZAR | EXCLUIR

    global $dbGeralNET;
    $dataAlt = date('Y-m-d H:i:s');

    if($acao === "CADASTRAR"){

        $sql = "INSERT INTO arquivos 
        (idEmpresa, idForcli, Tipo, Descricao, NomeArquivo, ArquivoBase64, DataCadastro)
        VALUES 
        (:idEmpresa, :idForcli, :Tipo, :Descricao, :NomeArquivo, :ArquivoBase64, :DataCadastro)";

        try {
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa' => $dados['idEmpresa'],
                ':idForcli' => $dados['idForcli'],
                ':Tipo' => $dados['Tipo'],
                ':Descricao' => $dados['Descricao'],
                ':NomeArquivo' => $dados['NomeArquivo'],
                ':ArquivoBase64' => $dados['ArquivoBase64'],
                ':DataCadastro' => $dataAlt
            ]);

            $idGerado = $dbGeralNET->lastInsertId();
            $_SESSION['idArquivo'] = $idGerado;

            return "";

        } catch (PDOException $e) {
            return $e;
        }
    }

    else if($acao === "ATUALIZAR"){

        $sql = "UPDATE arquivos SET
            Tipo = :Tipo,
            Descricao = :Descricao,
            NomeArquivo = :NomeArquivo,
            ArquivoBase64 = :ArquivoBase64
        WHERE id = :id AND idEmpresa = :idEmpresa";

        try {
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':id' => $dados['id'],
                ':idEmpresa' => $dados['idEmpresa'],
                ':Tipo' => $dados['Tipo'],
                ':Descricao' => $dados['Descricao'],
                ':NomeArquivo' => $dados['NomeArquivo'],
                ':ArquivoBase64' => $dados['ArquivoBase64']
            ]);

            return "";

        } catch (PDOException $e) {
            return $e;
        }
    }
    else if($acao === "EXCLUIR"){

        try {
            $sql = "DELETE FROM arquivos WHERE idForcli = :id AND idEmpresa = :idEmpresa";
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':id' => $dados['idForcli'],
                ':idEmpresa' => $dados['idEmpresa']
            ]);
            return "";

        } catch (PDOException $e) {
            return $e;
        }
    }
}

// ==========================================================================================
// USER CADASTRO | ATUALIZAR
function MeuCadastro($dados, $acao){ // ATUALIZAR

    global $dbGeralNET;

    if($acao === "ATUALIZAR"){

        $sql = "UPDATE user SET
            EstadoCivil = :EstadoCivil,
            NumOab = :NumOab,
            Rua = :Rua,
            NumeroEndereco = :NumeroEndereco,
            Cidade = :Cidade,
            UF = :UF,
            CEP = :CEP,
            LogoBase64 = :LogoBase64,
            Contato = :Contato,
            Documento = :Documento,
            Bairro = :Bairro
        WHERE id = :id";

        try {

            $stmt = $dbGeralNET->prepare($sql);

            $stmt->execute([
                ':id' => $dados['id'],
                ':EstadoCivil' => $dados['EstadoCivil'],
                ':NumOab' => $dados['NumOab'],
                ':Rua' => $dados['Rua'],
                ':NumeroEndereco' => $dados['NumeroEndereco'],
                ':Cidade' => $dados['Cidade'],
                ':UF' => $dados['UF'],
                ':CEP' => $dados['CEP'],
                ':LogoBase64' => $dados['LogoBase64'],
                ':Contato' => $dados['Contato'],
                ':Documento' => $dados['Documento'],
                ':Bairro' => $dados['Bairro']
            ]);

            return "";

        } catch (PDOException $e) {

            return $e;
        }
    }

    return "Ação inválida.";
}

// ==================================MULTA===================================================
function Multa($dados, $acao){

    global $dbGeralNET;

    if($acao === "CADASTRAR"){
        $sql = "INSERT INTO multa (idEmpresa, Forcli, SerieMulta, CodigoProcesso, OrgaoFiscalizador,
            PlacaVeiculo, PlacasAdicionais, RegistroCNH, PrazoDefesa, AutoSuspensiva, RecursoMulta,
            StatusMulta, Observacao, EnviarLembrete, idUser, UserAlt, DataCadastro, DataAlt
        ) VALUES (
            :idEmpresa, :Forcli, :SerieMulta, :CodigoProcesso, :OrgaoFiscalizador,
            :PlacaVeiculo, :PlacasAdicionais, :RegistroCNH, :PrazoDefesa, :AutoSuspensiva,
            :RecursoMulta, :StatusMulta, :Observacao, :EnviarLembrete, :idUser,
            :UserAlt, :DataCadastro, NOW()
        )";

        try {
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'         => $dados['idEmpresa'],
                ':Forcli'            => $dados['Forcli'],
                ':SerieMulta'        => $dados['SerieMulta'],
                ':CodigoProcesso'    => $dados['CodigoProcesso'],
                ':OrgaoFiscalizador' => $dados['OrgaoFiscalizador'],
                ':PlacaVeiculo'      => $dados['PlacaVeiculo'],
                ':PlacasAdicionais'  => $dados['PlacasAdicionais'],
                ':RegistroCNH'       => $dados['RegistroCNH'],
                ':PrazoDefesa'       => $dados['PrazoDefesa'],
                ':AutoSuspensiva'    => $dados['AutoSuspensiva'],
                ':RecursoMulta'      => $dados['RecursoMulta'],
                ':StatusMulta'       => $dados['StatusMulta'],
                ':Observacao'        => $dados['Observacao'],
                ':EnviarLembrete'   => $dados['EnviarLembrete'],
                ':idUser'            => $dados['idUser'],
                ':UserAlt'           => $dados['UserAlt'],
                ':DataCadastro'       => $dados['DataCadastro'],
            ]);
            // return $dbGeralNET->lastInsertId();
            return "";
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    if($acao === "ATUALIZAR"){

        $sql = "UPDATE multa SET
            Forcli = :Forcli,
            SerieMulta = :SerieMulta,
            CodigoProcesso = :CodigoProcesso,
            OrgaoFiscalizador = :OrgaoFiscalizador,
            PlacaVeiculo = :PlacaVeiculo,
            PlacasAdicionais = :PlacasAdicionais,
            RegistroCNH = :RegistroCNH,
            PrazoDefesa = :PrazoDefesa,
            AutoSuspensiva = :AutoSuspensiva,
            RecursoMulta = :RecursoMulta,
            StatusMulta = :StatusMulta,
            Observacao = :Observacao,
            EnviarLembrete = :EnviarLembrete,
            UserAlt = :UserAlt,
            DataCadastro = :DataCadastro,
            DataAlt = NOW()
        WHERE id = :id
        AND idEmpresa = :idEmpresa";

        try {
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':id'                => $dados['id'],
                ':idEmpresa'         => $dados['idEmpresa'],
                ':Forcli'            => $dados['Forcli'],
                ':SerieMulta'        => $dados['SerieMulta'],
                ':CodigoProcesso'    => $dados['CodigoProcesso'],
                ':OrgaoFiscalizador' => $dados['OrgaoFiscalizador'],
                ':PlacaVeiculo'      => $dados['PlacaVeiculo'],
                ':PlacasAdicionais'  => $dados['PlacasAdicionais'],
                ':RegistroCNH'       => $dados['RegistroCNH'],
                ':PrazoDefesa'       => $dados['PrazoDefesa'],
                ':AutoSuspensiva'    => $dados['AutoSuspensiva'],
                ':RecursoMulta'      => $dados['RecursoMulta'],
                ':StatusMulta'       => $dados['StatusMulta'],
                ':Observacao'        => $dados['Observacao'],
                ':EnviarLembrete'   => $dados['EnviarLembrete'],
                ':UserAlt'           => $dados['UserAlt'],
                ':DataCadastro'       => $dados['DataCadastro'],
            ]);
            return "";
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
       
    if($acao === "EXCLUIR"){
        $sql = "DELETE FROM multa WHERE id = :id AND idEmpresa = :idEmpresa";
        try {
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':id'        => $dados['id'],
                ':idEmpresa' => $dados['idEmpresa']
            ]);
            return "";
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    return "Ação inválida.";
}

function Lembrete($dados, $acao){

    global $dbGeralNET;

    if($acao === "CADASTRAR"){
        $sql = "INSERT INTO movtolembrete (idEmpresa,Titulo,Descricao,DataLembrete,
            Concluido,DataCadastro, idUser
        ) VALUES (:idEmpresa,:Titulo,:Descricao,:DataLembrete,
            :Concluido, NOW(), :idUser
        )";
        try{
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':idEmpresa'    => $dados['idEmpresa'],
                ':Titulo'       => $dados['Titulo'],
                ':Descricao'    => $dados['Descricao'],
                ':DataLembrete' => $dados['DataLembrete'],
                ':Concluido'    => $dados['Concluido'],
                ':idUser' => $dados['idUser']
            ]);
            return "";
        }catch(PDOException $e){
            return $e->getMessage();
        }
    }

    if($acao === "ATUALIZAR"){
        $sql = "UPDATE movtolembrete SET
            Titulo = :Titulo,
            Descricao = :Descricao,
            DataLembrete = :DataLembrete,
            Concluido = :Concluido
        WHERE id = :id
        AND idEmpresa = :idEmpresa
        AND idUser = :idUser";

        try{
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':id'            => $dados['id'],
                ':idEmpresa'     => $dados['idEmpresa'],
                ':Titulo'        => $dados['Titulo'],
                ':Descricao'     => $dados['Descricao'],
                ':DataLembrete'  => $dados['DataLembrete'],
                ':Concluido'     => $dados['Concluido'],
                ':idUser' => $dados['idUser'],
            ]);
            return "";
        }catch(PDOException $e){
            return $e->getMessage();
        }
    }

    if($acao === "EXCLUIR"){
        $sql = "DELETE FROM movtolembrete WHERE id = :id AND idEmpresa = :idEmpresa";
        try{
            $stmt = $dbGeralNET->prepare($sql);
            $stmt->execute([
                ':id'        => $dados['id'],
                ':idEmpresa' => $dados['idEmpresa']
            ]);
            return "";
        }catch(PDOException $e){
            return $e->getMessage();
        }
    }
    return "Ação inválida.";
}



?>