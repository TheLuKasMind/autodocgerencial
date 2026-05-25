<?php
include '../base/baseFuncoes.php'; 
require_once '../base/connection.php'; 
require_once '../base/verificaPlano.php';

$msgRetorno = "";
$tipoMsg = "";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];

$listaForcli = ExSqlNET("
    SELECT *,
    CASE Tipo 
        WHEN 1 THEN 'Despachante' 
        WHEN 2 THEN 'Revenda' 
        WHEN 3 THEN 'Fornecedor'
        WHEN 4 THEN 'Consumidor Final'
        WHEN 5 Then 'Desmanche'
    END AS TipoLiteral,
    (
        SELECT IFNULL(SUM(mi.TotalItem),0)
        FROM movimentoitem mi
        INNER JOIN movimento m 
            ON m.id = mi.ControleMovimento
        WHERE m.Forcli = forcli.id
          AND m.idEmpresa = ".$idEmpresa."
          AND m.Status = 0
    ) AS TotalDevedor

    FROM forcli 
    WHERE idEmpresa = ".$idEmpresa."
 ORDER BY forcli.Nome ASC");

?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Clientes - Autodoc</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">
    <!-- CSS BASE DO SISTEMA -->
    <link rel="stylesheet" href="../css/base.css?v=15"> 

 
</head>
<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">
    <div class="page-title">Clientes / Fornecedores</div>
    <div class="subtitle">
        Cadastro de clientes e fornecedores.
    </div>
    <?php

        if (isset($_SESSION['mensagem_sucesso'])) {
            $msgRetorno = $_SESSION['mensagem_sucesso'];
            $tipoMsg = "success";
            unset($_SESSION['mensagem_sucesso']);
        }
        if (isset($_SESSION['mensagem_erro'])) {
            $msgRetorno = $_SESSION['mensagem_erro'];
            $tipoMsg = "error";
            unset($_SESSION['mensagem_erro']);
        }

        if ($msgRetorno): ?>
        <div class="alert <?= $tipoMsg ?>">
            <?= htmlspecialchars($msgRetorno) ?>
        </div>
        <?php endif;

        if ($tipoMsg === 'success') {
            $_POST = [];
        }
    ?>

    <div class="actions">
        <a href="frmForcli.php" class="btn">Novo Cadastro</a>
    </div>

    <div class="card">
        
        <div style="margin-bottom:15px; position:relative;">

            <span style="
                position:absolute;
                left:10px;
                top:50%;
                transform:translateY(-50%);
                font-size:14px;
                color:#64748b;
            ">🔎</span>
        
            <input 
                type="text"
                id="filtroNome"
                placeholder="Buscar cliente ou fornecedor..."
                style="
                    width:100%;
                    padding:8px 8px 8px 32px;
                    border:1px solid #e5e7eb;
                    border-radius:6px;
                    font-size:14px;
                "
                onkeyup="filtrarTabela()"
                autofocus
            >
    
        </div>

    <table>
        <thead>
            <tr>
                <th>Nome / Razão Social</th>
                <th>Documento</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Total Devedor</th>
            </tr>
        </thead>

        <tbody>

        <?php if (!empty($listaForcli)): ?>
            <?php foreach ($listaForcli as $cli): ?>

                <tr onclick="abrirForcli(<?= (int)$cli['id'] ?>)">

                    <td><?= htmlspecialchars($cli['Nome']) ?></td>

                    <td><?= htmlspecialchars($cli['Documento']) ?></td>

                    <td><?= htmlspecialchars($cli['TipoLiteral']) ?></td>

                    <td>
                        <?= $cli['Inativo'] ? 'Inativo' : 'Ativo' ?>
                    </td>

                    <td class="<?= $cli['TotalDevedor'] > 0 ? 'text-danger' : 'text-success' ?>">
                        R$ <?= number_format($cli['TotalDevedor'], 2, ',', '.') ?>
                    </td>

                </tr>

            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center;">
                    Nenhum cliente cadastrado.
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>

    </div>
</div>

<script>
function abrirForcli(id) {
    window.location.href = 'frmForcli.php?id=' + id;
}


window.onload = function(){
    let campo = document.getElementById("filtroNome");
    campo.focus();
    campo.select();
}

function filtrarTabela(){

    let input = document.getElementById("filtroNome");
    let filtro = input.value.toLowerCase();

    let linhas = document.querySelectorAll("table tbody tr");

    linhas.forEach(function(linha){

        let nome = linha.cells[0].textContent.toLowerCase();
        let documento = linha.cells[1].textContent.toLowerCase();

        if(nome.includes(filtro) || documento.includes(filtro)){
            linha.style.display = "";
        }else{
            linha.style.display = "none";
        }

    });

}

function abrirForcli(id) {
    window.location.href = 'frmForcli.php?id=' + id;
}

</script>

</body>
</html>
