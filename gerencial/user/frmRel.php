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

$idEmpresa = $_SESSION['idEmpresa'];

$dataInicial   = $_GET['dataInicial'] ?? date('Y-m-01');
$dataFinal     = $_GET['dataFinal'] ?? date('Y-m-d');
$tipoRelatorio = $_GET['tipo'] ?? 'faturamento';

// $mesSelecionado = $_GET['mes'] ?? date('m');
$mesSelecionado = (int) ($_GET['mes'] ?? date('m'));

$anoSelecionado = date('Y'); 

$totalFaturamento = 0;
$totalPedidos = 0;

//======SOMENTE PARA RELATÓRIO DE LUCRO======
$produtoFiltro = $_GET['produto'] ?? '';
$produtoId   = $_GET['produtoId'] ?? 0;
$produtoNome = $_GET['produtoNome'] ?? '';
$grupoForcli = "";

// ================= FILTROS =================

// PRODUTOS
$grupoId = (int)($_GET['grupoId'] ?? 0);
$nomeGrupoProduto = $_GET['grupo_nome'] ?? '';

// CLIENTES
$grupoForcliId = (int)($_GET['grupoIdForcli'] ?? 0);
$nomeGrupoForcli = $_GET['grupo_nome_forcli'] ?? '';

$produtos = ExSqlNET("
    SELECT id, Nome
    FROM servprod
    WHERE idEmpresa = ?
    ORDER BY Nome
", null, [$idEmpresa]);


//====== FILTRO PRODUTO / GRUPO (PADRÃO GLOBAL) ======
$whereProd = "";
$paramsProd = [];

if ($produtoId > 0) {
    $whereProd = " AND sp.id = ? ";
    $paramsProd[] = $produtoId;

} elseif ($grupoId > 0) {
    $whereProd = " AND sp.Grupo = ? ";
    $paramsProd[] = $grupoId;
}

// ================= FATURAMENTO =================

$paramsFat = array_merge([$idEmpresa, $dataInicial, $dataFinal], $paramsProd);

$faturamento = ExSqlNET("
    SELECT SUM(mi.TotalItem) total
    FROM movimento m
    LEFT JOIN movimentoitem mi 
        ON mi.ControleMovimento = m.id
    LEFT JOIN servprod sp
        ON sp.id = mi.ServProd
    WHERE m.idEmpresa = ?
    AND DATE(m.Data) BETWEEN ? AND ?
    $whereProd
    ORDER BY sp.Nome ASC
", null, $paramsFat);

$totalFaturamento = $faturamento[0]['total'] ?? 0;

// ================= PEDIDOS =================

$paramsPed = array_merge([$idEmpresa, $dataInicial, $dataFinal], $paramsProd);

$pedidos = ExSqlNET("
    SELECT COUNT(DISTINCT m.id) total
    FROM movimento m
    LEFT JOIN movimentoitem mi 
        ON mi.ControleMovimento = m.id
    LEFT JOIN servprod sp
        ON sp.id = mi.ServProd
    WHERE m.idEmpresa = ?
    AND DATE(m.Data) BETWEEN ? AND ?
    $whereProd
", null, $paramsPed);

 $totalPedidos = $pedidos[0]['total'] ?? 0;

// ================= TICKET MÉDIO TOTAL =================

$ticketMedio = $totalPedidos > 0 
    ? $totalFaturamento / $totalPedidos 
    : 0;


// ================= GRÁFICO FATURAMENTO =================

$paramsGraf = array_merge([$idEmpresa, $dataInicial, $dataFinal], $paramsProd);

$graficoFat = ExSqlNET("
    SELECT DATE(m.Data) dia, SUM(mi.TotalItem) total
    FROM movimento m
    LEFT JOIN movimentoitem mi 
        ON mi.ControleMovimento = m.id
    LEFT JOIN servprod sp
        ON sp.id = mi.ServProd
    WHERE m.idEmpresa = ?
    AND DATE(m.Data) BETWEEN ? AND ?
    $whereProd
    GROUP BY DATE(m.Data)
", null, $paramsGraf);


$dias = [];
$valoresFat = [];

foreach ($graficoFat as $g) {
    $dias[] = date('d/m', strtotime($g['dia']));
    $valoresFat[] = (float)$g['total'];
}


// ================= METAS =================
$paramsMetas = array_merge(
    [$idEmpresa, $idEmpresa, $mesSelecionado, $anoSelecionado, $idEmpresa],
    $paramsProd
);

$metas = ExSqlNET("
    SELECT 
        sp.id, 
        sp.Nome, 
        sp.MetaMensal,
        IFNULL(SUM(mi.Qtd),0) vendido,
        IFNULL(SUM(mi.TotalItem),0) Faturamento

    FROM servprod sp

    LEFT JOIN movimentoitem mi 
        ON mi.ServProd = sp.id
        AND mi.idEmpresa = ?

    LEFT JOIN movimento m 
        ON m.id = mi.ControleMovimento

    WHERE 
        sp.idEmpresa = ?
        AND m.idEmpresa = ?
        AND MONTH(m.Data) = ?
        AND YEAR(m.Data) = ?
        $whereProd

    GROUP BY sp.id
    ORDER BY sp.Nome ASC
", null, array_merge(
    [$idEmpresa, $idEmpresa, $idEmpresa, $mesSelecionado, $anoSelecionado],
    $paramsProd
));

// ================= LUCRO =================

$paramsLucro = array_merge([$idEmpresa, $idEmpresa, $dataInicial, $dataFinal], $paramsProd);

$lucroProdutos = ExSqlNET("
    SELECT 
        COALESCE(sp.Nome, 'Produto não cadastrado') AS Nome,

        SUM(mi.Qtd) AS quantidade,

        SUM(mi.TotalItem) AS faturamento,

        SUM(mi.Qtd * mi.ValorCusto) AS custoTotal,

        SUM(mi.TotalItem - (mi.Qtd * mi.ValorCusto)) AS lucro

    FROM movimentoitem mi

    INNER JOIN movimento m 
        ON m.id = mi.ControleMovimento

    LEFT JOIN servprod sp 
        ON sp.id = mi.ServProd

    WHERE m.idEmpresa = ?
      AND mi.idEmpresa = ?
      AND DATE(m.Data) BETWEEN ? AND ?
      AND m.Status NOT IN (3,4)

    $whereProd

    GROUP BY mi.ServProd, sp.Nome
    ORDER BY nome ASC
", null, array_merge([
    $idEmpresa,
    $idEmpresa,
    $dataInicial,
    $dataFinal
], $paramsProd));

$nomeGrupo = '';

$grupos = ExSqlNET("
    SELECT Id, Nome 
    FROM grupos 
    WHERE idEmpresa = ? 
    AND Tipo = 'P'
    ORDER BY Nome
", null, [$_SESSION['idEmpresa']]);
    
if ($grupoId > 0) {
    $grupoSelecionado = ExSqlNET("
        SELECT Nome 
        FROM grupos 
        WHERE Id = ? AND idEmpresa = ?
    ", null, [$grupoId, $idEmpresa]);

    if ($grupoSelecionado) {
        $nomeGrupo = $grupoSelecionado[0]['Nome'];
    }
}

$gruposForcli = ExSqlNET("
    SELECT Id, Nome 
    FROM grupos 
    WHERE idEmpresa = ? 
    AND Tipo = 'C'
    ORDER BY Nome
", null, [$_SESSION['idEmpresa']]);

if ($grupoForcli > 0) {
    $grupoSelecionado = ExSqlNET("
        SELECT Nome 
        FROM grupos 
        WHERE Id = ? AND idEmpresa = ?
    ", null, [$grupoForcli, $idEmpresa]);

    if ($grupoSelecionado) {
        $nomeGrupo = $grupoSelecionado[0]['Nome'];
    }
}


// ================= DESPESAS =================

$whereDesp = "";
$paramsDesp = [];

$paramsDespesas = [
    $idEmpresa,
    $dataInicial,
    $dataFinal,
    $idEmpresa
];

$despesas = ExSqlNET("
    SELECT 
        td.id,
        td.Nome,
        td.Descricao,

        IFNULL(SUM(
            CASE 
                WHEN mc.TipoMov = 'SAIDA' THEN mc.Valor 
                ELSE 0 
            END
        ),0) AS totalGasto,

        COUNT(
            CASE 
                WHEN mc.TipoMov = 'SAIDA' THEN mc.Controle
                ELSE NULL 
            END
        ) AS qtdLancamentos

    FROM tipodespesa td

    LEFT JOIN movimentocc mc 
        ON mc.tipoDespesa = td.id
        AND mc.idEmpresa = ?
        AND DATE(mc.Data) BETWEEN ? AND ?

    WHERE 
        td.idEmpresa = ?
        AND td.Inativo = 0

    GROUP BY td.id
    ORDER BY td.Nome ASC
", null, $paramsDespesas);


$totalDespesas = 0;

foreach ($despesas as $d) {
    $totalDespesas += $d['totalGasto'] ?? 0;
}

// ================= SERVIÇOS -> FORCLI =================

$whereServicosForcli = "";
$paramsServicosForcli = [
    $idEmpresa,
    $dataInicial,
    $dataFinal,
];

if ($grupoForcliId > 0) {
    $whereServicosForcli .= " AND f.Grupo = ? ";
    $paramsServicosForcli[] = $grupoForcliId;
}

$servicosForcli = ExSqlNET("
    SELECT 
        f.Nome,
        SUM(
            (
                SELECT COALESCE(SUM(mi2.Qtd), 0)
                FROM movimentoitem mi2
                WHERE mi2.ControleMovimento = m.id
                  AND mi2.idEmpresa = m.idEmpresa
            )
        ) AS TotalServicos,

        SUM(
            COALESCE(
                (
                    SELECT SUM(mi.TotalItem)
                    FROM movimentoitem mi
                    WHERE mi.ControleMovimento = m.id
                ),
                0
            )
        ) AS Faturamento,

        SUM(
            (
                SELECT COALESCE(SUM(mc2.Valor), 0)
                FROM movimentocc mc2
                WHERE mc2.ControleOrigem = m.id
                  AND mc2.idEmpresa = m.idEmpresa
            )
        ) AS Lucro
    FROM movimento m
    LEFT JOIN forcli f 
        ON f.id = m.Forcli
    WHERE m.idEmpresa = ?
      AND DATE(m.Data) BETWEEN ? AND ?
      $whereServicosForcli

    GROUP BY f.Nome
    ORDER BY TotalServicos DESC
", null, $paramsServicosForcli);

$totalServicosForcli = 0;

foreach ($servicosForcli as $sf) {
    $totalServicosForcli += $sf['Faturamento'] ?? 0;
}

$totalLucroForcli = 0;

foreach ($servicosForcli as $item) {
    $totalLucroForcli += (float)($item['Lucro'] ?? 0);
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Relatórios</title>

    <link rel="stylesheet" href="../css/base.css">
    <link rel="icon" href="../img/favicon.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
<style>
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .card-box {
            background: #fff7ed;
            border-radius: 12px;
            padding: 20px;
        }

        .card-box h4 {
            margin: 0;
            font-size: 14px;
            color: #9a3412;
        }

        .card-box span {
            font-size: 24px;
            font-weight: bold;
        }

        .form-grid > div:last-child {
            grid-column: 1 / -1; /* ocupa largura toda */
        }

        .form-grid > div:last-child label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 500;
        }

        .form-grid > div:last-child input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
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
        
        .modal-table-wrapper thead th{
            position: sticky;
            top: 0;
            background: #fff7ed;
            z-index: 2;
        }
        
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
    

    </style>

</head>

<body>

    <?php include '../base/navbarUser.php'; ?>

    <div class="content">

        <div class="page-title">Relatórios e Dashboards</div>

        <div class="card">
            <form method="get" class="form-grid" id="formRelatorio">

                <div id="datasContainer">
                    <div>
                        <label>Data Inicial</label>
                        <input type="date" name="dataInicial" value="<?= $dataInicial ?>">
                    </div>

                    <div>
                        <label>Data Final</label>
                        <input type="date" name="dataFinal" value="<?= $dataFinal ?>">
                    </div>
                </div>

                <div id="mesContainer" style="display:none;">
                    <label>Mês</label>
                    <select name="mes">
                        <?php 
                            $meses = [
                                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',
                                4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                                7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro',
                                10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                            ];

                        for ($i = 1; $i <= 12; $i++) {
                        
                            $selected = ($i == $mesSelecionado) ? 'selected' : '';
                        
                            echo "<option value='$i' $selected>".$meses[$i]."</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label>Tipo Relatório</label>
                    <select name="tipo" id="tipoRelatorio">
                        <option value="faturamento" <?= $tipoRelatorio=='faturamento'?'selected':'' ?>>Faturamento</option>
                        <option value="metas" <?= $tipoRelatorio=='metas'?'selected':'' ?>>Metas</option>
                        <option value="lucro" <?= $tipoRelatorio=='lucro'?'selected':'' ?>>Lucro</option>
                        <option value="despesas" <?= $tipoRelatorio=='despesas'?'selected':'' ?>>Despesas</option>
                         <option value="servicosForcli" <?= $tipoRelatorio=='servicosForcli'?'selected':'' ?>>Serviços por Cliente</option>
                    </select>
                </div>

                <div id="produtoContainer" style="display:none;">
                
                    <label>Produto</label>
                    
                    <div style="display:flex; gap:6px;">
                    
                        <input type="hidden" name="produtoId" id="produtoId" value="<?= $produtoId ?>">
                        
                        <input 
                            type="text"
                            id="produtoNome"
                            name="produtoNome"
                            value="<?= htmlspecialchars($produtoNome) ?>"
                            placeholder="Digite o produto..."
                            readonly
                            onclick="abrirModal('produto')"
                        >
                    
                    </div>
                
                </div>
                
                <div id="grupoContainer">
                    
                    <label for="grupo_nome">Grupo</label>
                
                    <input type="hidden" name="grupoId" id="grupoId" value="<?= $grupoId ?>">
                                    
                    <!-- // ================= INPUT GRUPO PRODUTO ================= -->
                    <input 
                        type="text"
                        id="grupo_nome"
                        name="grupo_nome"
                        value="<?= htmlspecialchars($nomeGrupoProduto ?? '') ?>"
                        placeholder="Clique para buscar..."
                        onclick="abrirModal('grupo')"
                        readonly
                        style="cursor: pointer;"
                    >
                </div>

                <div id="grupoForcliContainer">
                    
                    <label for="grupo_nome_forcli">Grupo Clientes</label>
                
                   <!-- // ================= HIDDEN CLIENTE ================= -->
                    <input type="hidden" name="grupoIdForcli" id="grupoIdForcli" value="<?= $grupoForcliId ?>">
                    
                    <!-- // ================= INPUT GRUPO CLIENTE ================= -->
                    <input 
                        type="text"
                        id="grupo_nome_forcli"
                        name="grupo_nome_forcli"
                        value="<?= htmlspecialchars($nomeGrupoForcli ?? '') ?>"
                        placeholder="Clique para buscar..."
                        onclick="abrirModal('grupoForcli')"
                        readonly
                        style="cursor: pointer;"
                    >
                </div>
                
                <input type="hidden" name="graficoBase64" id="graficoBase64">

                <div style="display:flex;align-items:flex-end;gap:10px;">
                    <button class="btn">Filtrar</button>       
                    <button type="button" class="btn btn-secondary" id="btnPdf">
                        Imprimir PDF
                    </button>
                </div>

                <div>
                    <label>
                        <input type="checkbox" id="mostrarValores" name="mostrarValores" value="1"
                        <?= isset($_GET['mostrarValores']) ? 'checked' : '' ?>>
                        Imprimir com valores financeiros (Faturamento, Custo e Lucro)
                    </label>
                </div>

            </form>
        </div>

        <?php if ($tipoRelatorio == 'faturamento'): ?>

        <div class="cards">

            <div class="card-box">
                <h4>Faturamento</h4>
                <span style="color:#16a34a">
                    R$ <?= number_format($totalFaturamento,2,',','.') ?>
                </span>
            </div>

            <div class="card-box">
                <h4>Pedidos</h4>
                <span><?= $totalPedidos ?></span>
            </div>

            <div class="card-box">
                <h4>Ticket Médio</h4>
                <span style="color:#2563eb">
                    R$ <?= number_format($ticketMedio,2,',','.') ?>
                </span>
            </div>

        </div>


        <div class="card">

            <h3>Faturamento por Dia</h3>

            <canvas id="grafFaturamento"></canvas>

        </div>

        <?php endif; ?>



        <?php if ($tipoRelatorio == 'metas'): ?>

        <div class="card">

            <h3>Metas Mensais</h3>

            <table>

                <tr>
                    <th>Produto</th>
                    <th>Vendido</th>
                    <th>Meta</th>
                    <th>Falta</th>
                    <!--<th>Faturamento</th>-->
                    <th>% da Meta</th>
                </tr>

                <?php foreach($metas as $m):

                $meta = $m['MetaMensal'] ?? 0;
                $vendido = $m['vendido'] ?? 0;
                $faturamento = $m['Faturamento'] ?? 0;
                $perc = $meta > 0 ? ($vendido / $meta) * 100 : 0;
                ?>

                <tr>
                    <td><?= $m['Nome'] ?></td>
                    <td><?= number_format($vendido,0,',','.') ?></td>
                    <td><?= number_format($meta,0,',','.') ?></td>
                    <td><?= number_format(max(0, $meta - $vendido), 0, ',', '.') ?></td>
                    <!--<td style="color:<?= $faturamento >= 0 ? '#16a34a' : '#dc2626' ?>">-->
                        <!--R$ <?= number_format($faturamento,2,',','.') ?>-->
                    <!--</td>-->

                    <td style="color:<?= $perc >= 100 ? '#16a34a' : '#dc2626' ?>">
                        <?= number_format($perc,1,',','.') ?>%
                    </td>
                </tr>

                <?php endforeach; ?>

            </table>

        </div>

        <?php endif; ?>



        <?php if ($tipoRelatorio == 'lucro'): ?>

        <div class="card">

            <h3>Lucro por Produto</h3>

            <table>

                <tr>
                    <th>Produto</th>
                    <th>Qtd</th>
                    <th>Faturamento</th>
                    <th>Custo</th>
                    <th>Lucro</th>
                    <th>% Margem</th>
                    <th>Ticket Médio</th>
                </tr>

                <?php

                $totalLucro = 0;
                $totalFat = 0;
                $totalCusto = 0;

                foreach ($lucroProdutos as $l):

                $fat = $l['faturamento'] ?? 0;
                $custo = $l['custoTotal'] ?? 0;
                $lucro = $l['lucro'] ?? 0;
                $qtd = $l['quantidade'] ?? 0;

                $margem = $fat > 0 ? ($lucro / $fat) * 100 : 0;

                $ticketProduto = $qtd > 0 ? $fat / $qtd : 0;

                $totalLucro += $lucro;
                $totalFat += $fat;
                $totalCusto += $custo;

                ?>

                <tr>

                    <td><?= $l['Nome'] ?></td>
                    <td><?= $qtd ?></td>

                    <td>R$ <?= number_format($fat,2,',','.') ?></td>

                    <td>R$ <?= number_format($custo,2,',','.') ?></td>

                    <td style="color:<?= $lucro >= 0 ? '#16a34a' : '#dc2626' ?>">
                        R$ <?= number_format($lucro,2,',','.') ?>
                    </td>

                    <td><?= number_format($margem,1,',','.') ?>%</td>

                    <td>
                        R$ <?= number_format($ticketProduto,2,',','.') ?>
                    </td>

                </tr>

                <?php endforeach; ?>

            </table>

        </div>


        <div class="cards">

            <div class="card-box">
                <h4>Faturamento Total</h4>
                <span>R$ <?= number_format($totalFat,2,',','.') ?></span>
            </div>

            <div class="card-box">
                <h4>Custo Total</h4>
                <span style="color:#dc2626">
                    R$ <?= number_format($totalCusto,2,',','.') ?>
                </span>
            </div>

            <div class="card-box">
                <h4>Lucro Total</h4>
                <span style="color:#16a34a">
                    R$ <?= number_format($totalLucro,2,',','.') ?>
                </span>
            </div>

        </div>

        <?php endif; ?>


        
        <?php if ($tipoRelatorio == 'despesas'): ?>
        
        <div class="card">
        
            <h3>Despesas por Categoria</h3>
        
            <table>
        
                <tr>
                    <th>Categoria</th>
                    <th>Lançamentos</th>
                    <th>Total Gasto</th>
                    <th>Média</th>
                    <th>% do Total</th>
                </tr>
        
                <?php foreach($despesas as $d):
        
                    $total = $d['totalGasto'] ?? 0;
                    $qtd = $d['qtdLancamentos'] ?? 0;
        
                    $media = $qtd > 0 ? $total / $qtd : 0;
        
                    $perc = $totalDespesas > 0 
                        ? ($total / $totalDespesas) * 100 
                        : 0;
                ?>
        
                <tr>
        
                    <td><?= $d['Nome'] ?></td>
        
                    <td><?= number_format($qtd,0,',','.') ?></td>
        
                    <td style="color:#dc2626">
                        R$ <?= number_format($total,2,',','.') ?>
                    </td>
        
                    <td>
                        R$ <?= number_format($media,2,',','.') ?>
                    </td>
        
                    <td style="color:<?= $perc > 30 ? '#dc2626' : '#16a34a' ?>">
                        <?= number_format($perc,1,',','.') ?>%
                    </td>
        
                </tr>
        
                <?php endforeach; ?>
        
            </table>
        
        </div>
        
        
        <div class="cards">
        
            <div class="card-box">
                <h4>Total de Despesas</h4>
                <span style="color:#dc2626">
                    R$ <?= number_format($totalDespesas,2,',','.') ?>
                </span>
            </div>
        
        </div>
        
        <?php endif; ?>



        <?php if ($tipoRelatorio == 'servicosForcli'): ?>
        
        <div class="card">
        
            <h3>Serviços por Cliente</h3>
        
            <table>
        
                <tr>
                    <th>Cliente</th>
                    <th>Total Serviços / Produtos</th>
                    <th>Faturamento</th>
                    <th>Lucro</th>
                </tr>
        
                <?php foreach($servicosForcli as $sf):?>
        
                <tr>
        
                    <td><?= $sf['Nome'] ?></td>
                    <td><?= number_format($sf['TotalServicos'],0,',','.') ?></td>
        
                    <td style="color:#dc2626">
                        R$ <?= number_format($sf['Faturamento'],2,',','.') ?>
                    </td>

                    <td style="color:#dc2626">
                        R$ <?= number_format($sf['Lucro'],2,',','.') ?>
                    </td>
        
                </tr>
        
                <?php endforeach; ?>
        
            </table>
        
        </div>
        
        
        <div class="cards">
            <div class="card-box">
                <h4>Total de Faturamento</h4>
                <span style="color:#dc2626">
                    R$ <?= number_format($totalServicosForcli,2,',','.') ?>
                </span>
            </div>
            <div class="card-box">
                <h4>Total de Lucro</h4>
                <span style="color:#16a34a">
                    R$ <?= number_format($totalLucroForcli, 2, ',', '.') ?>
                </span>
            </div>
        </div>
        
        <?php endif; ?>




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
        
    </div>
    
<script>
    const dias = <?= json_encode($dias) ?>;
    const valoresFat = <?= json_encode($valoresFat) ?>;
    let graficoFaturamento = null;
    
    
    let grupos = <?= json_encode($grupos ?? []) ?>;
    let produtos = <?= json_encode($produtos ?? []) ?>;
    let gruposForcli = <?= json_encode($gruposForcli ?? []) ?>;     
     
    let tipoModal = '';
    
  
function carregarModal(filtro = '') {

    let head = document.getElementById('modalHead');
    let body = document.getElementById('modalBody');

    head.innerHTML = '';
    body.innerHTML = '';

    filtro = (filtro || '').toLowerCase();

    let html = '';

    if (tipoModal === 'grupo') {

        document.getElementById('modalTitulo').innerText = 'Selecionar Grupo';

        head.innerHTML = '<th>Nome</th>';

        (grupos || [])
            .filter(g => (g.Nome || '').toLowerCase().includes(filtro))
            .forEach(g => {

                let nomeSeguro = (g.Nome || '').replace(/'/g, "\\'");

                html += `
                    <tr onclick="selecionarGrupo(${g.Id}, '${nomeSeguro}')">
                        <td>${g.Nome || ''}</td>
                    </tr>`;
            });
    }


    if (tipoModal === 'produto') {

        document.getElementById('modalTitulo').innerText = 'Selecionar Produto';

        head.innerHTML = '<th>Nome</th>';

        (produtos || [])
            .filter(p => (p.Nome || '').toLowerCase().includes(filtro))
            .forEach(p => {

                let nomeSeguro = (p.Nome || '').replace(/'/g, "\\'");

                html += `
                    <tr onclick="selecionarProduto(${p.id}, '${nomeSeguro}')">
                        <td>${p.Nome || ''}</td>
                    </tr>`;
            });
    }


    if (tipoModal === 'grupoForcli') {

        document.getElementById('modalTitulo').innerText = 'Selecionar Grupo';

        head.innerHTML = '<th>Nome</th>';

        (gruposForcli || [])
            .filter(g => (g.Nome || '').toLowerCase().includes(filtro))
            .forEach(g => {

                let nomeSeguro = (g.Nome || '').replace(/'/g, "\\'");

                html += `
                    <tr onclick="selecionarGrupoForcli(${g.Id}, '${nomeSeguro}')">
                        <td>${g.Nome || ''}</td>
                    </tr>`;
            });
    }
    
    
    body.innerHTML = html;
}

  
document.getElementById('modalBusca').addEventListener('input', function() {
    carregarModal(this.value.toLowerCase());
});

function limparGrupo() {
    document.getElementById('grupoId').value = 0;
    document.getElementById('grupo_nome').value = '';
}

function limparGrupoForcli() {
    document.getElementById('grupoIdForcli').value = 0;
    document.getElementById('grupo_nome_forcli').value = '';
}

function limparProduto() {
    document.getElementById('produtoId').value = '';
    document.getElementById('produtoNome').value = '';
}

// FECHAMENTO MODAL
document.getElementById('modalBg').addEventListener('click', function(e) {
    if (e.target === this) {

        if (tipoModal === 'grupo') limparGrupo();
        if (tipoModal === 'produto') limparProduto();
        if (tipoModal === 'grupoForcli') limparGrupoForcli();

        fecharModal();
    }
});


function selecionarGrupo(id, nome) {
    document.getElementById('grupoId').value = id;
    document.getElementById('grupo_nome').value = nome;
    fecharModal();
}

function selecionarGrupoForcli(id, nome) {
    document.getElementById('grupoIdForcli').value = id;
    document.getElementById('grupo_nome_forcli').value = nome;
    fecharModal();
}


 function abrirModal(tipo) {

    tipoModal = tipo;

    document.getElementById('modalBg').style.display = 'flex';

    let input = document.getElementById('modalBusca');

    input.value = '';

    carregarModal('');

    setTimeout(() => {
        input.focus();
    }, 50);

}
 
function fecharModal() {
    document.getElementById('modalBg').style.display = 'none';
}



    <?php if ($tipoRelatorio == 'faturamento'): ?>
    if (document.getElementById('grafFaturamento')) {

        if (document.getElementById('grafFaturamento')) {

            graficoFaturamento = new Chart(
                document.getElementById('grafFaturamento'),
                {
                    type: 'line',
                    data: {
                        labels: dias,
                        datasets: [{
                            label: 'Faturamento',
                            data: valoresFat,
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        animation: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'R$ ' + value.toLocaleString('pt-BR');
                                    }
                                }
                            }
                        }
                    }
                }
            );

        }

    }
    <?php endif; ?>


 function abrirModal(tipo) {

    tipoModal = tipo;

    document.getElementById('modalBg').style.display = 'flex';

    let input = document.getElementById('modalBusca');

    input.value = '';

    carregarModal('');

    setTimeout(() => {
        input.focus();
    }, 50);

}
 
function fecharModal() {
    document.getElementById('modalBg').style.display = 'none';
}

document.getElementById('btnPdf').addEventListener('click', function(e) {

    e.preventDefault();

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'pdfRelatorio.php';
    form.target = '_blank';

    function addCampo(nome, valor) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nome;
        input.value = valor;
        form.appendChild(input);
    }

    const grupoId = document.getElementById('grupoId').value;
    addCampo('grupoId', grupoId);
        
    addCampo('dataInicial', document.querySelector('input[name="dataInicial"]').value);
    addCampo('dataFinal', document.querySelector('input[name="dataFinal"]').value);
    addCampo('tipo', document.querySelector('select[name="tipo"]').value);
    addCampo('mostrarValores', document.getElementById('mostrarValores').checked ? 1 : 0);

    if (tipoSelect.value === 'metas') {
        const mesSelecionado = document.querySelector('select[name="mes"]').value;
        addCampo('mes', mesSelecionado);
    }

    if (tipoSelect.value === 'metas' || tipoSelect.value === 'lucro') {

        const produtoId = document.getElementById('produtoId').value;
        const produtoNome = document.getElementById('produtoNome').value;

        addCampo('produtoId', produtoId);
        addCampo('produtoNome', produtoNome);

    }
    
    if (tipoSelect.value === 'servicosForcli') {

        const grupoIdForcli = document.getElementById('grupoIdForcli').value;
        addCampo('grupoIdForcli', grupoIdForcli);
        
    }
    
    if (tipoSelect.value === 'faturamento' && graficoFaturamento) {

        setTimeout(() => {

            const base64 = graficoFaturamento.toBase64Image();
            addCampo('graficoBase64', base64);

            console.log('BASE64 GERADO:', base64);

            document.body.appendChild(form);
            form.submit();

        }, 300);

    } else {

        document.body.appendChild(form);
        form.submit();
    }

});

    const tipoSelect = document.getElementById('tipoRelatorio');
    const datasContainer = document.getElementById('datasContainer');
    const mesContainer = document.getElementById('mesContainer');

    const produtoContainer = document.getElementById('produtoContainer');
    const grupoContainer = document.getElementById('grupoContainer');
    const grupoForcliContainer = document.getElementById('grupoForcliContainer');
    
    // ================= ATUALIZAÇÃO DE CAMPOS =================
    function atualizarCampos() {

        // Reset geral
        produtoContainer.style.display = 'none';
        grupoContainer.style.display = 'none';
        grupoForcliContainer.style.display = 'none';

        produtoContainer.style.pointerEvents = 'none';
        grupoContainer.style.pointerEvents = 'none';
        grupoForcliContainer.style.pointerEvents = 'none';

        if (tipoSelect.value === 'metas') {

            datasContainer.style.display = 'none';
            mesContainer.style.display = 'block';

            produtoContainer.style.display = 'block';
            grupoContainer.style.display = 'block';

            produtoContainer.style.pointerEvents = 'auto';
            grupoContainer.style.pointerEvents = 'auto';

        } 
        else if (tipoSelect.value === 'lucro') {

            datasContainer.style.display = 'grid';
            mesContainer.style.display = 'none';

            produtoContainer.style.display = 'block';
            grupoContainer.style.display = 'block';

            produtoContainer.style.pointerEvents = 'auto';
            grupoContainer.style.pointerEvents = 'auto';

        } 
        else if (tipoSelect.value === 'despesas') {

            datasContainer.style.display = 'grid';
            mesContainer.style.display = 'none';

        }     
        else if (tipoSelect.value === 'servicosForcli') {

            datasContainer.style.display = 'grid';
            mesContainer.style.display = 'none';

            grupoForcliContainer.style.display = 'block';
            grupoForcliContainer.style.pointerEvents = 'auto';

        }    
        else {

            datasContainer.style.display = 'grid';
            mesContainer.style.display = 'none';

            grupoContainer.style.display = 'block';
            grupoContainer.style.pointerEvents = 'auto';
        }
    }

    // Atualiza ao carregar a página
    atualizarCampos();

    // ================= TROCA DE RELATÓRIO =================
    tipoSelect.addEventListener('change', function() {

        atualizarCampos();
        document.getElementById('formRelatorio').submit();

    });
    
    
    function abrirModalProduto() {
        document.getElementById('modalProduto').style.display = 'flex';
    }
    
    function fecharModalProduto() {
        document.getElementById('modalProduto').style.display = 'none';
    }
    
    
    function selecionarProduto(id, nome) {
        document.getElementById('produtoId').value = id;
        document.getElementById('produtoNome').value = nome;
        fecharModal();
    }
    
    function filtrarProduto() {
    
        let input = document.getElementById('buscaProduto').value.toLowerCase();
        let linhas = document.querySelectorAll('#listaProdutos tr');
    
        linhas.forEach(l => {
    
            let texto = l.innerText.toLowerCase();
            l.style.display = texto.includes(input) ? '' : 'none';
    
        });
    }

    
    document.querySelector('select[name="mes"]').addEventListener('change', function() {
        document.getElementById('formRelatorio').submit();
    });


    //FECHANDO MODAL NO ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modalBg');

            if (modal && modal.style.display === 'flex') {

                if (tipoModal === 'grupo') limparGrupo();
                if (tipoModal === 'produto') limparProduto();
                if (tipoModal === 'grupoForcli') limparGrupoForcli();

                fecharModal();
            }
        }
    });
</script>


</body>

</html>
