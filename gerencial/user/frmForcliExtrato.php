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

$idEmpresa = $_SESSION['idEmpresa'];
$clienteFiltro = $_GET['cliente'] ?? '';
$dataHoje = date('Y-m-d');
$dataInicial = $_GET['dataInicial'] ?? $dataHoje;
$dataFinal   = $_GET['dataFinal'] ?? $dataHoje;

$statusFiltro = $_GET['status'] ?? '';
$tipoPesquisa = $_GET['tipo'] ?? 0;

/* ===========================
   MOVIMENTO (PEDIDOS/VENDAS)
=========================== */
$where = " WHERE m.idEmpresa = $idEmpresa ";
$params = [];

if ($clienteFiltro != '') {
    $where .= " AND m.Forcli = ? ";
    $params[] = $clienteFiltro;
}

if ($dataInicial != '') {
    $where .= " AND DATE(m.Data) >= ? ";
    $params[] = $dataInicial;
}

if ($dataFinal != '') {
    $where .= " AND DATE(m.Data) <= ? ";
    $params[] = $dataFinal;
}

if ($statusFiltro !== '' && $statusFiltro !== null) {
    if ($statusFiltro == 0) {
        $where .= " AND m.Status IN (0,3) ";
    } else {
        $where .= " AND m.Status = ? ";
        $params[] = $statusFiltro;
    }
}

/* ===========================
   MOVIMENTOCC (CAIXA)
=========================== */
$whereMovCC = " WHERE movimentocc.idEmpresa = $idEmpresa ";
$paramsMovCC = [];

if ($clienteFiltro != '') {
    $whereMovCC .= " AND movimentocc.idForcli = ? ";
    $paramsMovCC[] = $clienteFiltro;
}

if ($dataInicial != '') {
    $whereMovCC .= " AND DATE(movimentocc.Data) >= ? ";
    $paramsMovCC[] = $dataInicial;
}

if ($dataFinal != '') {
    $whereMovCC .= " AND DATE(movimentocc.Data) <= ? ";
    $paramsMovCC[] = $dataFinal;
}

/* ===========================
   CONSULTA FINAL POR TIPO
=========================== */

if ($tipoPesquisa == 1) {

    /* ===========================
       SOMENTE PEDIDOS / VENDAS
    ============================ */
    $sqlFinal = "
        SELECT 
            m.id,
            m.Data,
            f.Nome,
            m.Obs,
            movimentocc.Descricao,
            (
                SELECT SUM(mi.TotalItem)
                FROM movimentoitem mi
                WHERE mi.ControleMovimento = m.id
            ) AS Valor,
            (
                SELECT MovimentoItem.Descricao 
                FROM movimentoitem MovimentoItem
                WHERE ControleMovimento = m.id
                LIMIT 1
            ) AS Itens,
            movimentocc.Valor AS Lucro,
            m.Status,
            m.DataPgto
        FROM movimento m
        LEFT JOIN movimentocc 
            ON movimentocc.ControleOrigem = m.id
        LEFT JOIN forcli f 
            ON f.id = m.Forcli
        $where
        ORDER BY m.Data DESC
    ";

    $lista = ExSqlNET($sqlFinal, null, $params);

} elseif ($tipoPesquisa == 2) {

    /* ===========================
       SOMENTE LANCAMENTOS DE CAIXA
    ============================ */
    $sqlFinal = "
        SELECT 
            movimentocc.Controle,
            movimentocc.Data,
            f.Nome,
            '' AS Obs,
            movimentocc.Descricao,
            movimentocc.Valor AS Valor,
            movimentocc.Descricao AS Itens,
            movimentocc.Valor AS Lucro,
            1 AS Status,
            movimentocc.DataPgto
        FROM movimentocc
        LEFT JOIN forcli f 
            ON f.id = movimentocc.idForcli
        $whereMovCC
        AND (
            movimentocc.ControleOrigem IS NULL
            OR movimentocc.ControleOrigem = 0
        )
        ORDER BY movimentocc.Data DESC
    ";

    $lista = ExSqlNET($sqlFinal, null, $paramsMovCC);

} else {

    /* ===========================
       TODOS
       PEDIDOS + CAIXA AVULSO
    ============================ */
    $sqlFinal = "
    
        SELECT 
            m.id,
            m.Data,
            f.Nome,
            m.Obs,
            movimentocc.Descricao,
            (
                SELECT SUM(mi.TotalItem)
                FROM movimentoitem mi
                WHERE mi.ControleMovimento = m.id
            ) AS Valor,
            (
                SELECT MovimentoItem.Descricao 
                FROM movimentoitem MovimentoItem
                WHERE ControleMovimento = m.id
                LIMIT 1
            ) AS Itens,
            movimentocc.Valor AS Lucro,
            m.Status,
            m.DataPgto,
            m.id as ControleMovimento
        FROM movimento m
        LEFT JOIN movimentocc 
            ON movimentocc.ControleOrigem = m.id
        LEFT JOIN forcli f 
            ON f.id = m.Forcli
        $where

        UNION ALL

        SELECT 
            movimentocc.Controle,
            movimentocc.Data,
            f.Nome,
            '' AS Obs,
            movimentocc.Descricao,
            movimentocc.Valor AS Valor,
            movimentocc.Descricao AS Itens,
            movimentocc.Valor AS Lucro,
            1 AS Status,
            movimentocc.DataPgto,
            0 As ControleMovimento
        FROM movimentocc
        LEFT JOIN forcli f 
            ON f.id = movimentocc.idForcli
        $whereMovCC
        AND (
            movimentocc.ControleOrigem IS NULL
            OR movimentocc.ControleOrigem = 0
        )

        ORDER BY Data DESC
    ";

    $lista = ExSqlNET($sqlFinal, null, array_merge($params, $paramsMovCC));

    
}



/* ===========================
   CLIENTES
=========================== */
$clientes = ExSqlNET("
    SELECT id, Nome
    FROM forcli
    WHERE idEmpresa = $idEmpresa
    ORDER BY Nome
");

/* ================= TOTAIS ================= */

$totalPago = 0;
$totalPendente = 0;
$totalLucro = 0;
    
foreach ($lista as $item) {
    $valor = $item['Valor'] ?? 0;
    $lucro = $item['Lucro'] ?? 0;

    
    if ($item['DataPgto']) {
        $totalPago += $valor;
    } else {
        $totalPendente += $valor;
    }
    
    $totalLucro += $lucro;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Extrato do Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="icon" href="../img/favicon.png">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .filters,
        .summary,
        .table-wrapper {
            background: #fff;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .05);
        }

        .filters {
            padding: 20px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filters input {
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .btn-primary {
            background: #f97316;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .summary {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-box {
            border-left: 5px solid #f97316;
        }

        .summary-box h4 {
            margin: 0 0 5px 0;
            color: #64748b;
            font-size: 14px;
        }

        .summary-box strong {
            font-size: 20px;
        }

        .table-wrapper {
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #fff7ed;
        }

        th,
        td {
            padding: 14px;
            font-size: 14px;
            text-align: left;
        }

        td {
            border-top: 1px solid #f1f5f9;
        }

        tbody tr:hover {
            background: #fff7ed;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .pago {
            background: #dcfce7;
            color: #166534;
        }

        .pendente {
            background: #fee2e2;
            color: #991b1b;
        }

        /* ================= MODAL ================= */

            .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(3px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
            overflow-y: auto;
            }

            .modal-box {
                background: #ffffff;
                width: 100%;
                max-width: 800px;
                max-height: 90vh;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 20px 40px rgba(0,0,0,.15);
                display: flex;
                flex-direction: column;
                animation: fadeIn .2s ease;
            }

            @keyframes fadeIn {
                from { transform: translateY(10px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }

            .modal-box h3 {
                margin: 0 0 16px 0;
                font-size: 18px;
                color: #1e293b;
            }

            /* Scroll interno da tabela */
            .modal-box table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
                min-width: 600px;
            }

            .modal-box thead {
                background: #f97316;
                color: #fff;
            }

            .modal-box th,
            .modal-box td {
                padding: 10px;
                border-bottom: 1px solid #e5e7eb;
                text-align: left;
            }

            .modal-box tbody {
                display: block;
                overflow-y: auto;
                max-height: 300px;
            }

            .modal-box thead,
            .modal-box tbody tr {
                display: table;
                width: 100%;
                table-layout: fixed;
            }

            .modal-box tbody tr {
                cursor: pointer;
                transition: background .15s ease;
            }

            .modal-box tbody tr:hover {
                background: #fff7ed;
            }

            /* Bot�o fechar */
            .modal-box .btn-secondary {
                margin-top: 18px;
                align-self: flex-end;
            }

                /* Scroll horizontal se precisar */
            .modal-box {
                overflow: hidden;
            }

            .modal-table-wrapper {
                overflow-x: auto;
                max-height: 300px;
                overflow-y: auto;
                border-radius: 10px;
            }

            /* Tabela normal */
            .modal-box table {
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            }

            .modal-box thead {
                background: #f97316;
                color: #fff;
                position: sticky;
                top: 0;
            }

            .modal-box th,
            .modal-box td {
                padding: 10px;
                border-bottom: 1px solid #e5e7eb;
                text-align: left;
            }

            .modal-box tbody tr:hover {
                background: #fff7ed;
                cursor: pointer;
            }

            .modal-table-wrapper{
                max-height:350px;
                overflow-y:auto;
                border:1px solid #e5e7eb;
                border-radius:8px;
            }
    </style>
</head>

<body>

    <?php include __DIR__ . '/../base/navbarUser.php'; ?>

    <div class="content">

        <div class="page-title">Extrato do Cliente</div>

        <form method="GET" id = "formFiltro" class="filters">

            <input type="hidden" name="cliente" id="clienteId" value="<?= $clienteFiltro ?>">

            <button type="button" class="btn-primary" onclick="abrirModalCliente()">
                <i class="fa-solid fa-user"></i> Selecionar Cliente
            </button>

            <input type="date" name="dataInicial" value="<?= $dataInicial ?>">
            <input type="date" name="dataFinal" value="<?= $dataFinal ?>">

            <select name="status" id="status">
                <option value="">Todos</option>
                <option value="0" <?=($statusFiltro=='0')?'selected':''?>>Em Aberto</option>
                <option value="2" <?=($statusFiltro=='2')?'selected':''?>>Orçamento</option>
                <option value="1" <?=($statusFiltro=='1')?'selected':''?>>Pago</option>
                <option value="3" <?=($statusFiltro=='3')?'selected':''?>>Débito</option>
                <option value="4" <?=($statusFiltro=='4')?'selected':''?>>Débito pago</option>
            </select>
                    
            <button class="btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>

            <button type="button" class="btn btn-secondary" onclick="imprimirLista()">
                🖨 Imprimir
            </button>

            <!-- FILTRO TIPO DE EXTRATO -->
            <select name="tipo" id="tipo">
                <option value=0 <?= (!isset($_GET['tipo']) || $_GET['tipo'] == 0) ? 'selected' : '' ?>>
                    Todos
                </option>

                <option value="1" <?= ($_GET['tipo'] ?? '') == 1 ? 'selected' : '' ?>>
                    Pedidos / Vendas
                </option>

                <option value="2" <?= ($_GET['tipo'] ?? '') == 2 ? 'selected' : '' ?>>
                    Lançamentos de Caixa
                </option>
            </select>

        </form>

        <div class="summary">
            <div class="summary-box">
                <h4>Total Pago</h4>
                <strong>R$ <?= number_format($totalPago,2,',','.') ?></strong>
            </div>

            <div class="summary-box">
                <h4>Total Pendente</h4>
                <strong>R$ <?= number_format($totalPendente,2,',','.') ?></strong>
            </div>
            
            <div class="summary-box">
                <h4>Lucro</h4>
                <strong>R$ <?= number_format($totalLucro,2,',','.') ?></strong>
            </div>
            
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Obs. Pedido</th>
                        <th>Descrição</th>
                        <th>Itens</th>
                        <th>Valor</th>
                        <th>Lucro</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($lista) > 0): ?>
                    <?php foreach($lista as $item): ?>
                    <!--<tr>-->
                    <!--<tr onclick="abrirLancamento(<?= $item['ControleMovimento'] ?>)" style="cursor:pointer;">-->
                    
                        <tr 
                            <?php if (!empty($item['ControleMovimento'])): ?>
                            onclick="abrirLancamento(<?= (int)$item['ControleMovimento'] ?>)"
                            style="cursor:pointer;"
                            <?php endif; ?>
                        >
                         
                        <td><?= date('d/m/Y', strtotime($item['Data'])) ?></td>
                        <td><?= $item['Nome'] ?></td>
                        <td><?= $item['Obs'] ?></td>
                        <td><?= $item['Descricao'] ?></td>
                        <td><?= $item['Itens'] ?></td>
                        <td>R$ <?= number_format($item['Valor'] ?? 0,2,',','.') ?></td>

                        <td>R$ <?= number_format($item['Lucro'] ?? 0,2,',','.') ?></td>
                        
                        <td>
                            <?php if ($item['Status'] == 3): ?>
                                <span class="status pendente">Débito em aberto</span>
                        
                            <?php elseif ($item['Status'] == 4): ?>
                                <span class="status pago"><?= mb_convert_encoding('Débito Pago', 'UTF-8', 'ISO-8859-1') ?></span>
                        
                            <?php elseif ($item['DataPgto']): ?>
                                <span class="status pago">Pago</span>
                        
                            <?php else: ?>
                                <span class="status pendente">Pendente</span>
                            <?php endif; ?>
                        </td>
                        
                        <!--<td>-->
                        <!--    <?php if($item['DataPgto'] || $item['Status'] == 4): ?>-->
                        <!--        <span class="status pago">Pago</span>-->
                        <!--    <?php else: ?>-->
                        <!--        <span class="status pendente">Pendente</span>-->
                        <!--    <?php endif; ?>-->
                        <!--</td>-->

                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5">Nenhum registro encontrado.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- MODAL CLIENTE -->

    <div class="modal" id="modalCliente">
        <div class="modal-box">
            <h3>Selecionar Cliente</h3>

            <input type="text" id="buscaCliente" placeholder="Digite para buscar..." onkeyup="filtrarCliente()">

            <table>
                <tbody id="listaClientes">
                    <?php foreach($clientes as $c): ?>
                    <tr onclick="selecionarCliente('<?= $c['id'] ?>','<?= $c['Nome'] ?>')">
                        <td><?= $c['Nome'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button class="btn-primary" onclick="fecharModalClienteComReset()">Fechar</button>

        </div>
    </div>

<script>

    function abrirModalCliente() {
        const modal = document.getElementById('modalCliente');
        const campoBusca = document.getElementById('buscaCliente');

        modal.style.display = 'flex';
        // delay antes do focus
        setTimeout(() => {
            campoBusca.focus();
            campoBusca.select();
        }, 100);
        
    }

    function fecharModalCliente() {
        document.getElementById('modalCliente').style.display = 'none';
        document.getElementById('buscaCliente').value = '';
        const linhas = document.querySelectorAll('#listaClientes tr');
        linhas.forEach(l => l.style.display = '');
    }

    function selecionarCliente(id, nome) {
        document.getElementById('clienteId').value = id;
        fecharModalCliente();
    }

    function filtrarCliente() {
        let input = document.getElementById('buscaCliente').value.toLowerCase();
        let linhas = document.querySelectorAll('#listaClientes tr');

        linhas.forEach(l => {
            let texto = l.innerText.toLowerCase();
            l.style.display = texto.includes(input) ? '' : 'none';
        });
    }

    function imprimirLista() {
        const form = document.getElementById("formFiltro");

        const formData = new FormData(form);

        const url = new URLSearchParams(formData).toString();

        window.open("/gerencial/Cliente/Extrato/Imprimir?" + url, "_blank");
    }
    
    function abrirLancamento(id) {                        
        window.location.href = 'Pedido?id=' + id;
    }
    
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModalClienteComReset();
        }
    });
    
    function fecharModalClienteComReset() {
        document.getElementById('modalCliente').style.display = 'none';
        document.getElementById('clienteId').value = '';
        document.getElementById('buscaCliente').value = '';
    
        const linhas = document.querySelectorAll('#listaClientes tr');
        linhas.forEach(l => l.style.display = '');
    }

    document.getElementById('modalCliente').addEventListener('click', function(e) {
    
        const modalBox = this.querySelector('.modal-box');
    
        // se clicou fora da caixa
        if (!modalBox.contains(e.target)) {
            fecharModalClienteComReset();
        }
    
    });
    
    
    </script>

</body>

</html>