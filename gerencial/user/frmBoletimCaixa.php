<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Login");
    exit;
}
date_default_timezone_set('America/Sao_Paulo');

$idEmpresa = $_SESSION['idEmpresa'];
$dataHoje = date('Y-m-d');

$dataInicial = $_GET['dataInicial'] ?? date('Y-m-d');
$dataFinal   = $_GET['dataFinal'] ?? date('Y-m-d');

// ================= CAIXA HOJE =================

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
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
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


    .btn-lancamento{
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        background: linear-gradient(135deg, #fb923c, #ea580c);
        color: #fff;
        padding: 18px 34px;
        border-radius: 18px;
        text-decoration: none;
        font-size: 18px;
        font-weight: 800;
        letter-spacing: .4px;
        position: relative;
        box-shadow:
            0 18px 40px rgba(249,115,22,.35),
            inset 0 1px 0 rgba(255,255,255,.25);
        transition:
            transform .18s ease,
            box-shadow .18s ease,
            filter .18s ease;
        animation: pulseLancamento 2s infinite;
    }

    .btn-lancamento::before{
        content: "＋";
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
    }

    .btn-lancamento:hover{
        transform: translateY(-4px) scale(1.02);
        filter: brightness(1.05);
        box-shadow:
            0 24px 50px rgba(249,115,22,.45),
            inset 0 1px 0 rgba(255,255,255,.3);
    }

    .btn-lancamento:active{
        transform: scale(.98);
    }

    @keyframes pulseLancamento{

        0%{
            box-shadow:
                0 18px 40px rgba(249,115,22,.35),
                0 0 0 0 rgba(249,115,22,.35);
        }

        70%{
            box-shadow:
                0 18px 40px rgba(249,115,22,.35),
                0 0 0 14px rgba(249,115,22,0);
        }

        100%{
            box-shadow:
                0 18px 40px rgba(249,115,22,.35),
                0 0 0 0 rgba(249,115,22,0);
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
                    Caixa Diário
                </h1>
                <div class="page-subtitle">
                    Controle completo das entradas, saídas e saldo do caixa
                </div>
            </div>
            <a href="BoletimCaixa/Lancamento" class="btn-lancamento">
                Novo Lançamento
            </a>
        </div>

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
                    <a href="BoletimCaixa/Imprimir?dataInicial=<?= $dataInicial ?>&dataFinal=<?= $dataFinal ?>" class="btn btn-secondary" target="_blank">
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
