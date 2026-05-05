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

$listaServProd = ExSqlNET("
    SELECT *, 
    CASE TIPO 
        WHEN 1 THEN 'Serviço' 
        WHEN 2 THEN 'Produto' 
    END AS TipoLiteral
    FROM servprod
    WHERE idEmpresa = ". $idEmpresa ."
    ORDER BY nome asc
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos / Serviços</title>

<link rel="stylesheet" href="../css/base.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../img/favicon.png">
<style>

    .progress-bar {
        background: #e5e7eb;
        border-radius: 6px;
        height: 10px;
        width: 100%;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 6px;
        transition: 0.3s;
    }

    .meta-text {
        font-size: 12px;
        margin-top: 4px;
        font-weight: 600;
    }

    .meta-low { background: #dc2626; }      /* vermelho */
    .meta-medium { background: #f59e0b; }   /* amarelo */
    .meta-good { background: #16a34a; }     /* verde */
    .meta-excel { background: #7c3aed; }    /* roxo */

</style>

</head>
<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">Produtos / Serviços</div>
    <div class="subtitle">Cadastro de produtos e serviços.</div>

    <div class="actions">
        <a href="frmServProd.php" class="btn">Novo Cadastro</a>
    </div>
    <?php

        if (isset($_SESSION['mensagem_sucesso'])) {
            $msgRetorno = $_SESSION['mensagem_sucesso'];
            $tipoMsg = "success";
            unset($_SESSION['mensagem_sucesso']);
        }
        if (isset($_SESSION['mensagem_erro'])) {
            $msgRetorno = $_SESSION['mensagem_erro'];
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

    <div class="card">

        <input 
            type="text" 
            id="filtroNome" 
            placeholder="🔎 Buscar produto ou serviço..."
            class="input"
            onkeyup="filtrarTabela()"
            autofocus
        >

        <table>

            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Meta Mensal</th>
                    <th>Qtd Vendida</th>
                    <th>Progresso</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($listaServProd as $item): ?>

                <?php
                $id = $item['id'];
                $meta = !empty($item['MetaMensal']) ? (int)$item['MetaMensal']: 0;
                
                $dadosMeta = ExSqlNET("
                    SELECT SUM(Qtd) AS Total
                    FROM movimentoitem
                    LEFT JOIN movimento 
                        ON movimento.id = movimentoitem.ControleMovimento
                    WHERE ServProd = ?
                    AND movimento.idEmpresa = ?
                    AND MONTH(Data) = MONTH(CURDATE())
                    AND YEAR(Data) = YEAR(CURDATE())
                ", null, [$id, $idEmpresa]);

                $qtdVendida = $dadosMeta[0]['Total'] ?? 0;

                $percentual = 0;
                if ($meta > 0) {
                    $percentual = ($qtdVendida / $meta) * 100;
                }

                $percentualBarra = min(100, $percentual);

                // COR DA META
                $classeCor = 'meta-low';

                if ($percentual >= 150) {
                    $classeCor = 'meta-excel';
                } elseif ($percentual >= 100) {
                    $classeCor = 'meta-good';
                } elseif ($percentual >= 50) {
                    $classeCor = 'meta-medium';
                }
                ?>

                <tr onclick="abrirProduto(<?= (int)$item['id'] ?>)">

                    <td><?= htmlspecialchars($item['Nome']) ?></td>

                    <td><?= htmlspecialchars($item['TipoLiteral']) ?></td>

                    <td><?= number_format($meta, 0, ',', '.') ?></td>

                    <td><?= number_format($qtdVendida, 0, ',', '.') ?></td>

                    <td style="width:220px">

                        <div class="progress-bar">
                            <div class="progress-fill <?= $classeCor ?>"
                                 style="width: <?= $percentualBarra ?>%">
                            </div>
                        </div>

                        <div class="meta-text">
                            <?= number_format($percentual, 1, ',', '.') ?>%
                        </div>

                    </td>

                    <td class="<?= $item['Inativo'] ? 'status-inativo' : 'status-ativo' ?>">
                        <?= $item['Inativo'] ? 'Inativo' : 'Ativo' ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<script>
function abrirProduto(id) {
    window.location.href = 'frmServProd.php?id=' + id;
}


function filtrarTabela(){

    let input = document.getElementById("filtroNome");
    let filtro = input.value.toLowerCase();

    let tabela = document.querySelector("table tbody");
    let linhas = tabela.querySelectorAll("tr");

    linhas.forEach(function(linha){

        let nome = linha.cells[0].textContent.toLowerCase();

        if(nome.includes(filtro)){
            linha.style.display = "";
        }else{
            linha.style.display = "none";
        }

    });

}


</script>

</body>
</html>
