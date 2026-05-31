<?php

require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];

$msg = '';
$tipoMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $valor = isset($_POST['valor']) && $_POST['valor'] !== '' ? formataValorGravacao($_POST['valor']) : 0;
    $descricao = $_POST['descricao'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $dataHoraAtual = date('Y-m-d H:i:s');

    $dados['idEmpresa'] = $idEmpresa;

    $dados['TipoDespesa'] = $_POST['TipoDespesa'] ?? 0;
    $dados['Descricao'] = $descricao ?? '';
    $dados['Valor'] = $valor ?? 0;
    $dados['Data'] = $dataHoraAtual ?? date('Y-m-d H:i:s');
    $dados['idForcli'] = $_POST['idForcli'] ?? 0;
    $dados['idServProd'] = $_POST['idServProd'] ?? 0;
    $dados['DataPgto'] = $dataHoraAtual ?? date('Y-m-d H:i:s');
    $dados['ValorPgto'] = $valor ?? 0;
    $dados['UserAlt'] = $_SESSION['usuario_id'] ?? 0;
    $dados['TipoMov'] = $tipo ?? '';
    $dados['CaixaGeral'] = $_POST['CaixaGeral'] ?? 1;
    $dados['ControleOrigem'] = $_POST['ControleOrigem'] ?? 0;

    $dados['idUser'] = $_SESSION['usuario_id']  ?? 0;

    $dados['Controle'] = !empty($_POST['idExcluir']) ? $_POST['idExcluir'] : 0;
    $dados['idUser'] = $_SESSION['usuario_id'];
        
    // ================= EXCLUIR =================
    if (isset($_POST['idExcluir']) && $_POST['idExcluir'] != '') {

        $retorno = MovimentoCC($dados,"EXCLUIRCONTROLE");

        if ($retorno === "") {
            $tipoMsg = "success";
            $msg = "Movimento excluído com sucesso!";
        } else {
            $tipoMsg = "error";
            $msg = "Erro ao excluir movimento: ".$retorno;
        }

    }else if (isset($_POST['valor']) && isset($_POST['descricao']) && isset($_POST['tipo'])) { // // ================= SALVAR =================

       
        $retorno = MovimentoCC($dados, "CADASTRAR");

        if ($retorno === "") {
            $tipoMsg = "success";
            $msg = "Movimento registrado com sucesso!";
        } else {
            $tipoMsg = "error";
            $msg = "Erro ao cadastrar movimento, contate o administrador! Erro: ".$retorno;
        }

    }
}
// ================= TOTAL GERAL =================

$resumo = ExSqlNET("
    SELECT 
        SUM(CASE WHEN TipoMov = 'ENTRADA' THEN Valor ELSE 0 END) entradas,
        SUM(CASE WHEN TipoMov = 'SAIDA' THEN Valor ELSE 0 END) saidas,
        SUM(CASE WHEN TipoMov = 'AJUSTE' THEN Valor ELSE 0 END) ajustes
    FROM movimentocc
    WHERE idEmpresa = ?
    AND CaixaGeral = 1 
", null, [$idEmpresa]);

$entradas = $resumo[0]['entradas'] ?? 0;
$saidas   = $resumo[0]['saidas'] ?? 0;
$ajustes  = $resumo[0]['ajustes'] ?? 0;

$totalGeral = ($entradas - $saidas) + $ajustes;


// ================= HISTÓRICO =================

$lista = ExSqlNET("
    SELECT Controle, Data, Descricao, Valor, TipoMov
    FROM movimentocc
    WHERE idEmpresa = ?
    AND CaixaGeral = 1 
    ORDER BY Data DESC
", null, [$idEmpresa]);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Financeiro Geral</title>
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">

    <style>
    /* ===========================
        TÍTULO
        =========================== */

        .content h2 {
            margin-bottom: 20px;
            font-weight: 600;
            color: #333;
        }

        /* ===========================
        CARDS
        =========================== */

        .card {
            background: #ffffff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border: 1px solid #eee;
        }

        .card h3 {
            margin-bottom: 15px;
            font-weight: 600;
            color: #444;
        }

        /* ===========================
        SALDO
        =========================== */

        .card[style*="text-align:center"] {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
        }

        .card[style*="text-align:center"] h3 {
            color: rgba(255,255,255,0.9);
        }

        .card[style*="text-align:center"] div {
            font-size: 42px !important;
            font-weight: bold;
            margin-top: 10px;
        }

        /* ===========================
        FORMULÁRIO
        =========================== */

        form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        form select,
        form input {
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        form select:focus,
        form input:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 2px rgba(249,115,22,0.2);
        }

        /* ===========================
        BOTÃO
        =========================== */

        .btn {
            background: #f97316;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn:hover {
            background: #ea580c;
            transform: translateY(-1px);
        }

        /* ===========================
        TABELA
        =========================== */

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th {
            background: #f97316;
            color: white;
            padding: 12px;
            font-weight: 600;
            font-size: 14px;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        table tr:nth-child(even) {
            background: #fafafa;
        }

        table tr:hover {
            background: #f3f4f6;
            transition: 0.2s;
        }

        /* ===========================
        ALERTAS
        =========================== */

        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .modal{
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.45);
            justify-content:center;
            align-items:center;
            z-index:999;
        }
        
        .modal-content{
            background:#fff;
            padding:30px;
            border-radius:10px;
            width:360px;
            max-width:90%;
            text-align:center;
            box-shadow:0 15px 40px rgba(0,0,0,0.2);
            animation:modalFade .2s ease;
        }
        
        .modal-content h3{
            margin-bottom:10px;
            color:#333;
        }
        
        .modal-content p{
            margin-bottom:20px;
            color:#555;
            font-size:14px;
        }
        
        .modal-botoes{
            display:flex;
            justify-content:center;
            gap:10px;
        }
        
        /* botão cancelar */
        .btnCancelar{
            background:#e5e7eb;
            border:none;
            padding:9px 18px;
            border-radius:6px;
            cursor:pointer;
            font-weight:500;
        }
        
        .btnCancelar:hover{
            background:#d1d5db;
        }
        
        /* botão excluir */
        .btnExcluirConfirmar{
            background:#dc2626;
            border:none;
            color:white;
            padding:9px 18px;
            border-radius:6px;
            cursor:pointer;
            font-weight:600;
        }
        
        .btnExcluirConfirmar:hover{
            background:#b91c1c;
        }
        
        .btnExcluir{
            background:#dc2626;
            border:none;
            color:white;
            padding:6px 10px;
            border-radius:6px;
            cursor:pointer;
        }
        
        .btnExcluir:hover{
            background:#b91c1c;
        }
        
        @keyframes modalFade{
            from{
                transform:scale(.9);
                opacity:0;
            }
            to{
                transform:scale(1);
                opacity:1;
            }
        }
    
        .modal-content form{
            display:block;
        }
        
        .modal-botoes{
            display:flex;
            justify-content:center;
            align-items:center;
            gap:12px;
            margin-top:10px;
        }

        /* ===========================
        RESPONSIVO
        =========================== */

        @media (max-width: 768px) {

            form {
                flex-direction: column;
            }

            form select,
            form input,
            .btn {
                width: 100%;
            }

            table th,
            table td {
                font-size: 13px;
                padding: 8px;
            }

        }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../base/navbarUser.php'; ?>

    <div class="content">

        <div class="page-header">
            <div class="page-title-wrap">
                <h1 class="page-title">
                    Financeiro Geral
                </h1>
            </div>
        </div>

        <!-- <?php if($msg): ?>
        <div class="alert success"><?= $msg ?></div>
        <?php endif; ?> -->

        <?php
            if ($msg): ?>
            <div class="alert <?= $tipoMsg ?>">
                <?= htmlspecialchars($msg) ?>
            </div>
            <?php endif;

            if ($tipoMsg === 'success') {
                $_POST = [];
            }
        ?>

        <div class="card" style="text-align:center">

            <h3>Saldo Total Empresa</h3>

            <!-- <div style="font-size:40px;font-weight:bold;color:#16a34a">
                R$ <?= number_format($totalGeral,2,',','.') ?>
            </div> -->

            <div style="
                font-size:40px;
                font-weight:bold;
                color:#ffffff;
                border:3px solid #ffffff;
                border-radius:12px;
                padding:15px 30px;
                display:inline-block;
            ">
                R$ <?= number_format($totalGeral,2,',','.') ?>
            </div>
        </div>


        <div class="card">

            <h3>Movimentar</h3>

            <form method="post">

                <select name="tipo" required>
                    <option value="ENTRADA">Adicionar</option>
                    <option value="SAIDA">Retirar</option>
                    <option value="AJUSTE">Ajuste</option>
                </select>

                <input type="text" name="valor" id="valor" placeholder="Valor" required>

                <input type="text" name="descricao" placeholder="Descrição" required>

                <button class="btn">Salvar</button>

            </form>

        </div>


        <div class="card">

            <h3>Histórico</h3>

            <table>

                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>

                <!-- <?php foreach($lista as $l): ?>

                <tr>

                    <td><?= date('d/m/Y H:i', strtotime($l['Data'])) ?></td>
                    <td><?= $l['TipoMov'] ?></td>
                    <td><?= htmlspecialchars($l['Descricao']) ?></td>
                    <td>R$ <?= number_format($l['Valor'],2,',','.') ?></td>

                </tr>

                <?php endforeach; ?> -->

                <?php foreach($lista as $l): 

                    $cor = '#000';
                    $sinal = '';

                    if ($l['TipoMov'] == 'ENTRADA') {
                        $cor = '#16a34a'; 
                        $sinal = '+ ';
                    } elseif ($l['TipoMov'] == 'SAIDA') {
                        $cor = '#dc2626'; 
                        $sinal = '- ';
                    } elseif ($l['TipoMov'] == 'AJUSTE') {
                        $cor = '#f59e0b'; 
                        $sinal = '± ';
                    }

                ?>

                <tr>

                    <td><?= date('d/m/Y H:i', strtotime($l['Data'])) ?></td>

                    <td style="color:<?= $cor ?>;font-weight:bold">
                        <?= $l['TipoMov'] ?>
                    </td>

                    <td><?= htmlspecialchars($l['Descricao']) ?></td>

                    <td style="color:<?= $cor ?>;font-weight:bold">
                        <?= $sinal ?>R$ <?= number_format($l['Valor'],2,',','.') ?>
                    </td>

                    <td>
                        <button 
                            type="button"
                            class="btnExcluir"
                            onclick="abrirModalExcluir(<?= $l['Controle'] ?>,'<?= htmlspecialchars($l['Descricao'],ENT_QUOTES) ?>')"
                        >
                            🗑
                        </button>
                    </td>

                </tr>

                <?php endforeach; ?>


            </table>

            <div id="modalExcluir" class="modal">
            
                <div class="modal-content">
            
                    <h3>Excluir lançamento</h3>
            
                    <p id="textoExcluir"></p>
            
                    <form method="post">
            
                        <input type="hidden" name="idExcluir" id="idExcluir">
            
                        <div class="modal-botoes">
            
                            <button type="button" class="btnCancelar" onclick="fecharModal()">
                                Cancelar
                            </button>
            
                            <button type="submit" class="btnExcluirConfirmar">
                                Excluir
                            </button>
            
                        </div>
            
                    </form>
            
                </div>
            
            </div>

        </div>

    </div>
</body>

</html>

<script>

function abrirModalExcluir(id, descricao){

    document.getElementById("modalExcluir").style.display = "flex";

    document.getElementById("idExcluir").value = id;

    document.getElementById("textoExcluir").innerText =
        "Deseja excluir o lançamento: " + descricao + "?";
}

function fecharModal(){
    document.getElementById("modalExcluir").style.display = "none";
}

window.onclick = function(event) {
    let modal = document.getElementById("modalExcluir");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

//======================================================================
function formatarMoeda(campo) {
    let v = campo.value.replace(/\D/g, '');

    v = (v / 100).toFixed(2) + '';
    v = v.replace('.', ',');
    v = v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    campo.value = v;
}

//======================================================================
document.getElementById('valor')?.addEventListener('input', function () {
    formatarMoeda(this);
});
</script>
