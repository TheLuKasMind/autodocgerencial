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

$clientes = ExSqlNET("
    SELECT id, Nome, Documento
    FROM forcli
    WHERE idEmpresa = ?
    AND Inativo = 0
    ORDER BY Nome
", null, [$idEmpresa]);

$statusFiltro   = $_GET['status'] ?? '';
$clienteFiltro  = $_GET['cliente'] ?? '';
$serieFiltro    = $_GET['serie'] ?? '';
$placaFiltro    = $_GET['placa'] ?? '';
$orgaoFiltro    = $_GET['orgao'] ?? '';
$processoFiltro = $_GET['processo'] ?? '';
$autoFiltro     = $_GET['auto'] ?? '';
$dataInicial    = $_GET['dataInicial'] ?? '';
$dataFinal      = $_GET['dataFinal'] ?? '';

// SE NÃO VEIO FILTRO, USA DATA DE HOJE
if ($dataInicial == '' && $dataFinal == '') {
    $dataHoje = date('Y-m-d');
    $dataInicial = $dataHoje;
    $dataFinal = $dataHoje;
}

$where = " WHERE m.idEmpresa = $idEmpresa ";

if ($statusFiltro !== '') {
    $where .= " AND m.StatusMulta = '$statusFiltro' ";
}

if ($clienteFiltro != '') {
    $where .= " AND m.Forcli = '$clienteFiltro' ";
}

if ($serieFiltro != '') {
    $where .= " AND m.SerieMulta LIKE '%$serieFiltro%' ";
}

if ($placaFiltro != '') {
    $where .= " AND m.PlacaVeiculo LIKE '%$placaFiltro%' ";
}

if ($orgaoFiltro != '') {
    $where .= " AND m.OrgaoFiscalizador LIKE '%$orgaoFiltro%' ";
}

if ($processoFiltro != '') {
    $where .= " AND m.CodigoProcesso LIKE '%$processoFiltro%' ";
}

if ($autoFiltro !== '') {
    $where .= " AND m.AutoSuspensiva = '$autoFiltro' ";
}

if ($dataInicial != '') {
    $where .= " AND CAST(m.DataCadastro AS DATE) >= '$dataInicial' ";
}

if ($dataFinal != '') {
    $where .= " AND CAST(m.DataCadastro AS DATE) <= '$dataFinal' ";
}

$listaMultas = ExSqlNET("
    SELECT 
        m.*,
        c.Nome AS ClienteNome,
        CASE m.StatusMulta
            WHEN 0 THEN 'Em Aberto'
            WHEN 1 THEN 'Defesa Enviada'
            WHEN 2 THEN 'Em Recurso'
            WHEN 3 THEN 'Deferida'
            WHEN 4 THEN 'Indeferida'
            WHEN 5 THEN 'Finalizada'
        ELSE 'Não Definido'
        END AS StatusLiteral
    FROM multa m
    LEFT JOIN forcli c ON c.Id = m.Forcli
    $where
    ORDER BY m.Id DESC
");

$totalMultas = count($listaMultas);
$totalAuto = 0;

foreach ($listaMultas as $multa) {
    if (($multa['AutoSuspensiva'] ?? 0) == 1) {
        $totalAuto++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Lista de Multas</title>
    <link rel="icon" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
    body {
        font-family: 'Inter', sans-serif;
    }

    .modal h3 {
        margin: 0 0 12px 0;
        font-size: 18px;
        color: #333;
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


    .form-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        align-items: end;
    }

    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 600px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .actions {
        margin-top: 25px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .totais-container {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .totais-card {
        background: #fff;
        border: 1px solid #f97316;
        border-radius: 10px;
        padding: 15px 20px;
        min-width: 200px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        text-align: right;
    }

    .totais-label {
        display: block;
        font-size: 13px;
        color: #777;
        margin-bottom: 5px;
    }

    .totais-valor {
        font-size: 20px;
        font-weight: 700;
        color: #f97316;
    }

    .autosuspensiva {
        color: #dc2626;
        font-weight: bold;
    }

    .modal-bg {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal {
        background: #fff;
        width: 650px;
        max-width: 95%;
        border-radius: 14px;
        padding: 22px;
    }

    .modal-search {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    .modal-table-wrapper {
        max-height: 380px;
        overflow-y: auto;
        margin-top: 12px;
    }

    .modal table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal th, .modal td {
        padding: 10px;
        border-bottom: 1px solid #eee;
    }

    .modal tbody tr:hover {
        background: #fff7ed;
        cursor: pointer;
    }
</style>
</head>
<body>

<?php include '../base/navbarUser.php'; ?>

<div class="content">

    <div class="page-title">Lista de Multas</div>

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

    <div class="card">
        <form method="GET" id="formFiltro">

            <div class="form-grid">

                <div>
                    <label>Data Inicial</label>
                    <input type="date" name="dataInicial" value="<?= $dataInicial ?>">
                </div>

                <div>
                    <label>Data Final</label>
                    <input type="date" name="dataFinal" value="<?= $dataFinal ?>">
                </div>

                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="0" <?=($statusFiltro==='0')?'selected':''?>>Em Aberto</option>
                        <option value="1" <?=($statusFiltro==='1')?'selected':''?>>Defesa Enviada</option>
                        <option value="2" <?=($statusFiltro==='2')?'selected':''?>>Em Recurso</option>
                        <option value="3" <?=($statusFiltro==='3')?'selected':''?>>Deferida</option>
                        <option value="4" <?=($statusFiltro==='4')?'selected':''?>>Indeferida</option>
                        <option value="4" <?=($statusFiltro==='5')?'selected':''?>>Finalizada</option>
                    </select>
                </div>

                <div>
                    <label>Cliente</label>
                    <input type="hidden" name="cliente" id="cliente_id" value="<?= $clienteFiltro ?>">
                    <input type="text" id="cliente_nome" readonly placeholder="Clique para buscar..." onclick="abrirModal()"
                    value="<?php
                        if($clienteFiltro){
                            $c = ExSqlNET("SELECT Nome FROM forcli WHERE Id='$clienteFiltro'");
                            echo $c[0]['Nome'] ?? '';
                        }
                    ?>">
                </div>

                <div>
                    <label>Série</label>
                    <input type="text" name="serie" value="<?= htmlspecialchars($serieFiltro) ?>">
                </div>

                <div>
                    <label>Placa</label>
                    <input type="text" name="placa" value="<?= htmlspecialchars($placaFiltro) ?>">
                </div>

                <div>
                    <label>Órgão Fiscalizador</label>
                    <select name="orgao">
                        <option value="">Todos</option>
                        <option value="Brigada Militar" <?=($orgaoFiltro==='Brigada Militar')?'selected':''?>>Brigada Militar</option>
                        <option value="PRF" <?=($orgaoFiltro==='PRF')?'selected':''?>>PRF</option>
                        <option value="DETRAN" <?=($orgaoFiltro==='DETRAN')?'selected':''?>>DETRAN</option>
                        <option value="DNIT" <?=($orgaoFiltro==='DNIT')?'selected':''?>>DNIT</option>
                    </select>
                </div>

                <div>
                    <label>Número Processo</label>
                    <input type="text" name="processo" value="<?= htmlspecialchars($processoFiltro) ?>">
                </div>

                <div>
                    <label>Auto Suspensiva</label>
                    <select name="auto">
                        <option value="">Todos</option>
                        <option value="1" <?=($autoFiltro==='1')?'selected':''?>>Sim</option>
                        <option value="0" <?=($autoFiltro==='0')?'selected':''?>>Não</option>
                    </select>
                </div>

            </div>

            <div class="actions">
                <a href="frmMulta.php" class="btn">Nova Multa</a>
                <button type="submit" class="btn btn-secondary">Consultar</button>
                <button type="button" class="btn btn-secondary" onclick="imprimirLista()">🖨 Imprimir</button>
            </div>


        </form>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <!-- <th>Código</th> -->
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Série</th>
                    <th>Processo</th>
                    <th>Placa</th>
                    <th>Órgão</th>
                    <th>Prazo Defesa</th>
                    <th>Auto Suspensiva</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($listaMultas) > 0): ?>
                    <?php foreach($listaMultas as $m): ?>
                        <tr onclick="window.location.href='frmMulta.php?id=<?= $m['id'] ?>'" style="cursor:pointer;">
                            <!-- <td><?= $m['id'] ?></td> -->
                            <td><?= date('d/m/Y', strtotime($m['DataCadastro'])) ?></td>
                            <td><?= htmlspecialchars($m['ClienteNome']) ?></td>
                            <td><?= htmlspecialchars($m['SerieMulta']) ?></td>
                            <td><?= htmlspecialchars($m['CodigoProcesso']) ?></td>
                            <td><?= htmlspecialchars($m['PlacaVeiculo']) ?></td>
                            <td><?= htmlspecialchars($m['OrgaoFiscalizador']) ?></td>
                            <td><?= !empty($m['PrazoDefesa']) ? date('d/m/Y', strtotime($m['PrazoDefesa'])) : '' ?></td>
                            <td class="<?= ($m['AutoSuspensiva'] == 1 ? 'autosuspensiva' : '') ?>">
                                <?= ($m['AutoSuspensiva'] == 1 ? 'SIM' : 'NÃO') ?>
                            </td>
                            <td><?= $m['StatusLiteral'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">Nenhuma multa encontrada.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="totais-container">
        <div class="totais-card">
            <span class="totais-label">Total de Multas</span>
            <span class="totais-valor"><?= $totalMultas ?></span>
        </div>

        <div class="totais-card">
            <span class="totais-label">Auto Suspensivas</span>
            <span class="totais-valor"><?= $totalAuto ?></span>
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
// let clientes = <?= json_encode($clientes ?? []) ?>;
let clientes = <?= json_encode($clientes ?? []) ?>;

function abrirModal() {
    document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';
    document.getElementById('modalBg').style.display = 'flex';

    document.getElementById('modalHead').innerHTML = `
        <th>Nome</th>
        <th>Documento</th>
    `;

    carregarClientes('');
    document.getElementById('modalBusca').value = '';

    setTimeout(() => {
        document.getElementById('modalBusca').focus();
    }, 50);

}

function fecharModal() {
    document.getElementById('modalBg').style.display = 'none';
}

function carregarClientes(filtro = '') {
    filtro = filtro.toLowerCase();
    let html = '';

    clientes
        .filter(c => (c.Nome || '').toLowerCase().includes(filtro))
        .forEach(c => {
            let nomeSeguro = (c.Nome || '').replace(/'/g, "\\'");
            html += `
                <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
                    <td>${c.Nome}</td>
                    <td>${c.Documento || ''}</td>
                </tr>
            `;
        });

    document.getElementById('modalBody').innerHTML = html;
}

function selecionarCliente(id, nome) {
    document.getElementById('cliente_id').value = id;
    document.getElementById('cliente_nome').value = nome;
    fecharModal();
}

function imprimirLista() {
    const form = document.getElementById('formFiltro');
    const formData = new FormData(form);
    const url = new URLSearchParams(formData).toString();

    window.open('frmMultaListaImprimir.php?' + url, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalBusca').addEventListener('input', function() {
        carregarClientes(this.value);
    });

    document.getElementById('modalBg').addEventListener('click', function(e) {
        if (e.target === this) {
            fecharModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fecharModal();
        }
    });
});
</script>

</body>
</html>