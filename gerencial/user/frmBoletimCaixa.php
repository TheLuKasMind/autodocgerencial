<?php
include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}
date_default_timezone_set('America/Sao_Paulo');

$idEmpresa = $_SESSION['idEmpresa'];
$dataHoje = date('Y-m-d');

$dataInicial = $_GET['dataInicial'] ?? date('Y-m-d');
$dataFinal   = $_GET['dataFinal'] ?? date('Y-m-d');

// ================= CAIXA HOJE =================

// $resumo = ExSqlNET("
//     SELECT 
//         SUM(CASE WHEN TipoMov = 'ENTRADA' THEN Valor ELSE 0 END) entradas,
//         SUM(CASE WHEN TipoMov = 'SAIDA' THEN Valor ELSE 0 END) saidas
//     FROM movimentocc
//     WHERE idEmpresa = ?
//     AND CaixaGeral = 0
//     AND DATE(Data) = ?
// ", null, [$idEmpresa, $dataHoje]);

// $resumo = ExSqlNET("
//     SELECT 
//         SUM(CASE WHEN TipoMov = 'ENTRADA' THEN Valor ELSE 0 END) entradas,
//         SUM(CASE WHEN TipoMov = 'SAIDA' THEN Valor ELSE 0 END) saidas
//     FROM movimentocc
//     WHERE idEmpresa = ?
//     AND CaixaGeral = 0
//     AND DATE(Data) BETWEEN ? AND ?
// ", null, [$idEmpresa, $dataInicial, $dataFinal]);

$resumo = ExSqlNET("
    SELECT 
        SUM(CASE WHEN TipoMov = 'ENTRADA' THEN Valor ELSE 0 END) entradas,
        SUM(CASE WHEN TipoMov = 'SAIDA' THEN Valor ELSE 0 END) saidas
    FROM movimentocc
    WHERE idEmpresa = ?
    AND CaixaGeral = 0
    AND DATE(Data) BETWEEN ? AND ?
", null, [$idEmpresa, $dataInicial, $dataFinal]);

$entradas = $resumo[0]['entradas'] ?? 0;
$saidas   = $resumo[0]['saidas'] ?? 0;

$saldo = $entradas - $saidas;


// ================= MOVIMENTOS =================

// $lista = ExSqlNET("
//     SELECT Data, Descricao, Valor, TipoMov
//     FROM movimentocc
//     WHERE idEmpresa = ?
//     AND CaixaGeral = 0
//     AND DATE(Data) = ?
//     ORDER BY Data DESC
// ", null, [$idEmpresa, $dataHoje]);
// $lista = ExSqlNET("
//     SELECT Data, Descricao, Valor, TipoMov
//     FROM movimentocc
//     WHERE idEmpresa = ?
//     AND CaixaGeral = 0
//     AND DATE(Data) BETWEEN ? AND ?
//     ORDER BY Data DESC
// ", null, [$idEmpresa, $dataInicial, $dataFinal]);

// $lista = ExSqlNET("
//     SELECT DataPgto As Data, Descricao, Valor, TipoMov, Controle
//     FROM movimentocc
//     WHERE idEmpresa = ?
//     AND CaixaGeral = 0
//     AND DATE(DataPgto) BETWEEN ? AND ?
//     ORDER BY DataPgto DESC
// ", null, [$idEmpresa, $dataInicial, $dataFinal]);

$lista = ExSqlNET("
    SELECT Data As Data, Descricao, Valor, TipoMov, Controle, ControleOrigem As Pedido
    FROM movimentocc

    WHERE idEmpresa = ?
    AND CaixaGeral = 0
    AND DATE(Data) BETWEEN ? AND ?
    ORDER BY Data DESC
", null, [$idEmpresa, $dataInicial, $dataFinal]);


if(isset($_POST['excluir']) && !empty($_POST['lancamentos'])){

    $ids = array_map('intval', $_POST['lancamentos']);
    
    $dados['idEmpresa'] = $idEmpresa;
    $dados['Controle'] = $ids;
    
    MovimentoCC($dados, "EXCLUIRCONTROLE");

    header("Location: ".$_SERVER['REQUEST_URI']);
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Caixa Diário</title>
    <link rel="stylesheet" href="../css/base.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">
<style>

    .resumo {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .resumo-box {
        background: #fff7ed;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .resumo-box h4 {
        margin: 0;
        font-size: 14px;
        color: #9a3412;
    }

    .resumo-box span {
        display: block;
        margin-top: 6px;
        font-size: 22px;
        font-weight: 700;
    }

    .valor-entrada {
        color: #16a34a;
    }

    .valor-saida {
        color: #dc2626;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th, table td {
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
        text-align: left;
    }

    table th {
        background-color: #fff7ed;
        color: #9a3412;
    }

    .btn-lancamento {
        background: #28a745;
        color: #fff;
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
    }
    .btn-lancamento:hover {
        background: #218838;
    }

</style>

</head>

<body>

    <?php include '../base/navbarUser.php'; ?>

    <div class="content">

        <h2 style="display:flex; justify-content:space-between; align-items:center;">
            Caixa do Dia

            <a href="frmBoletimCaixaLcto.php" class="btn-lancamento">
                + Lançamento
            </a>
        </h2>

        <div class="card" style="margin-bottom:20px;">

            <form method="GET" style="display:flex; gap:15px; align-items:end; flex-wrap:wrap;">

                <div>
                    <label>Data Inicial</label><br>
                    <input type="date" name="dataInicial" value="<?= $dataInicial ?>">
                </div>

                <div>
                    <label>Data Final</label><br>
                    <input type="date" name="dataFinal" value="<?= $dataFinal ?>">
                </div>

                <div>
                    <button type="submit" class="btn">
                        Filtrar
                    </button>
                    <a href="pdfBoletimCaixa.php?dataInicial=<?= $dataInicial ?>&dataFinal=<?= $dataFinal ?>" class="btn btn-secondary" target="_blank">
                        Imprimir
                    </a>
                </div>

            </form>

        </div>

        <div class="card resumo">

            <div class="resumo-box">
                <h4>Entradas</h4>
                <span class="valor-entrada">
                    R$ <?= number_format($entradas,2,',','.') ?>
                </span>
            </div>

            <div class="resumo-box">
                <h4>Saídas</h4>
                <span class="valor-saida">
                    R$ <?= number_format($saidas,2,',','.') ?>
                </span>
            </div>

            <div class="resumo-box">
                <h4>Saldo</h4>
                <span>
                    R$ <?= number_format($saldo,2,',','.') ?>
                </span>
            </div>
        </div>

        <form method="POST">
            <div class="card">

            <h3>Movimentos do Dia</h3>

            <div style="margin-bottom:15px;">
                <button type="submit" name="excluir" class="btn btn-danger"
                    onclick="return confirm('Excluir lançamentos selecionados?')">
                    🗑 Excluir selecionados
                </button>
            </div>

            <table>

                <tr>
                     <th>
                        <input type="checkbox" onclick="marcarTodos(this)">
                    </th>
    
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Pedido</th>
                    <th>Valor</th>
                </tr>

                <?php foreach($lista as $l): 

                    $cor = '#000';
                    $sinal = '';

                    if ($l['TipoMov'] == 'ENTRADA') {
                        $cor = '#16a34a';
                        $sinal = '+ ';
                    } elseif ($l['TipoMov'] == 'SAIDA') {
                        $cor = '#dc2626';
                        $sinal = '- ';
                    }

                ?>

                <tr>

                    <td>
                        <input type="checkbox" name="lancamentos[]" value="<?= $l['Controle'] ?>">
                    </td>

                    <!--<td><?= date('H:i', strtotime($l['Data'])) ?></td>-->
                        
                    <td><?= date('d/m/Y H:i', strtotime($l['Data'])) ?></td>
                    
                    <td style="color:<?= $cor ?>; font-weight:bold">
                        <?= $l['TipoMov'] ?>
                    </td>

                    <td><?= htmlspecialchars($l['Descricao']) ?></td>
                    
                    <td><?= htmlspecialchars($l['Pedido']) ?></td>
                     
                    <td style="color:<?= $cor ?>; font-weight:bold">
                        <?= $sinal ?>R$ <?= number_format($l['Valor'],2,',','.') ?>
                    </td>

                </tr>

                <?php endforeach; ?>


            </table>

            </div>
        </form>


    </div>

</body>

</html>

<script>

function marcarTodos(source){

    let checkboxes = document.querySelectorAll('input[name="lancamentos[]"]');

    checkboxes.forEach(c => {
        c.checked = source.checked;
    });

}

</script>
