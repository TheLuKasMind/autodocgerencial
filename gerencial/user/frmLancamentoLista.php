<?php
include '../base/baseFuncoes.php';
require_once '../base/connection.php';
require_once '../base/verificaPlano.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'];

$buscaModal = $_GET['buscaModal'] ?? null;

$clientes = ExSqlNET("
    SELECT id, Nome, Documento
    FROM forcli
    WHERE idEmpresa = ?
    AND Inativo = 0
    ORDER BY Nome
", null, [$_SESSION['idEmpresa']]);

$statusFiltro  = $_GET['status'] ?? '';
$clienteFiltro = $_GET['cliente'] ?? '';
$repasseFiltro = $_GET['repasse'] ?? '';

$dataHoje = date('Y-m-d');

$dataInicial = $_GET['dataInicial'] ?? $dataHoje;
$dataFinal = $_GET['dataFinal'] ?? $dataHoje;

$condicaoFiltro = $_GET['CondPgto'] ?? '';

$where = "WHERE p.idEmpresa = ". $idEmpresa;

if ($statusFiltro !== '' && $statusFiltro !== null) {
    
    if ($statusFiltro ==  0){
        $where .= " AND p.Status in ( 0, 3) ";
    }else{
        $where .= " AND p.Status = '$statusFiltro'";    
    }
    
}


if ($clienteFiltro != '') {
    // $where .= " AND p.Forcli = '$clienteFiltro'";
    if ($clienteFiltro > 0 ){
        $where .= " AND ( p.Forcli = '$clienteFiltro' OR p.ForcliRepasse = '$clienteFiltro' )";
    }
}

if ($dataInicial != '') {
    $where .= " AND CAST(p.Data AS DATE) >= '$dataInicial'";
}

if ($dataFinal != '') {
    $where .= " AND CAST(p.Data AS DATE) <= '$dataFinal'";
}
if ($condicaoFiltro != '') {
    $where .= " AND p.CondPgto = '$condicaoFiltro'";
}

if ($repasseFiltro !== '') {
    if ($repasseFiltro > 0){
        if ($repasseFiltro == 0) {
            $where .= " AND (p.ForcliRepasse = 0 OR p.ForcliRepasse IS NULL)";
        } else {
            $where .= " AND p.ForcliRepasse = '$repasseFiltro'";
        }
    }
}

$pedidoFiltro = $_GET['pedido'] ?? '';

if ($pedidoFiltro != '') {
    $pedidoFiltro = intval($pedidoFiltro);
    $where .= " AND p.id = $pedidoFiltro";
}

$placaFiltro = trim($_GET['placa'] ?? '');

if ($placaFiltro !== '') {
    $placaFiltro = addslashes($placaFiltro);
    $where .= " AND p.PlacaVeiculo = '$placaFiltro'";
}

$listaPedidos = ExSqlNET("SELECT 
    p.*,
    c.Nome AS ClienteNome,
    COALESCE(r.Nome,'') AS ClienteRepasseNome,
    CASE p.Status
        WHEN 1 THEN 'Pago'
        WHEN 2 THEN 'Orçamento'
        WHEN 0 THEN 'Em Aberto'
        WHEN 3 THEN 'Débito'
        WHEN 4 THEN 'Débito Pago'
    END AS StatusLiteral,
    CONCAT(p.ModeloVeiculo,' • ',p.CorVeiculo,' - ', p.PlacaVeiculo) As Veiculo,
    COALESCE(
        (SELECT SUM(TotalItem) 
        FROM movimentoitem 
        WHERE ControleMovimento = p.id),
        0
    ) AS Valor,
    (
    SELECT 
        CASE 
            WHEN COUNT(*) > 1 
                THEN CONCAT(MIN(Descricao), ' & outros...')
            ELSE MIN(Descricao)
        END
    FROM movimentoitem
    WHERE ControleMovimento = p.id
    ) AS Itens,
    CASE p.CondPgto 
        WHEN 1 THEN 'À Vista / Dinheiro'
        WHEN 2 THEN 'Pix'
        WHEN 3 THEN 'Cartão de Crédito'
        WHEN 4 THEN 'Cartão de Débito'
        WHEN 5 THEN 'Cheque'
        WHEN 6 THEN '30 Dias'
        WHEN 7 THEN '60 Dias'
    END AS CondPgtoLiteral,
    movimentocc.Valor As Lucro,
    p.id As Codigo,
    p.Obs As Obs
FROM movimento p
LEFT JOIN forcli c ON c.Id = p.Forcli
LEFT JOIN forcli r ON r.Id = p.ForcliRepasse
LEFT JOIN movimentocc on movimentocc.ControleOrigem = p.id
$where
ORDER BY p.Id DESC
");


$totalGeral = 0;

foreach ($listaPedidos as $p) {
    $totalGeral += $p['Valor'];
}

/* =========================
   MARCAR PEDIDOS COMO PAGO
   ========================= */

if(isset($_POST['marcar_pago']) && !empty($_POST['pedidos'])){

    $ids = implode(",", array_map('intval', $_POST['pedidos']));
    
    //SETA PEDIDO PAGO
    ExSqlNET("
        UPDATE movimento
        SET Status = 1,
            DataPgto = NOW()
        WHERE idEmpresa = $idEmpresa
        AND Status <> 3
        AND id IN ($ids)
    ");
    
    //SETA DÉBITO PAGO
    ExSqlNET(" 
        UPDATE movimento
        SET Status = 4,
            DataPgto = NOW()
        WHERE idEmpresa = $idEmpresa
        AND Status = 3
        AND id IN ($ids)
    ");

    $_SESSION['mensagem_sucesso'] = "Pedidos marcados como pagos!";
    header("Location: frmLancamentoLista.php");
    exit;
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos</title>
    <link rel="icon" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/base.css?v=15"> 
    <link rel="stylesheet" href="../css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
  
    .lista-clientes {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-top: 10px;
    }

    .cliente-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #f1f1f1;
    }

    .cliente-item:hover {
        background: #fff7ed;
    }
    

    
    @keyframes modalFade{
        from{
            opacity:0;
            transform:scale(.96);
        }
        to{
            opacity:1;
            transform:scale(1);
        }
    }
    
     
        .totais-card{
            width: 320px;
            text-align: right;
            font-size: 16px;
        }
        
        .totais-card div{
            margin: 6px 0;
        }


        .totais-container{
            display:flex;
            justify-content:flex-end;
            gap:15px;
            margin-top:20px;
            flex-wrap:wrap;
        }
        
        .totais-card{
            background:#fff;
            border:1px solid #f97316;
            border-radius:10px;
            padding:15px 20px;
            min-width:180px;
            box-shadow:0 4px 12px rgba(0,0,0,0.05);
            text-align:right;
            transition:.2s;
        }
        
        .totais-card:hover{
            transform:translateY(-2px);
            box-shadow:0 8px 18px rgba(0,0,0,0.08);
        }
        
        .totais-label{
            display:block;
            font-size:13px;
            color:#777;
            margin-bottom:5px;
        }
        
        .totais-valor{
            font-size:20px;
            font-weight:700;
            color:#f97316;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            align-items: end;
        }
        
        /* TABLET */
        @media (max-width: 1024px) {
            .form-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        /* MOBILE */
        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
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

        /*.status-pago{*/
        /*    background: #dcfce7;*/
        /*    color: #166534;*/
        /*    font-weight: 700;*/
        /*    border-radius: 6px;*/
        /*    text-align: center;*/
        /*}*/
        .status-pago{
            background: #dcfce7;
            color: #166534;
            font-weight: 700;
            border-radius: 6px;
            text-align: center;
        }
        
        .status-aberto{
            background: #fef9c3;
            color: #854d0e;
            font-weight: 700;
            border-radius: 6px;
            text-align: center;
        }

    </style>
</head>

<body>

    <?php include '../base/navbarUser.php'; ?>

    <div class="content">

        <div class="page-title">Lista de Pedidos</div>
        <?php

            $msgRetorno = "";
            $tipoMsg = "";
            if (isset($_SESSION['mensagem_sucesso'])) {
                $msgRetorno = $_SESSION['mensagem_sucesso'];
                $tipoMsg = "success";
                unset($_SESSION['mensagem_sucesso']);
            }
            if (isset($_SESSION['mensagem_erro'])) {
                $tipoMsg = "error";
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
        <!-- ================= FILTROS NA PÁGINA ================= -->
        <div class="card">
            <form method="GET" id="formFiltro">

                <div class="form-grid">
                    <!--<div>-->
                    <!--    <label>Data Inicial</label>-->
                    <!--    <input type="date" name="dataInicial" value="<?= $dataInicial ?>">-->

                    <!--    <label>Data Final</label>-->
                    <!--    <input type="date" name="dataFinal" value="<?= $dataFinal ?>">-->
                    <!--</div>-->
                    <div>
                        <label>Data Inicial</label>
                        <input type="date" name="dataInicial" value="<?= $dataInicial ?>">
                    </div>


                    <div>
                        <label>Status</label>
                        <select name="status">
                            <option value="">Todos</option>
                            <option value="0" <?=($statusFiltro=='0')?'selected':''?>>Em Aberto</option>
                            <option value="2" <?=($statusFiltro=='2')?'selected':''?>>Orçamento</option>
                            <option value="1" <?=($statusFiltro=='1')?'selected':''?>>Pago</option>
                            <option value="3" <?=($statusFiltro=='3')?'selected':''?>>Débito</option>
                            <option value="4" <?=($statusFiltro=='4')?'selected':''?>>Débito pago</option>
                        </select>

                    </div>

                    <div>
                        <label>Condição de Pagamento</label>
                        <select name="CondPgto" id="CondPgto">

                            <option value="">Todas</option>

                            <option value="1" <?=($condicaoFiltro=='1')?'selected':''?>>À Vista / Dinheiro</option>
                            <option value="2" <?=($condicaoFiltro=='2')?'selected':''?>>Pix</option>
                            <option value="3" <?=($condicaoFiltro=='3')?'selected':''?>>Cartão de Crédito</option>
                            <option value="4" <?=($condicaoFiltro=='4')?'selected':''?>>Cartão de Débito</option>
                            <option value="5" <?=($condicaoFiltro=='5')?'selected':''?>>Cheque</option>
                            <option value="6" <?=($condicaoFiltro=='6')?'selected':''?>>30 Dias</option>
                            <option value="7" <?=($condicaoFiltro=='7')?'selected':''?>>60 Dias</option>

                        </select>
                    </div>

                    <div>
                        <label>Cliente</label>
                        <input type="hidden" name="cliente" id="cliente_id" value="<?= $clienteFiltro ?>">
                        <input type="text" id="cliente_nome" value="<?php
                            if($clienteFiltro){
                                $c = ExSqlNET("SELECT Nome FROM forcli WHERE Id='$clienteFiltro'");
                                echo $c[0]['Nome'] ?? '';
                            } ?>" placeholder="Clique para buscar..." onclick="abrirModal('cliente')" readonly>
                    </div>

                    <div>
                        <label>Pagador</label>
                        <input type="hidden" name="repasse" id="repasse_id" value="<?= $repasseFiltro ?>">
                        <input type="text" id="repasse_nome" value="<?php
                        if($repasseFiltro){
                            $c = ExSqlNET("SELECT Nome FROM forcli WHERE Id='$repasseFiltro'");
                            echo $c[0]['Nome'] ?? '';
                        } ?>" placeholder="Clique para buscar..." onclick="abrirModal('repasse')" readonly>
                    </div>

                    <div>
                        <label>Data Final</label>
                        <input type="date" name="dataFinal" value="<?= $dataFinal ?>">
                    </div>


                     <div>
                        <label>Pedido</label>
                        <input type="text" name="pedido" id="pedido"
                            value="<?= $_GET['pedido'] ?? '' ?>"
                            placeholder="Digite o código do pedido">
                    </div>
                    
                    <div>
                        <label>Placa</label>
                        <input type="text" name="placa" id="placa"
                            value="<?= $_GET['placa'] ?? '' ?>"
                            placeholder="Placa do veículo no pedido...">
                    </div>
                        
                </div>

                <div class="actions" style="margin-top:20px;">
                    <a href="frmLancamento.php" class="btn btn">Novo Pedido</a>
                    <button type="submit" class="btn btn-secondary">Consultar</button>

                    <!-- <button type="button" class="btn btn-secondary"
                        onclick="window.open(
                        'frmLancamentoListaImprimir.php?<?= http_build_query($_GET) ?>',
                        '_blank'
                    )">
                    🖨 Imprimir
                    </button> -->
                    <button type="button" class="btn btn-secondary" onclick="imprimirLista()">
                        🖨 Imprimir
                    </button>

                </div>

            </form>
        </div>

        <form method="POST">

            <div style="margin-bottom:15px;">
                <button type="submit" name="marcar_pago" class="btn">
                    ✔ Marcar selecionados como Pago
                </button>
            </div>
                    

            <!-- ================= TABELA ================= -->
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                           <th>
                                <input type="checkbox" onclick="marcarTodos(this)">
                            </th>
                            <th>Código</th>
                            <th>Data</th>
                            <th>Cliente</th>
                            <th>Pagador</th>
                            <th>Itens</th>
                            <!--<th>Cond.Pgto</th>-->
                            <th>Obs</th>
                            <th>Veículo</th>
                            <!--<th>Status</th>-->
                            <th style="text-align:center;">Status</th>
                            <th>Valor</th>
                             <th>Lucro</th>
                        </tr>
                    </thead>
                    
                    <tbody>
    
                        <?php if(count($listaPedidos)>0): ?>
                        <?php foreach($listaPedidos as $row): ?>
    
                        <tr onclick="window.location.href='frmLancamento.php?id=<?= $row['id'] ?>'">
    
                            <td>
                                <!--<input type="checkbox" name="pedidos[]" value="<?= $row['id'] ?>" onclick="event.stopPropagation()">-->
                                    <input 
                                        type="checkbox"
                                        name="pedidos[]"
                                        value="<?= $row['id'] ?>"
                                        onclick="event.stopPropagation(); calcularTotalSelecionados();">
                            </td>
                             <td><?= $row['Codigo'] ?></td>
                            <td><?= date('d/m/Y', strtotime($row['Data'])) ?></td>
                            <td><?= $row['ClienteNome'] ?></td>
                            <td><?= $row['ClienteRepasseNome'] ?></td>
                            <td><?= $row['Itens'] ?></td>
                            <td><?= $row['Obs'] ?></td>
                            <!--<td><?= $row['CondPgtoLiteral'] ?></td>-->
                            <td><?= $row['Veiculo'] ?></td>
                            <!--<td><?= $row['StatusLiteral'] ?></td>-->
                            <!--<td class="<?= in_array($row['Status'], [1,4]) ? 'status-pago' : '' ?>">-->
                            <!--    <?= $row['StatusLiteral'] ?>-->
                            <!--</td>-->
                            <td class="<?=
                                in_array($row['Status'], [1,4]) 
                                    ? 'status-pago' 
                                    : (in_array($row['Status'], [0,3]) ? 'status-aberto' : '')
                            ?>">
                                <?= $row['StatusLiteral'] ?>
                            </td>
                            <td>R$ <?= number_format($row['Valor'],2,',','.') ?></td>
                            <td>R$ <?= number_format($row['Lucro'] ?? 0, 2, ',', '.') ?></td>
    
                        </tr>
    
                        <?php endforeach; ?>
                        <?php else: ?>
    
                        <tr>
                            <td colspan="8">Nenhum pedido encontrado.</td>
                        </tr>
    
                        <?php endif; ?>
    
                    </tbody>
                    
                </table>
          </form>
        </div>

        <div class="totais-container">
        
            <div class="totais-card">
                <span class="totais-label">Total de Registros</span>
                <span class="totais-valor"><?= count($listaPedidos) ?></span>
            </div>
        
            <div class="totais-card">
                <span class="totais-label">Total Geral</span>
                <span class="totais-valor">
                    R$ <?= number_format($totalGeral,2,',','.') ?>
                </span>
            </div>
        
            <div class="totais-card">
                <span class="totais-label">Total Selecionados</span>
                <span class="totais-valor">
                    R$ <span id="totalSelecionados">0,00</span>
                </span>
            </div>
        
        </div>

    </div>

    <!-- MODAL PADRÃO -->
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

            <button class="btn" onclick="fecharModal()">Fechar</button>
        </div>
    </div>

    <script>
    let tipoSelecionando = '';
    let clientes = <?= json_encode($clientes ?? []) ?>;
    
    function carregarModal(filtro = '') {
        let head = document.getElementById('modalHead');
        let body = document.getElementById('modalBody');

        head.innerHTML = '';
        body.innerHTML = '';

        filtro = filtro.toLowerCase();
        let html = '';

        if (tipoModal === 'cliente') {
            tipoSelecionando = 'cliente';
            document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';
            head.innerHTML = '<th>Nome</th><th>Documento</th>';

            (clientes || [])
                .filter(c => (c.Nome || '').toLowerCase().includes(filtro))
                .forEach(c => {
                    let nomeSeguro = (c.Nome || '').replace(/'/g, "\'");
                    html += `
                        <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
                            <td>${c.Nome}</td>
                            <td>${c.Documento || ''}</td>
                        </tr>`;
                });
        }

        if (tipoModal === 'repasse') {
            tipoSelecionando = 'repasse';
            document.getElementById('modalTitulo').innerText = 'Selecionar Pagador';
            head.innerHTML = '<th>Nome</th><th>Documento</th>';

            (clientes || [])
                .filter(c => (c.Nome || '').toLowerCase().includes(filtro))
                .forEach(c => {
                    let nomeSeguro = (c.Nome || '').replace(/'/g, "\'");
                    html += `
                        <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
                            <td>${c.Nome}</td>
                            <td>${c.Documento || ''}</td>
                        </tr>`;
                });
        }

        body.innerHTML = html;
    }

    function abrirModal(tipo) {
        tipoModal = tipo;
        document.getElementById('modalBg').style.display = 'flex';
        document.getElementById('modalBusca').value = '';
        carregarModal('');
        setTimeout(() => {
            document.getElementById('modalBusca').focus();
        }, 50);
    }

    function fecharModal() {
        document.getElementById('modalBg').style.display = 'none';
    }

    function cancelarModal() {
        if(tipoSelecionando === 'cliente'){
            limparInput('cliente');
        } else if(tipoSelecionando === 'repasse'){
            limparInput('repasse');
        }

        fecharModal();
    }

    function selecionarCliente(id, nome) {
        if (tipoSelecionando === 'cliente') {
            document.getElementById("cliente_id").value = id;
            document.getElementById("cliente_nome").value = nome;
        } else {
            document.getElementById("repasse_id").value = id;
            document.getElementById("repasse_nome").value = nome;
        }
        fecharModal();
    }

    function limparInput(tipo) {
        if(tipo === 'cliente'){
            document.getElementById('cliente_id').value = 0;
            document.getElementById('cliente_nome').value = '';
        }else if(tipo === 'repasse'){
            document.getElementById('repasse_id').value = 0;
            document.getElementById('repasse_nome').value = '';
        }
    }

    function abrirLancamento(id) {
        window.location.href = 'frmLancamento.php?id=' + id;
    }

    function imprimirLista() {
        const form = document.getElementById("formFiltro");

        const formData = new FormData(form);

        const url = new URLSearchParams(formData).toString();

        window.open("frmLancamentoListaImprimir.php?" + url, "_blank");
    }

//   function marcarTodos(source) {

//         let checkboxes = document.querySelectorAll('input[name="pedidos[]"]');

//         checkboxes.forEach(c => {
//             c.checked = source.checked;
//         });

//     }
    function marcarTodos(source) {
    
        let checkboxes = document.querySelectorAll('input[name="pedidos[]"]');
    
        checkboxes.forEach(c => {
            c.checked = source.checked;
        });
    
        calcularTotalSelecionados();
    }
    
    //FECHANDO QUALQUER MODAL
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cancelarModal();
        }
    });
    
    function calcularTotalSelecionados() {
    
        let total = 0;
    
        document.querySelectorAll('input[name="pedidos[]"]:checked')
        .forEach(cb => {
    
            let valor = cb.closest("tr")
                          .querySelector("td:last-child")
                          .innerText
                          .replace("R$", "")
                          .replace(".", "")
                          .replace(",", ".");
    
            total += parseFloat(valor);
    
        });
    
        document.getElementById("totalSelecionados").innerText =
            total.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
    
    }
    

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('modalBusca').addEventListener('input', function() {
                carregarModal(this.value);
            });

            document.getElementById('modalBg').addEventListener('click', function(e) {
                if (e.target === this) cancelarModal();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') cancelarModal();

                
            });
        });

    </script>

</body>

</html>