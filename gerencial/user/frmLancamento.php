<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

$idEmpresa = $_SESSION['idEmpresa'];

$listaForcli = ExSqlNET("SELECT Id, Nome, Documento FROM forcli WHERE Inativo = 0 AND idEmpresa = " .$idEmpresa. " ORDER BY Nome ASC");
$listaServProd = ExSqlNET("SELECT Id, Codigo, Nome, ValorCusto, ValorVenda, SolicitaVeiculo FROM servprod WHERE Inativo = 0 AND idEmpresa = ". $idEmpresa. " ORDER BY Nome ASC");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    $dados = ExSqlNET(
        "SELECT *,
        movimento.Id As idMovimento,
        c.Nome As ForcliNome,
        c.id As ForcliId,
        c.Documento As ForcliDoc,
        p.Nome As PagadorNome,
        p.id As PagadorId,
        p.Documento As PagadorDoc,
        movimento.Obs,
        user.id As idUser,
        user.Nome as NomeUsuario,
        movimento.DataAlt As DataAltPedido
        
         FROM movimento
         LEFT JOIN forcli c on c.id = movimento.Forcli
         LEFT JOIN forcli p on p.id = movimento.ForcliRepasse
         LEFT JOIN user on user.id = movimento.idUser
         WHERE movimento.id = ?
           AND movimento.idEmpresa = ?",
        null,
        [$id, $_SESSION['idEmpresa']]
    );

    $dados = $dados[0] ?? null; // pega o registro
    $Alterando = true;

    $dadosItens = ExSqlNET(
        "SELECT *
         FROM movimentoitem
         WHERE ControleMovimento = ?
           AND idEmpresa = ?",
        null,
        [$dados['idMovimento'], $_SESSION['idEmpresa']]
    );

} else {
    $Alterando = false;
}

$msgRetorno =  '';
$tipoMsg = '';

$dadosCadastro = [];
$dadosCadastro['Data'] = $_POST['data'] ?? null;; 
$dadosCadastro['Status'] = $_POST['statusPedido'] ?? null;; 
$dadosCadastro['CondPgto'] = $_POST['CondPgto'] ?? null;; 
$dadosCadastro['Forcli'] = $_POST['clienteId'] ?? null;; 

// $dadosCadastro['ForcliRepasse'] = $_POST['pagadorId'] ?? 0;; 
$dadosCadastro['ForcliRepasse'] = !empty($_POST['pagadorId']) 
    ? (int) $_POST['pagadorId'] 
    : 0;
    

$dadosCadastro['PlacaVeiculo'] = $_POST['veiculo_placa'] ?? null;; 
$dadosCadastro['ModeloVeiculo'] = $_POST['veiculo_modelo'] ?? null;; 
$dadosCadastro['CorVeiculo'] = $_POST['veiculo_cor'] ?? null;; 
$dadosCadastro['id'] = $id;
$dadosCadastro['idEmpresa'] = $_SESSION['idEmpresa']  ?? null;

// $dadosCadastro['DataPgto'] = $_POST['dataPgto'] ?? null;
// $dadosCadastro['DataPgto'] = (!empty($_POST['dataPgto']) && $_POST['statusPedido'] == 1)
//     ? $_POST['dataPgto']
//     : null;

if ($dadosCadastro['Status'] == 1) {

    if (!empty($_POST['dataPgto'])) {
        $dadosCadastro['DataPgto'] = $_POST['dataPgto'];
    } else {
        $dadosCadastro['DataPgto'] = date('Y-m-d H:i:s');
    }

} else {
    $dadosCadastro['DataPgto'] = null;
}

    
$dadosCadastro['Obs'] = $_POST['obs'] ?? " ";
$dadosCadastro['idUser'] = $_SESSION['usuario_id']  ?? 0;
$dadosCadastro['StatusProcesso'] = $_POST['statusProcesso'] ?? null;; 

// if (!empty($dadosCadastro['DataPgto'])) {

//     if (strlen($dadosCadastro['DataPgto']) == 10) {
//         $dadosCadastro['DataPgto'] .= ' ' . date('H:i:s');
//     }

// } else {
//     $dadosCadastro['DataPgto'] = date('Y-m-d H:i:s');
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar']) || isset($_POST['excluir'])) {
 
    try {
        
        if($dadosCadastro['Forcli'] == ""){
          $_SESSION['mensagem_erro'] = "Necessário informar cliente no pedido!";
            header('Location: Pedidos');
            exit;
        }
        
        if ($Alterando === false){ //CADASTRANDO
            $retorno = Movimento($dadosCadastro, "CADASTRAR");
            $itens = json_decode($_POST['itens'] ?? '[]', true);
            $idGerado = $_SESSION['idGerado'];
            // file_put_contents(__DIR__ . '/../logs/frmLancamentoCadastrar.txt', print_r($itens , true) . "\n", FILE_APPEND);
            $retornoItens = "";
    
            // Se existir itens
            if (!empty($itens)) {

                foreach ($itens as $item) {

                    $dadosItem = [
                        'ControleMovimento' => $idGerado,
                        'ServProd' => $item['idProduto'],
                        'Descricao' => $item['descricao'],
                        'Qtd' => $item['qtd'],
                        'Valor' => $item['valor'],
                        'ValorCusto' => $item['valorCusto'],
                        'TotalItem' => $item['total'],
                        'idEmpresa' => $_SESSION['idEmpresa']
                    ];

                    $retornoItens = MovimentoItem($dadosItem, "CADASTRAR");

                    if ($retornoItens != "") {
                        $msgRetorno = "Erro ao cadastrar o Item do Movimento. Erro -> " . $retornoItens;
                        $_SESSION['mensagem_erro'] = $msgRetorno;
                        header('Location: Pedido');
                        exit;
                    }
                }
            }

            $dadosCadastro['ControleOrigem'] = $idGerado;
            $dadosCadastro['Descricao'] = "LUCRO VENDA PEDIDO";
    
            $SomaItens = ExSqlNET(
                "SELECT SUM(TotalItem) AS Total FROM movimentoitem 
                where ControleMovimento = ".$idGerado." AND idEmpresa = ".$idEmpresa,
            null);
            
            $SomaCusto = ExSqlNET(
                "SELECT SUM(ValorCusto * Qtd) AS Total 
                 FROM movimentoitem
                 WHERE idEmpresa = ?
                 AND ControleMovimento = ?",
            null,
            [$idEmpresa, $idGerado]
            );
            
            $SomaCusto = $SomaCusto[0] ?? null; 
            $SomaItens = $SomaItens[0] ?? null; 

            $retornoCC = "";
            if(($SomaItens['Total'] ?? 0) > 0){ //Só cadastra se tiver valor nos itens

              //  if($dadosCadastro['Status'] == 1 ){  // SE JÁ FOI PAGO
              
                    $dadosCadastro['Valor'] = $SomaItens['Total'] ?? 0;
                     $dadosCadastro['Valor'] =  $dadosCadastro['Valor'] - $SomaCusto['Total'] ?? 0;
                     
                    $dadosCadastro['ValorPgto'] = $SomaItens['Total'] ?? 0;
                    $dadosCadastro['ValorPgto'] =  $dadosCadastro['ValorPgto'] - $SomaCusto['Total'] ?? 0;
                    
                    // $dadosCadastro['Valor'] = $SomaItens['Total'] ?? 0;
                    // $dadosCadastro['ValorPgto'] = $SomaItens['Total'] ?? 0;

                    //$dadosCadastro['DataPgto'] = $dadosCadastro['Data'];
                    // $dadosCadastro['DataPgto']  = $dadosCadastro['DataPgto']  . ' ' . date('H:i:s');

                    $dadosCadastro['Data'] = date('Y-m-d H:i:s');
                    $dadosCadastro['UserAlt'] = $_SESSION['usuario_id'];
                    $dadosCadastro['TipoMov'] = "ENTRADA";
                    $dadosCadastro['CaixaGeral'] = 0;
                    $dadosCadastro['TipoDespesa'] = 1;
                    $dadosCadastro['idForcli'] = 0;
                    $dadosCadastro['idServProd'] = 0;

                    $dadosCadastro['idUser'] = $_SESSION['usuario_id']  ?? 0;

                    // if ($_POST['statusPedido'] != 3 && $_POST['statusPedido'] != 2 &&
                    //     $_POST['statusPedido'] != 4){
                            
                        if ($_POST['statusPedido'] != 3 && 
                        $_POST['statusPedido'] != 4){
                            
                        
                        $retornoCC = MovimentoCC($dadosCadastro, "CADASTRAR");
                    }//DÉBITO E ORÇAMENTO E DÉBITO PAGO
                    
                    
               // }
            }

            if ($retornoCC === "") {
                $tipoMsg = "success";
                $msgRetorno = "Movimento cadastrado com sucesso!";        
            }else{
                $tipoMsg = "error";
                $msgRetorno = "Erro ao cadastrar movimento CONTA CORRENTE, contate o administrador! Erro -> " . $retornoCC;
                $_SESSION['mensagem_erro'] = $msgRetorno;
                header('Location: Pedidos');
                exit;
            }

            // Se chegou aqui → sucesso
            if ($retorno === "") {

                $_SESSION['mensagem_sucesso'] = "Movimento cadastrado com sucesso!";
                header('Location: Pedidos');
                exit;

            } else {

                $_SESSION['mensagem_erro'] = "Erro ao cadastrar Movimento -> " . $retorno;
            }
            
            if ($retornoItens === "" ) {
                $msgRetorno = "Movimento cadastrado com sucesso!";
                $tipoMsg = "success";
                $_SESSION['mensagem_sucesso'] = $msgRetorno;
                header('Location: Pedidos');
                exit;

            } else {
                // $msgRetorno = "Erro ao cadastrar o Movimento. Erro -> ". $retornoItens . $retorno;
                $tipoMsg = "error";
                $_SESSION['mensagem_erro'] = $msgRetorno;
            }

        }else if ($Alterando === true && isset($_POST['salvar'])){ //ALTERANDO
                
            $dadosCadastro['id'] = $id;
            $dadosCadastro['idEmpresa'] = $_SESSION['idEmpresa'];
            
            $retorno = Movimento($dadosCadastro, "ATUALIZAR");
            $retornoItensExcluir = MovimentoItem($dadosCadastro, "EXCLUIR");
            
            $dadosCadastro['ControleOrigem'] = $id;
            $retornoCC = MovimentoCC($dadosCadastro, "EXCLUIR");

            $itens = json_decode($_POST['itens'] ?? '[]', true);
            // $idGerado = $_SESSION['idGerado'];

            // file_put_contents(__DIR__ . '/../logs/frmLancamentoAtualizar.txt', print_r($dadosCadastro , true) . "\n", FILE_APPEND);

            // Se existir itens
            if (!empty($itens)) {

                foreach ($itens as $item) {

                    $dadosItem = [
                        'ControleMovimento' => $id,
                        'ServProd' => $item['idProduto'],
                        'Descricao' => $item['descricao'],
                        'Qtd' => $item['qtd'],
                        'ValorCusto' => $item['valorCusto'],
                        'Valor' => $item['valor'],
                        'TotalItem' => $item['total'],
                        'idEmpresa' => $_SESSION['idEmpresa']
                    ];

                    $retornoItens = MovimentoItem($dadosItem, "CADASTRAR");

                    if ($retornoItens != "") {
                        $msgRetorno = "Erro ao cadastrar o Item do Movimento. Erro -> " . $retornoItens;
                        $_SESSION['mensagem_erro'] = $msgRetorno;
                        header('Location: Pedidos');
                        exit;
                    }
                }
            }

            $dadosCadastro['ControleOrigem'] = $id;
            $dadosCadastro['Descricao'] = "LUCRO VENDA PEDIDO";
    
            $SomaItens = ExSqlNET(
                "SELECT SUM(TotalItem) AS Total FROM movimentoitem 
                where ControleMovimento = ".$id." AND idEmpresa = ".$idEmpresa,
            null);

            $SomaCusto = ExSqlNET(
                "SELECT SUM(ValorCusto * Qtd) AS Total 
                 FROM movimentoitem
                 WHERE idEmpresa = ?
                 AND ControleMovimento = ?",
            null,
            [$idEmpresa, $id]
            );
            
            $SomaItens = $SomaItens[0] ?? null;
            $SomaCusto = $SomaCusto[0] ?? null; 

            $retornoCC = "";
            if(($SomaItens['Total'] ?? 0) > 0){ //Só grava movimento conta corrente se valor dos itens for > 0

                // if($dadosCadastro['Status'] == 1 ){  // SE ESTÁ COMO PAGO
                   
                    $dadosCadastro['Valor'] = $SomaItens['Total'] ?? 0;
                     $dadosCadastro['Valor'] =  $dadosCadastro['Valor'] - $SomaCusto['Total'] ?? 0;
                     
                    $dadosCadastro['ValorPgto'] = $SomaItens['Total'] ?? 0;
                    $dadosCadastro['ValorPgto'] =  $dadosCadastro['ValorPgto'] - $SomaCusto['Total'] ?? 0;
                   
                   
                    // $dadosCadastro['Valor'] = $SomaItens['Total'] ?? 0;
                    // $dadosCadastro['ValorPgto'] = $SomaItens['Total'] ?? 0;

                    //$dadosCadastro['DataPgto'] = $dadosCadastro['Data'];
                    // $dadosCadastro['DataPgto']  = $dadosCadastro['DataPgto']  . ' ' . date('H:i:s');
                    // $dadosCadastro['Data'] = date('Y-m-d H:i:s');
                    $dadosCadastro['Data'] = $dadosCadastro['Data'] . ' ' . date('H:i:s');
                    
                    $dadosCadastro['UserAlt'] = $_SESSION['usuario_id'];
                    $dadosCadastro['TipoMov'] = "ENTRADA";
                    $dadosCadastro['CaixaGeral'] = 0;
                    $dadosCadastro['TipoDespesa'] = 1;
                    $dadosCadastro['idForcli'] = 0;
                    $dadosCadastro['idServProd'] = 0;
                    
                    // if ($_POST['statusPedido'] != 3 && $_POST['statusPedido'] != 2 &&
                    //     $_POST['statusPedido'] != 4){
                        if ($_POST['statusPedido'] != 3 &&
                        $_POST['statusPedido'] != 4){
                            
                        $retornoCC = MovimentoCC($dadosCadastro, "CADASTRAR");
                    }//DÉBITO E ORÇAMENTO
                    
                // }

            }

            if ($retorno === "") {

                $_SESSION['mensagem_sucesso'] = "Movimento atualizado com sucesso!";
                header('Location: Pedidos');
                exit;

            } else {
                unset($_SESSION['mensagem_sucesso']);
                $tipoMsg = "error";
                $_SESSION['mensagem_erro'] = "Erro ao atualizar Movimento-> " . $retorno;
                header('Location: Pedidos');
                exit;
            }
            
            if ($retornoItens === "" && $retorno === "" && $retornoCC === "") {
                $msgRetorno = "Movimento atualizado com sucesso!" . $retorno ;
                $tipoMsg = "success";
                $_SESSION['mensagem_sucesso'] = $msgRetorno;
                header('Location: Pedidos');
                exit;

            } else {
                $tipoMsg = "error";
                unset($_SESSION['mensagem_sucesso']);
                $_SESSION['mensagem_erro'] = $msgRetorno;
            }

        }else if(isset($_POST['excluir'])){ //EXCLUINDO 
            $dadosCadastro['ControleOrigem'] = $id;
            $retorno = Movimento($dadosCadastro, "EXCLUIR");
            $retornoItens = MovimentoItem($dadosCadastro, "EXCLUIR");
            $retornoCC = MovimentoCC($dadosCadastro, "EXCLUIR");

            if ($retorno === "" && $retornoItens === "" && $retornoCC === "") {
                $msgRetorno = "Movimento excluído com sucesso!";
                $_SESSION['mensagem_sucesso'] = $msgRetorno;
                $tipoMsg = "success";
                header('Location: Pedidos');
                exit;
            } else {
                $msgRetorno = "Erro ao excluir o Movimento. Erro -> ". $retorno. $retornoItens;
                $tipoMsg = "error";
            }
        }
    } catch (Exception $e) {

        $msgRetorno = "Erro: " . $e->getMessage();
        $tipoMsg = "error";
        $_SESSION['mensagem_+'] = $msgRetorno;
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lançamento de Venda / Prestação de Serviço</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="stylesheet" href="/gerencial/css/home.css">

<style>

    .topo-pagina{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:18px;
        gap:10px;
        flex-wrap:wrap;
    }

    .btn-voltar{
        display:inline-flex;
        align-items:center;
        gap:8px;
        background:#fff;
        color:#f97316;
        border:1px solid #fed7aa;
        padding:10px 16px;
        border-radius:12px;
        font-size:14px;
        font-weight:600;
        text-decoration:none;
        transition:.2s ease;
        box-shadow:0 2px 10px rgba(0,0,0,0.04);
    }

    .btn-voltar:hover{
        background:#f97316;
        color:#fff;
        transform:translateY(-1px);
        box-shadow:0 6px 16px rgba(249,115,22,0.25);
    }
    .btn-secondary {
        background: #fff;
        color: #f97316;
        border: 1px solid #f97316;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-secondary:hover{
        background:#f97316;
        color:#fff;
    }

    .modal-bg {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal {
        background: #fff;
        width: 600px;
        max-width: 95%;
        border-radius: 12px;
        padding: 20px;
        max-height: 80vh;
        overflow: auto;
    }

    .modal h3 {
        margin-top: 0;
    }

    .modal-search {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
    }

    .modal table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal table th,
    .modal table td {
        padding: 8px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .modal table tr:hover {
        background: #fff7ed;
    }

    .total-geral {
        font-size: 18px;
        font-weight: 700;
        text-align: right;
        margin-top: 15px;
    }

    .responsavel-box {
        display: none;
    }

    .btn-remover {
    background-color: #e74c3c;
    color: #fff;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    line-height: 28px;
    text-align: center;
    transition: 0.2s ease;
    }

    .btn-remover:hover {
        background-color: #c0392b;
        transform: scale(1.1);
    }
    
    
    /* ===== MODAL PADRÃO DO SISTEMA ===== */
    
    .modal-bg {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        backdrop-filter: blur(3px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        animation: fadeIn 0.15s ease;
    }
    
    .modal {
        background: #ffffff;
        width: 650px;
        max-width: 95%;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 12px 35px rgba(0,0,0,0.18);
        max-height: 80vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        animation: modalUp 0.18s ease;
    }
    
    .modal h3 {
        margin: 0 0 12px 0;
        font-size: 18px;
        color: #333;
    }
    
    .modal-search {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #dcdcdc;
        font-size: 14px;
        outline: none;
        transition: border 0.2s, box-shadow 0.2s;
    }
    
    .modal-search:focus {
        border-color: #ea580c;
        box-shadow: 0 0 0 2px rgba(234,88,12,0.15);
    }
    
    .modal table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        font-size: 14px;
    }
    
    .modal thead {
        background: #fff7ed;
    }
    
    .modal th {
        text-align: left;
        padding: 10px;
        font-weight: 600;
        color: #9a3412;
    }
    
    .modal td {
        padding: 9px 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    
    .modal tbody {
        overflow-y: auto;
    }
    
    .modal tbody tr {
        transition: background 0.15s;
    }
    
    .modal tbody tr:hover {
        background: #fff7ed;
    }
    
    .modal button {
        margin-top: 15px;
        align-self: flex-end;
    }
    
    @keyframes modalUp {
        from {
            transform: translateY(15px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }



    .modal-table-wrapper{
        max-height: 380px;
        overflow-y: auto;
        margin-top: 10px;
        border: 1px solid #eee;
        border-radius: 8px;
    }
    
    /* deixa o cabeçalho fixo */
    .modal-table-wrapper thead th{
        position: sticky;
        top: 0;
        background: #fff7ed;
        z-index: 2;
    }
    
    /* scroll bonito */
    .modal-table-wrapper::-webkit-scrollbar{
        width: 8px;
    }
    
    .modal-table-wrapper::-webkit-scrollbar-track{
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    .modal-table-wrapper::-webkit-scrollbar-thumb{
        background: #d6d6d6;
        border-radius: 10px;
    }
    
    .modal-table-wrapper::-webkit-scrollbar-thumb:hover{
        background: #bdbdbd;
    }


    /* =========================================
    CARD VEÍCULO - PROFISSIONAL
    ========================================= */

    .card-veiculo{
        background:#fff;
        border:1px solid #e5e7eb;
        border-left:4px solid #f97316;
        border-radius:14px;
        padding:22px;
        box-shadow:0 2px 10px rgba(0,0,0,0.04);
    }

    .header-veiculo{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:14px;
    }

    .header-veiculo h3{
        margin:0;
        font-size:18px;
        font-weight:700;
        color:#111827;
    }

    .header-veiculo p{
        margin:4px 0 0 0;
        font-size:13px;
        color:#6b7280;
    }

    .linha-divisoria{
        height:1px;
        background:#f1f5f9;
        margin-bottom:20px;
    }

    .grid-veiculo{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
        gap:18px;
    }

    .grupo-campo{
        display:flex;
        flex-direction:column;
    }

    .grupo-campo label{
        font-size:13px;
        font-weight:600;
        color:#374151;
        margin-bottom:7px;
    }

    .grupo-campo input,
    .grupo-campo select{
        height:44px;
        border:1px solid #d1d5db;
        border-radius:10px;
        padding:0 14px;
        font-size:14px;
        background:#fff;
        transition:all .2s ease;
        box-sizing:border-box;
    }

    .grupo-campo input:focus,
    .grupo-campo select:focus{
        outline:none;
        border-color:#f97316;
        box-shadow:0 0 0 3px rgba(249,115,22,0.10);
    }

    .grupo-campo input::placeholder{
        color:#9ca3af;
    }
</style>

</head>

<body>

    <?php include __DIR__ . '/../base/navbarUser.php'; ?>

    <div class="content">
          <div class="page-title">Lançamento de Venda / Prestação de Serviço</div>

        <?php
            if ($msgRetorno): ?>
            <div class="alert <?= $tipoMsg ?>">
                <?= htmlspecialchars($msgRetorno) ?>
            </div>
            <?php endif;

            if ($tipoMsg === 'success') {
                $_POST = [];
            }
        ?>

        <?php if ($Alterando && !empty($dados['NomeUsuario'])): ?>
            <div style="
                font-size:12px;
                color:#999;
                text-align:right;
                margin-bottom:15px;
            ">
                👤 <?= htmlspecialchars($dados['NomeUsuario']) ?> • 
                <?= date('d/m/Y H:i', strtotime($dados['DataAltPedido'])) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="topo-pagina">
                <a href=Pedidos class="btn-voltar">
                    ← Voltar para lista
                </a>
            </div>
            <div class="card">
                
                <div class="form-grid">

                    <div>
                        <label>Data</label>
                        <input type="date" name="data" id="data" value="<?= $Alterando ? date('Y-m-d', strtotime($dados['Data'])) : date('Y-m-d') ?>">
                    </div>

                    <div id="dataPagamentoContainer">
                        <label>Data de Pagamento</label>
                        <input type="date" name="dataPgto" id="dataPgto" 
                            value="<?= $Alterando && !empty($dados['DataPgto']) 
                                ? date('Y-m-d', strtotime($dados['DataPgto'])) 
                                : '' ?>">
                    </div>

                    <div>
                        <label>Status</label>
                            <select id="statusPedido" name="statusPedido" onchange="atualizarStatus()">
                                <option value="1" <?= $Alterando && $dados['Status']==1 ? 'selected' : '' ?>>Pago</option>
                                <option value="0" <?= $Alterando && $dados['Status']==0 ? 'selected' : '' ?>>Não Pago</option>
                                <option value="2" <?= $Alterando && $dados['Status']==2 ? 'selected' : '' ?>>Orçamento</option>
                                 <option value="3" <?= $Alterando && $dados['Status']==3 ? 'selected' : '' ?>>Débito</option>
                                  <option value="4" <?= $Alterando && $dados['Status']==4 ? 'selected' : '' ?>>Débito Pago</option>
                            </select>
                    </div>

                    <div>
                        <label>Condição de Pagamento</label>
                        <select name="CondPgto" id="CondPgto">
                            <option value="1" <?= $Alterando && $dados['CondPgto']==1 ? 'selected' : '' ?>>À Vista / Dinheiro</option>
                            <option value="2" <?= $Alterando && $dados['CondPgto']==2 ? 'selected' : '' ?>>Pix</option>
                            <option value="3" <?= $Alterando && $dados['CondPgto']==3 ? 'selected' : '' ?>>Cartão de Crédito</option>
                            <option value="4" <?= $Alterando && $dados['CondPgto']==4 ? 'selected' : '' ?>>Cartão de Débito</option>
                            <option value="5" <?= $Alterando && $dados['CondPgto']==5 ? 'selected' : '' ?>>Cheque</option>
                            <option value="6" <?= $Alterando && $dados['CondPgto']==6 ? 'selected' : '' ?>>30 Dias</option>
                            <option value="7" <?= $Alterando && $dados['CondPgto']==7 ? 'selected' : '' ?>>60 Dias</option>
                        </select>
                    </div>

                    <div>
                        <label>Andamento Processo</label>
                        <select id="statusProcesso" name="statusProcesso">
                            <option value="0" <?= $Alterando && $dados['StatusProcesso']==0 ? 'selected' : '' ?>></option>
                            <option value="1" <?= $Alterando && $dados['StatusProcesso']==1 ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="2" <?= $Alterando && $dados['StatusProcesso']==2 ? 'selected' : '' ?>>Concluído</option>
                        </select>
                    </div>

                </div>

            </div>

            <!-- CLIENTE -->
            <div class="card">

                <label>Cliente</label>

                <div style="display:flex; gap:10px;">
                    <input type="text" id="clienteNome" name="clienteNome"
                        value="<?= $Alterando ? htmlspecialchars($dados['ForcliNome'] ?? '') : '' ?>"
                        readonly>

                    <input type="hidden" id="clienteId" name="clienteId"
                        value="<?= $Alterando ? $dados['ForcliId'] ?? '' : '' ?>">
                    <button type="button" class="btn" onclick="abrirModal('cliente')">Selecionar</button>
                    <input type="hidden" id="clienteDoc" name="clienteDoc">
                </div>

            </div>

            <!-- RESPONSÁVEL -->
            <div class="card responsavel-box" id="responsavelBox">

                <label>Responsável pela Dívida</label>

                <div style="display:flex; gap:10px;">
                <input type="text" id="pagadorNome" name="pagadorNome"
                        value="<?= $Alterando ? htmlspecialchars($dados['PagadorNome'] ?? '') : '' ?>"
                        readonly>

                    <input type="hidden" id="pagadorId" name="pagadorId"
                        value="<?= $Alterando ? $dados['PagadorId'] ?? '' : '' ?>">
                

                    <button type="button" class="btn" onclick="abrirModal('pagador')">Selecionar</button>
                </div>

            </div>
            
            <!-- OBSERVAÇÃO -->
            <div class="card">
            
                <label>Observação</label>
            
                <textarea name="obs" id="obs" rows="3"
                    placeholder="Digite uma observação para este pedido..."
                    style="width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; resize:vertical;"
                ><?= $Alterando ? htmlspecialchars($dados['Obs'] ?? '') : '' ?></textarea>
            
            </div>

            <!-- PRODUTOS -->
            <div class="card">

                <label>Produtos / Serviços</label>

                <button type="button" class="btn" onclick="abrirModal('produto')">
                    Adicionar Produto
                </button>

                <table id="tabelaItens" name="tabelaItens" style="margin-top:15px;">
                    <thead>
                        <tr>
                            <!-- <th>Código</th> -->
                            <th>Descrição</th>
                            <th>Qtd</th>
                            <th>Valor Custo</th>
                            <th>Valor Venda</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="total-geral">
                    Total Geral: <span id="totalGeral">R$ 0,00</span>
                </div>

            </div>

            <div id="dadosVeiculo" class="card card-veiculo" style="display:none; margin-top:25px;">

                <div class="header-veiculo">
                    <div>
                        <h3>Dados do Veículo</h3>
                        <p>Informações vinculadas ao atendimento</p>
                    </div>
                </div>

                <div class="linha-divisoria"></div>

                <div class="grid-veiculo">

                    <div class="grupo-campo">
                        <label>Modelo do Veículo</label>

                        <input 
                            type="text"
                            id="veiculo_modelo"
                            name="veiculo_modelo"
                            placeholder="Informe o modelo do veículo"
                            value="<?= $Alterando ? htmlspecialchars($dados['ModeloVeiculo'] ?? '') : '' ?>"
                        >
                    </div>

                    <div class="grupo-campo">
                        <label>Cor</label>

                        <select name="veiculo_cor" id="veiculo_cor">
                            <option value="" <?= $Alterando && $dados['CorVeiculo']=="" ? 'selected' : '' ?>></option>
                            <option value="Amarelo" <?= $Alterando && $dados['CorVeiculo']=="Amarelo" ? 'selected' : '' ?>>AMARELO</option>
                            <option value="Azul" <?= $Alterando && $dados['CorVeiculo']=="Azul" ? 'selected' : '' ?>>AZUL</option>
                            <option value="Bege" <?= $Alterando && $dados['CorVeiculo']=="Bege" ? 'selected' : '' ?>>BEGE</option>
                            <option value="Branco" <?= $Alterando && $dados['CorVeiculo']=="Branco" ? 'selected' : '' ?>>BRANCO</option>
                            <option value="Cinza" <?= $Alterando && $dados['CorVeiculo']=="Cinza" ? 'selected' : '' ?>>CINZA</option>
                            <option value="Prata" <?= $Alterando && $dados['CorVeiculo']=="Prata" ? 'selected' : '' ?>>PRATA</option>
                            <option value="Preto" <?= $Alterando && $dados['CorVeiculo']=="Preto" ? 'selected' : '' ?>>PRETO</option>
                            <option value="Verde" <?= $Alterando && $dados['CorVeiculo']=="Verde" ? 'selected' : '' ?>>VERDE</option>
                            <option value="Vermelho" <?= $Alterando && $dados['CorVeiculo']=="Vermelho" ? 'selected' : '' ?>>VERMELHO</option>
                            <option value="Laranja" <?= $Alterando && $dados['CorVeiculo']=="Laranja" ? 'selected' : '' ?>>LARANJA</option>
                            <option value="Roxo" <?= $Alterando && $dados['CorVeiculo']=="Roxo" ? 'selected' : '' ?>>ROXO</option>
                        </select>
                    </div>

                    <div class="grupo-campo">
                        <label>Placa</label>

                        <input 
                            type="text"
                            id="veiculo_placa"
                            name="veiculo_placa"
                            placeholder="ABC1D23"
                            style="text-transform:uppercase"
                            value="<?= $Alterando ? htmlspecialchars($dados['PlacaVeiculo'] ?? '') : '' ?>"
                        >
                    </div>

                </div>

            </div>

            <?php if ($Alterando): ?>
                <button 
                    type="submit"
                    name="excluir"
                    class="btn-excluir"
                    onclick="return confirm('Tem certeza que deseja excluir este Pedido? Essa ação não pode ser desfeita.')"
                >
                    Excluir
                </button>
            <?php endif; ?>

            <input type="hidden" name="itens" id="itens">

            <?php if ($Alterando): ?>

            <button class="btn-imprimir"
                    onclick="window.open('Pedido/Imprimir?id=<?= $id ?>', '_blank')">

                🖨 Gerar PDF

            </button>

            <?php endif; ?>

            <button type="submit" name="salvar" id="salvar"
                    class="btn"
                    onclick="return prepararEnvio()">
                Salvar Pedido
            </button>

        </form>

    </div>

    <!-- ================= MODAL ================= -->

    <div class="modal-bg" id="modalBg">
        <div class="modal">

            <h3 id="modalTitulo"></h3>

            <input type="text" id="modalBusca" class="modal-search" placeholder="Digite para buscar...">

                <div class="modal-table-wrapper">
                    <table>
                        <thead>
                            <tr id="modalHead"></tr>
                        </thead>
                        <tbody id="modalBody"></tbody>
                    </table>
                </div>

            <br>
            <button class="btn" onclick="fecharModal()">Fechar</button>

        </div>
    </div>

<script>
        const clientes = <?= json_encode($listaForcli) ?>;
        const produtos = <?= json_encode($listaServProd) ?>;

        let tipoModal = '';

    function prepararEnvio() {

        if (!validarPedido()) return false;

        let itens = [];

        document.querySelectorAll('#tabelaItens tbody tr').forEach(tr => {

            let nome = tr.children[0].innerText;
            let qtd = tr.querySelector('.qtd').value;
            let valorCustoTexto = tr.querySelector('.valorCustoItem').value;
            let valorCusto = moedaParaNumero(valorCustoTexto);
            let valorTexto = tr.querySelector('.valorItem').value;
            let valor = moedaParaNumero(valorTexto);
            let totalTexto = tr.querySelector('.linhaTotal').innerText;
            let total = moedaParaNumero(totalTexto);
            let itemId = tr.dataset.itemId || 0;
            let produtoId = tr.dataset.produtoId || 0;

            itens.push({
                idProduto: produtoId,
                id: itemId,
                descricao: nome,
                qtd: qtd,
                valorCusto: valorCusto,
                valor: valor,
                total: total,
                solicitaVeiculo: tr.dataset.veiculo || 0
            });

        });

        document.getElementById('itens').value = JSON.stringify(itens);

        return true;
    }
    
    function abrirModal(tipo) {
    
        tipoModal = tipo;
    
        document.getElementById('modalBg').style.display = 'flex';
    
        let input = document.getElementById('modalBusca');
    
        input.value = '';
    
        carregarModal('');
    
        // foca no campo de busca
        setTimeout(() => {
            input.focus();
        }, 50);

    }

    function fecharModal() {
        document.getElementById('modalBg').style.display = 'none';
    }

    document.getElementById('modalBusca').addEventListener('input', function() {
        carregarModal(this.value.toLowerCase());
    });

    function verificarVeiculoObrigatorio() {

        let linhas = document.querySelectorAll('#tabelaItens tbody tr');

        let precisaVeiculo = false;

        linhas.forEach(linha => {
            if (linha.dataset.veiculo == "1") {
                precisaVeiculo = true;
            }
        });

        let box = document.getElementById('dadosVeiculo');

        if (precisaVeiculo) {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    function carregarModal(filtro) {

        let head = document.getElementById('modalHead');
        let body = document.getElementById('modalBody');

        head.innerHTML = '';
        body.innerHTML = '';

        if (tipoModal === 'cliente' || tipoModal === 'pagador') {

            document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';

            head.innerHTML = '<th>Nome</th><th>Documento</th>';

            clientes
                .filter(c => c.Nome.toLowerCase().includes(filtro))
                .forEach(c => {

                    body.innerHTML += `
            <tr onclick="selecionarCliente(${c.Id}, '${c.Nome.replace(/'/g,"\\'")}')">
                <td>${c.Nome}</td>
                <td>${c.Documento ?? ''}</td>
            </tr>`;
                });

        }

        if (tipoModal === 'produto') {

            document.getElementById('modalTitulo').innerText = 'Selecionar Produto';

            // head.innerHTML = '<th>Código</th><th>Nome</th><th>Valor</th>';
            head.innerHTML = '<th>Nome</th><th>Valor</th>';
            //   <td>${p.Codigo}</td>
            produtos
                .filter(p => p.Nome.toLowerCase().includes(filtro))
                .forEach(p => {

                    body.innerHTML += `
                    <tr onclick="selecionarProduto(
                        ${p.Id},
                        '${p.Codigo}',
                        '${p.Nome.replace(/'/g,"\\'")}',
                         ${p.ValorCusto ?? 0},
                        ${p.ValorVenda ?? 0},
                        ${p.SolicitaVeiculo ?? 0}
                    )">
                      
                        <td>${p.Nome}</td>
                        <td>R$ ${parseFloat(p.ValorVenda ?? 0).toFixed(2)}</td>
                    </tr>`;
                });
        }

    }

    function selecionarCliente(id, nome) {
        if (tipoModal === 'cliente') {
            document.getElementById('clienteId').value = id;
            document.getElementById('clienteNome').value = nome;

            const status = document.getElementById('statusPedido').value;
            if (status === '0') {
                document.getElementById('pagadorId').value = id;
                document.getElementById('pagadorNome').value = nome;

                document.getElementById('responsavelBox').style.display = 'block';
            }

        } else { // pagador
            document.getElementById('pagadorId').value = id;
            document.getElementById('pagadorNome').value = nome;
        }

        fecharModal();
    }

    function selecionarProduto(id, codigo, nome, valorCusto, valor, solicitaVeiculo) {

        let tbody = document.querySelector('#tabelaItens tbody');
        let linha = `
        <tr 
            data-produto-id="${id}" 
            data-item-id="0" 
            data-veiculo="${solicitaVeiculo}"
        >

            <td>${nome}</td>

            <td>
                <input type="number"
                    class="qtd"
                    value="1"
                    min="1"
                    onchange="atualizarLinha(this)">
            </td>

            <td>
                <input type="text"
                    class="valorCustoItem"
                    value="${formatarMoeda(valorCusto)}"
                    oninput="mascaraMoeda(this)"
                    onchange="atualizarLinha(this)">
            </td>
            
            <td>
                <input type="text"
                    class="valorItem"
                    value="${formatarMoeda(valor)}"
                    oninput="mascaraMoeda(this)"
                    onchange="atualizarLinha(this)">
            </td>

            <td class="linhaTotal">
                ${formatarMoeda(valor)}
            </td>

            <td>
                <button class="btn-remover" type="button"
                    onclick="removerLinha(this)">×</button>
            </td>

        </tr>`;


        //tbody.innerHTML += linha;
        tbody.insertAdjacentHTML('beforeend', linha);

        calcularTotal();
        verificarVeiculoObrigatorio();
        fecharModal();
    }

    function moedaParaNumero(valor) {
        if (!valor) return 0;

        return parseFloat(
            valor
                .replace(/\s/g, '')
                .replace(/\./g, '')
                .replace(',', '.')
        ) || 0;
    }


    function mascaraMoeda(campo) {

        let valor = campo.value.replace(/\D/g, '');

        if (valor === '') {
            campo.value = '0,00';
            return;
        }

        valor = (parseInt(valor) / 100).toFixed(2) + '';
        valor = valor.replace('.', ',');
        valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        campo.value = valor;
    }

    function atualizarLinha(input) {

        let linha = input.closest('tr');
        let qtd = parseFloat(linha.querySelector('.qtd').value) || 0;
        let valorCustoTexto = linha.querySelector('.valorCustoItem').value;
        let valorCusto = moedaParaNumero(valorCustoTexto);
        let valorTexto = linha.querySelector('.valorItem').value;
        let valor = moedaParaNumero(valorTexto);
        let total = qtd * valor;

        linha.querySelector('.linhaTotal').innerText =
            total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

        calcularTotal();
    }


    function removerLinha(btn) {
        btn.closest('tr').remove();
        calcularTotal();
        verificarVeiculoObrigatorio();
    }

    function validarPedido() {

        let box = document.getElementById('dadosVeiculo');

        if (box.style.display === 'block') {

            let modelo = document.querySelector('[name="veiculo_modelo"]').value;
            let placa = document.querySelector('[name="veiculo_placa"]').value;

        }

        return true;
    }

    function calcularTotal() {

        let totalGeral = 0;

        document.querySelectorAll('.linhaTotal').forEach(td => {

            let valorTexto = td.innerText;

            let valor = parseFloat(
                valorTexto
                    .replace(/\./g, '')  
                    .replace(',', '.')  
            ) || 0;

            totalGeral += valor;
        });

        document.getElementById('totalGeral').innerText =
            totalGeral.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
    }


    document.getElementById('statusPedido').addEventListener('change', function() {

        let status = this.value;
        let box = document.getElementById('responsavelBox');
        let pagadorId = document.getElementById('pagadorId').value;
        let alterando = <?= $Alterando ? 'true' : 'false' ?>;

        let dataPagamentoContainer = document.getElementById('dataPagamentoContainer');

        if (status === '0') {

            box.style.display = 'block';

 
            if (!pagadorId) {
                let clienteId = document.getElementById('clienteId').value;
                let clienteNome = document.getElementById('clienteNome').value;

                if (clienteId && clienteNome) {
                    document.getElementById('pagadorId').value = clienteId;
                    document.getElementById('pagadorNome').value = clienteNome;
                }
            }

            dataPagamentoContainer.style.display = 'none';
            document.getElementById('dataPgto').value = '';

        } else if (status === '1') {
          
            dataPagamentoContainer.style.display = 'block';

            if (pagadorId) {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
                document.getElementById('pagadorId').value = '';
                document.getElementById('pagadorNome').value = '';
            }

        } else {

            box.style.display = 'none';
            document.getElementById('pagadorId').value = '';
            document.getElementById('pagadorNome').value = '';
            dataPagamentoContainer.style.display = 'none';
            document.getElementById('dataPgto').value = '';
        }

    });

    function formatarCampoMoeda(campo) {

        let valor = campo.value;
        valor = valor.replace(/[^\d,]/g, '');
        let numero = parseFloat(valor.replace(',', '.'));
        if (isNaN(numero)) numero = 0;

        campo.value = numero.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }


    function formatarMoeda(valor) {
        return parseFloat(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function adicionarItemExistente(idProduto, nome, qtd, valorCusto, valor, solicitaVeiculo, idItemBanco = 0) {

        let tbody = document.querySelector('#tabelaItens tbody');

        let total = qtd * valor;

        let linha = `
            <tr 
                data-produto-id="${idProduto}" 
                data-item-id="${idItemBanco}" 
                data-veiculo="${solicitaVeiculo || 0}"
            >

                <td>${nome}</td>

                <td>
                    <input type="number"
                        class="qtd"
                        value="${qtd}"
                        min="1"
                        onchange="atualizarLinha(this)">
                </td>

                <td>
                    <input type="text"
                        class="valorCustoItem"
                        value="${formatarMoeda(valorCusto)}"
                        oninput="mascaraMoeda(this)"
                        onchange="atualizarLinha(this)">
                </td>


                <td>
                    <input type="text"
                        class="valorItem"
                        value="${formatarMoeda(valor)}"
                        oninput="mascaraMoeda(this)"
                        onchange="atualizarLinha(this)">
                </td>

                <td class="linhaTotal">
                    ${formatarMoeda(total)}
                </td>

                <td>
                    <button class="btn-remover"
                        type="button"
                        onclick="removerLinha(this)">×</button>
                </td>
            </tr>`;

        tbody.insertAdjacentHTML('beforeend', linha);
    }

    const itensExistentes = <?= json_encode($dadosItens ?? []) ?>;

    document.addEventListener("DOMContentLoaded", function() {

        const statusSelect = document.getElementById('statusPedido');
        const box = document.getElementById('responsavelBox');

        if (!statusSelect || !box) {
            console.log("Elemento não encontrado");
            return;
        }

        function atualizarResponsavel() {

            let status = statusSelect.value;
            let pagadorId = document.getElementById('pagadorId').value;
            let alterando = <?= $Alterando ? 'true' : 'false' ?>;

            if (status === '0') {
                box.style.display = 'block';
            } else {
                if (alterando && pagadorId) {
                    box.style.display = 'block';
                } else {
                    box.style.display = 'none';
                }
            }
        }
     
        atualizarResponsavel();

        // ===== CARREGAR ITENS EXISTENTES =====
        if (itensExistentes && itensExistentes.length > 0) {

            itensExistentes.forEach(item => {

                adicionarItemExistente(
                    item.ServProd || item.IdServProd || 0,   // id produto (ajuste se nome diferente)
                    item.Descricao || item.Nome || '',      // nome
                    parseFloat(item.Qtd || 1),              // quantidade
                    parseFloat(item.ValorCusto || item.ValorCusto || 0), // valorCusto
                    parseFloat(item.ValorUnitario || item.Valor || 0), // valor
                    item.SolicitaVeiculo || 0               // flag veículo
                );

            });

            calcularTotal();
            verificarVeiculoObrigatorio();
        }

        // ===== MOSTRAR VEÍCULO SE JÁ EXISTIR DADOS =====
        const modelo = document.getElementById('veiculo_modelo').value;
        const placa = document.getElementById('veiculo_placa').value;

        if (modelo || placa) {
            document.getElementById('dadosVeiculo').style.display = 'block';
        }

    });
    
    const dataPagamentoContainer = document.getElementById('dataPagamentoContainer');

    function atualizarStatus() {
        
        let statusSelect = document.getElementById('statusPedido');
        let status = statusSelect.value;
    
        let box = document.getElementById('responsavelBox');
        let pagadorIdEl = document.getElementById('pagadorId');
        let pagadorNomeEl = document.getElementById('pagadorNome');
        let dataPagamentoContainer = document.getElementById('dataPagamentoContainer');
        let dataPgto = document.getElementById('dataPgto');
    
        let pagadorId = pagadorIdEl.value;
    
        if (status === '0') { 
            box.style.display = 'block';
    
            dataPagamentoContainer.style.display = 'none';
            dataPgto.value = '';
            dataPgto.removeAttribute('required');
    
            if (!pagadorId) {
                let clienteId = document.getElementById('clienteId').value;
                let clienteNome = document.getElementById('clienteNome').value;
    
                if (clienteId && clienteNome) {
                    pagadorIdEl.value = clienteId;
                    pagadorNomeEl.value = clienteNome;
                }
            }
    
        } else if (status === '1') { 
    
            dataPagamentoContainer.style.display = 'block';
            dataPgto.setAttribute('required', 'required');
    
            if (pagadorId) {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
                pagadorIdEl.value = '';
                pagadorNomeEl.value = '';
            }
    
        } else {
    
            box.style.display = 'none';
            pagadorIdEl.value = '';
            pagadorNomeEl.value = '';
    
            dataPagamentoContainer.style.display = 'none';
            dataPgto.value = '';
            dataPgto.removeAttribute('required');
        }
    }

    window.addEventListener('DOMContentLoaded', atualizarStatus);

    document.getElementById("modalBg").addEventListener("click", function(e){
    
        if(e.target === this){
            fecharModal();
        }
    
    });

    //FECHANDO QUALQUER MODAL
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModal();
        }
    });

</script>

</body>

</html>