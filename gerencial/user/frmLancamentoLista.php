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

if ($buscaModal !== null) {

    $whereBusca = "";

    if ($buscaModal !== "") {
        $whereBusca = "AND Nome LIKE '%$buscaModal%'";
    }

    $listaBusca = ExSqlNET("
        SELECT Id, Nome
        FROM forcli
        WHERE idEmpresa = $idEmpresa
        $whereBusca
        ORDER BY Nome
        LIMIT 20
    ");

    foreach ($listaBusca as $c) {
        echo "<div class='result-item' onclick=\"selecionarCliente('{$c['Id']}','{$c['Nome']}')\">{$c['Nome']}</div>";
    }

    exit;
}

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
    $where .= " AND p.Forcli = '$clienteFiltro'";
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

    if ($repasseFiltro == 0) {
        $where .= " AND (p.ForcliRepasse = 0 OR p.ForcliRepasse IS NULL)";
    } else {
        $where .= " AND p.ForcliRepasse = '$repasseFiltro'";
    }
}

$pedidoFiltro = $_GET['pedido'] ?? '';

if ($pedidoFiltro != '') {
    $pedidoFiltro = intval($pedidoFiltro);
    $where .= " AND p.id = $pedidoFiltro";
}


// (SELECT MovimentoItem.Descricao 
//  FROM movimentoitem MovimentoItem
//  WHERE ControleMovimento = p.id
//  LIMIT 1) AS Itens

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
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
    .modal-bg {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal {
        width: 600px;
    }

    .result-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }

    .result-item:hover {
        background: #fff7ed;
    }

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
    
    .modal-bg{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.5);
        justify-content:center;
        align-items:center;
        z-index:9999;
    }

    .modal-bg{
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,0.45);
        justify-content:center;
        align-items:center;
        z-index:9999;
    }
    
    .modal{
        width:600px;
        max-width:90%;
        padding:25px;
        border-radius:12px;
        background:#fff;
        box-shadow:0 10px 30px rgba(0,0,0,0.15);
        animation:modalFade .15s ease;
    }
    
    .modal h3{
        margin-top:0;
        margin-bottom:15px;
        font-size:20px;
    }
    
    .modal input{
        width:100%;
        padding:10px 12px;
        border:1px solid #ddd;
        border-radius:8px;
        font-size:14px;
        outline:none;
        transition:.15s;
    }
    
    .modal input:focus{
        border-color:#f97316;
        box-shadow:0 0 0 2px rgba(249,115,22,0.15);
    }
    
    #resultadoBusca{
        max-height:300px;
        overflow-y:auto;
        margin-top:15px;
        border:1px solid #eee;
        border-radius:8px;
    }
    
    .result-item{
        padding:10px 12px;
        cursor:pointer;
        border-bottom:1px solid #f3f3f3;
        transition:.12s;
        font-size:14px;
    }
    
    .result-item:last-child{
        border-bottom:none;
    }
    
    .result-item:hover{
        background:#fff7ed;
    }
    
    #resultadoBusca::-webkit-scrollbar{
        width:6px;
    }
    
    #resultadoBusca::-webkit-scrollbar-thumb{
        background:#ddd;
        border-radius:4px;
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
                            <th>Status</th>
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
                            <td><?= $row['StatusLiteral'] ?></td>
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

    <!-- ================= MODAL BUSCA ================= -->
    <div class="modal-bg" id="modalBusca">
        <div class="card modal">

            <h3>Buscar Cliente</h3>

            <!--<input type="text" id="inputBusca" placeholder="Digite o nome..." onkeyup="buscarCliente(this.value)">-->

            <input type="text" id="inputBusca" placeholder="Digite o nome..." oninput="buscarCliente(this.value)">
            
            <div id="resultadoBusca" style="max-height:300px;overflow:auto;margin-top:15px;"></div>

            <br>
            <button class="btn btn-secondary" onclick="fecharModal()">Fechar</button>

        </div>
    </div>

    <script>
    let tipoSelecionando = '';

    // function abrirModal(tipo) {
    //     tipoSelecionando = tipo;
    //     buscarCliente("");
    //     document.getElementById("modalBusca").style.display = "flex";
    // }

    function abrirModal(tipo) {
    
        tipoSelecionando = tipo;
    
        document.getElementById("modalBusca").style.display = "flex";
    
        document.getElementById("inputBusca").value = '';
    
        buscarCliente("");
    
        setTimeout(() => {
            document.getElementById("inputBusca").focus();
        }, 50);
    
    }

    function fecharModal() {
        document.getElementById("modalBusca").style.display = "none";
        document.getElementById("inputBusca").value = '';
        document.getElementById("resultadoBusca").innerHTML = '';
    }

   function buscarCliente(valor){

        fetch("frmLancamentoLista.php?buscaModal=" + encodeURIComponent(valor))
        .then(res => res.text())
        .then(html => {
    
            document.getElementById("resultadoBusca").innerHTML = html;
    
        });

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
            fecharModal();
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
    

    </script>

</body>

</html>