<?php
require_once __DIR__ . '/../base/connection.php';
require_once  __DIR__ .'/../base/baseFuncoes.php';
require_once  __DIR__ .'/../base/verificaPlano.php';
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

$buscaModal = $_GET['buscaModal'] ?? null;

// $clientes = ExSqlNET("
//     SELECT id, Nome, Documento
//     FROM forcli
//     WHERE idEmpresa = ?
//     AND Inativo = 0
//     ORDER BY Nome
// ", null, [$_SESSION['idEmpresa']]);

if (isset($_GET['buscar_clientes']) && $_GET['buscar_clientes'] == 1) {
    header('Content-Type: application/json');
    $termo = trim($_GET['q'] ?? '');
    $idEmpresa = $_SESSION['idEmpresa'];

    $sql = "SELECT id, Nome, Documento 
            FROM forcli 
            WHERE idEmpresa = ? 
            AND Inativo = 0";
    
    $params = [$idEmpresa];

    if ($termo !== '') {
        $sql .= " AND (Nome LIKE ? OR Documento LIKE ?)";
        $params[] = "%$termo%";
        $params[] = "%$termo%";
    }

    $sql .= " ORDER BY Nome LIMIT 20";

    $clientesEncontrados = ExSqlNET($sql, null, $params);
    echo json_encode($clientesEncontrados ?: []);
    exit;
}


// Se veio pelo navbar, limpa todos os filtros
if (isset($_GET['limparFiltros']) && $_GET['limparFiltros'] == 1) {
    unset($_SESSION['filtroPedidos']);

}

// Se o usuário realizou uma pesquisa, salva os filtros na sessão
$getSemUrl = $_GET;
unset($getSemUrl['url']);

if (!empty($getSemUrl)) {
    $_SESSION['filtroPedidos'] = $_GET;
}

// Recupera os filtros salvos, caso existam
$filtros = $_SESSION['filtroPedidos'] ?? [];


// Pesquisou os filtros?
$camposFiltro = [
    'status',
    'cliente',
    'repasse',
    'dataInicial',
    'dataFinal',
    'CondPgto',
    'statusProcesso',
    'pedido',
    'placa'
];

$pesquisouFiltros = false;

foreach ($camposFiltro as $campo) {
    if (isset($_GET[$campo])) {
        $pesquisouFiltros = true;
        break;
    }
}

// Salva somente se realmente for uma pesquisa.
if ($pesquisouFiltros) {
    $_SESSION['filtroPedidos'] = [];

    foreach ($camposFiltro as $campo) {
        $_SESSION['filtroPedidos'][$campo] = $_GET[$campo] ?? '';
    }
}

$filtros = $_SESSION['filtroPedidos'] ?? [];







// $statusFiltro  = $_GET['status'] ?? '';
// $clienteFiltro = $_GET['cliente'] ?? '';
// $repasseFiltro = $_GET['repasse'] ?? '';
$statusFiltro  = $_GET['status'] ?? ($filtros['status'] ?? '');
$clienteFiltro = $_GET['cliente'] ?? ($filtros['cliente'] ?? '');
$repasseFiltro = $_GET['repasse'] ?? ($filtros['repasse'] ?? '');

$dataHoje = date('Y-m-d');

// $dataInicial = $_GET['dataInicial'] ?? $dataHoje;
// $dataFinal = $_GET['dataFinal'] ?? $dataHoje;

// $condicaoFiltro = $_GET['CondPgto'] ?? '';

// $statusProcessoFiltro  = $_GET['statusProcesso'] ?? '';

$dataInicial = $_GET['dataInicial'] ?? ($filtros['dataInicial'] ?? $dataHoje);
$dataFinal = $_GET['dataFinal'] ?? ($filtros['dataFinal'] ?? $dataHoje);

$condicaoFiltro = $_GET['CondPgto'] ?? ($filtros['CondPgto'] ?? '');

$statusProcessoFiltro = $_GET['statusProcesso'] ?? ($filtros['statusProcesso'] ?? '');

$pedidoFiltro = $_GET['pedido'] ?? ($filtros['pedido'] ?? '');

$placaFiltro = trim($_GET['placa'] ?? ($filtros['placa'] ?? ''));


$where = "WHERE p.idEmpresa = ". $idEmpresa;

if ($statusFiltro !== '' && $statusFiltro !== null) {
    if ($statusFiltro ==  0){
        $where .= " AND p.Status in ( 0, 3) ";
    }else{
        $where .= " AND p.Status = '$statusFiltro'";    
    }
}

if ($statusProcessoFiltro  !== '' && $statusProcessoFiltro  !== null && $statusProcessoFiltro  !== '0') {
    $where .= " AND p.StatusProcesso = '$statusProcessoFiltro '";    
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

// $pedidoFiltro = $_GET['pedido'] ?? '';

if (!empty($pedidoFiltro)) {

    $pedidos = explode(',', $pedidoFiltro);

    $pedidos = array_map(function ($v) {
        return (int) trim($v);
    }, $pedidos);

    $pedidos = array_filter($pedidos);

    if (!empty($pedidos)) {
        $lista = implode(',', $pedidos);
        $where .= " AND p.Id IN ($lista)";
    }
}

// $placaFiltro = trim($_GET['placa'] ?? '');

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
    ExSqlNET(" UPDATE movimento SET Status = 1, DataPgto = NOW(), DataAlt = NOW()  WHERE idEmpresa = $idEmpresa  AND Status <> 3 AND id IN ($ids)");
    
    //SETA DÉBITO PAGO
    ExSqlNET(" UPDATE movimento SET Status = 4, DataPgto = NOW(), DataAlt = NOW()  WHERE idEmpresa = $idEmpresa  AND Status = 3  AND id IN ($ids) ");
    $_SESSION['mensagem_sucesso'] = "Pedidos marcados como pagos!";
    header("Location: Pedidos");
    exit;
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos</title>
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <link rel="stylesheet" href="/gerencial/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
  

    .status-processo{
        font-weight: 700;
        text-align: center;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        min-width: 130px;
        display: inline-block;
        letter-spacing: .3px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .processo-andamento{
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
        animation: pulseStatus 1.8s infinite;
    }

    .processo-concluido{
        background: #dcfce7;
        color: #166534;
        border: 1px solid #22c55e;
    }

    @keyframes pulseStatus{
        0%{
            box-shadow: 0 0 0 0 rgba(245,158,11,.5);
        }
        70%{
            box-shadow: 0 0 0 8px rgba(245,158,11,0);
        }
        100%{
            box-shadow: 0 0 0 0 rgba(245,158,11,0);
        }
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

        .modal-table-wrapper {
            height: 320px; 
            max-height: 320px;
            overflow-y: auto;
            margin-top: 10px;
            border: 1px solid #eee;
            border-radius: 8px;
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

    

        /* ==========================================================
           AUTODOC — CAMADA VISUAL MODERNA
           Mantém as rotas, filtros, consultas e ações existentes.
           ========================================================== */
        :root {
            --pd-orange: #f97316;
            --pd-orange-dark: #ea580c;
            --pd-orange-soft: #fff7ed;
            --pd-text: #1f2937;
            --pd-muted: #6b7280;
            --pd-border: #e5e7eb;
            --pd-bg: #f8fafc;
            --pd-green: #15803d;
            --pd-red: #b91c1c;
        }

        body {
            background: var(--pd-bg);
            color: var(--pd-text);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .content {
            width: min(100% - 48px, 1440px);
            max-width: 1440px;
            margin: 0 auto;
            padding: 28px 0 42px;
            box-sizing: border-box;
        }

        .page-heading {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 7px;
            margin: 0 0 24px;
            min-width: 0;
        }

        .page-title {
            display: block;
            position: static;
            margin: 0;
            padding: 0;
            color: #111827;
            font-size: clamp(24px, 3vw, 30px);
            font-weight: 750;
            letter-spacing: -.7px;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .page-description {
            display: block;
            position: static;
            margin: 0;
            padding: 0;
            max-width: 100%;
            color: var(--pd-muted);
            font-size: 14px;
            font-weight: 400;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .card {
            margin: 0 0 20px;
            padding: 20px;
            border: 1px solid var(--pd-border);
            border-radius: 13px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
            box-sizing: border-box;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px 16px;
            align-items: end;
        }

        .form-grid > div {
            min-width: 0;
        }

        .form-grid label {
            display: block;
            margin: 0 0 7px;
            color: #374151;
            font-size: 12px;
            font-weight: 650;
        }

        .form-grid input:not([type="hidden"]),
        .form-grid select,
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            min-height: 41px;
            box-sizing: border-box;
            padding: 9px 11px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            background: #fff;
            color: var(--pd-text);
            font: inherit;
            font-size: 13px;
            transition: border-color .18s ease, box-shadow .18s ease;
        }

        .form-grid input:focus,
        .form-grid select:focus,
        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: var(--pd-orange);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, .13);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
            padding-top: 17px;
            border-top: 1px solid #f1f5f9;
        }

        .btn,
        .btn-secondary,
        .actions a,
        .actions button {
            display: inline-flex;
            min-height: 39px;
            align-items: center;
            justify-content: center;
            gap: 7px;
            box-sizing: border-box;
            padding: 9px 15px;
            border: 1px solid var(--pd-orange);
            border-radius: 8px;
            background: var(--pd-orange);
            color: #fff !important;
            font-family: inherit;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(234, 88, 12, .10);
            transition: background .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }

        .btn:hover,
        .actions a:hover,
        .actions button:hover {
            border-color: var(--pd-orange-dark);
            background: var(--pd-orange-dark);
            box-shadow: 0 4px 10px rgba(234, 88, 12, .16);
            transform: translateY(-1px);
        }

        .btn-secondary {
            border-color: #e5e7eb;
            background: #fff;
            color: #374151 !important;
            box-shadow: none;
        }

        .btn-secondary:hover {
            border-color: #fdba74;
            background: #fff7ed;
            color: #374151 !important;
            transform: translateY(-1px);
        }

        .content > form > div[style*="margin-bottom"] {
            margin: 0 0 12px !important;
        }

        .content > form > div[style*="margin-bottom"] .btn {
            background: #15803d;
            border-color: #15803d;
        }

        .content > form > div[style*="margin-bottom"] .btn:hover {
            background: #166534;
            border-color: #166534;
        }

        .content .card:has(table) {
            padding: 0;
            overflow-x: auto;
        }

        .content table {
            width: 100%;
            min-width: 1050px;
            border-collapse: separate;
            border-spacing: 0;
            text-align: left;
            font-size: 13px;
        }

        .content table thead th {
            padding: 13px 14px;
            border-bottom: 1px solid var(--pd-border);
            background: #f9fafb;
            color: #6b7280;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .055em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .content table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #374151;
            font-size: 12px;
            line-height: 1.45;
            vertical-align: middle;
        }

        .content table tbody tr {
            background: #fff;
            transition: background .15s ease;
        }

        .content table tbody tr[onclick] {
            cursor: pointer;
        }

        .content table tbody tr[onclick]:hover {
            background: #fffaf5;
        }

        .content table tbody tr:last-child td {
            border-bottom: 0;
        }

        .content table input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--pd-orange);
            cursor: pointer;
        }

        .status-processo {
            min-width: 0;
            padding: 5px 9px;
            border-radius: 999px;
            box-shadow: none;
            font-size: 11px;
            font-weight: 650;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .processo-andamento {
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #92400e;
            animation: none;
        }

        .processo-concluido {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .status-pago,
        .status-aberto {
            padding: 7px 9px;
            border-radius: 7px;
            font-size: 11px;
            white-space: nowrap;
        }

        .status-pago {
            background: #f0fdf4;
            color: #166534;
        }

        .status-aberto {
            background: #fffbeb;
            color: #92400e;
        }

        .totais-container {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .totais-card {
            width: auto;
            min-width: 0;
            padding: 18px 20px;
            border: 1px solid var(--pd-border);
            border-radius: 12px;
            background: #fff;
            text-align: left;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .totais-card:hover {
            border-color: #fed7aa;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .06);
            transform: translateY(-1px);
        }

        .totais-label {
            margin-bottom: 8px;
            color: var(--pd-muted);
            font-size: 12px;
            font-weight: 550;
        }

        .totais-valor {
            color: #111827;
            font-size: clamp(20px, 2vw, 24px);
            font-weight: 750;
            letter-spacing: -.4px;
            overflow-wrap: anywhere;
        }

        .modal-bg {
            background: rgba(15, 23, 42, .48);
            backdrop-filter: blur(4px);
        }

        .modal {
            width: min(680px, calc(100% - 28px));
            max-width: 680px;
            padding: 22px;
            border: 1px solid rgba(229, 231, 235, .9);
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .22);
        }

        .modal h3 {
            color: #111827;
            font-size: 19px;
            font-weight: 750;
            letter-spacing: -.3px;
        }

        .modal-search {
            min-height: 42px;
            box-sizing: border-box;
            border-color: #d1d5db;
            border-radius: 8px;
        }

        .modal-table-wrapper {
            border-color: var(--pd-border);
            border-radius: 9px;
        }

        .modal table {
            min-width: 0;
            font-size: 13px;
        }

        .modal th {
            background: #f9fafb;
            color: #6b7280;
            font-size: 10px;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .modal td {
            color: #374151;
        }

        .modal tbody tr:hover {
            background: var(--pd-orange-soft);
        }

        .alert {
            margin: 0 0 18px;
            padding: 12px 15px;
            border: 1px solid transparent;
            border-radius: 9px;
            font-size: 13px;
        }

        .alert.success {
            border-color: #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .alert.error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        @media (max-width: 1100px) {
            .form-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .content table {
                min-width: 1050px;
            }
        }

        @media (max-width: 700px) {
            .content {
                width: calc(100% - 24px);
                padding: 20px 0 30px;
            }
            .page-heading {
                gap: 6px;
                margin-bottom: 20px;
            }
            .page-title {
                font-size: clamp(23px, 6vw, 28px);
                letter-spacing: -.45px;
            }
            .page-description {
                font-size: 14px;
                line-height: 1.6;
            }
            .card {
                padding: 15px;
                border-radius: 11px;
            }
            .form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px 11px;
            }
            .actions {
                align-items: stretch;
            }
            .actions .btn,
            .actions a,
            .actions button {
                flex: 1 1 auto;
            }
            .totais-container {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .totais-card {
                padding: 15px 17px;
            }
            .modal {
                padding: 17px;
            }
        }

        @media (max-width: 600px) {
            .form-grid {
                grid-template-columns: minmax(0, 1fr);
            }
            .content .card:has(table) {
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .content table {
                min-width: 900px;
            }
            .totais-container {
                display: flex;
                flex-direction: column;
                align-items: stretch;
            }
            .totais-card {
                width: 100%;
                min-width: 0;
                box-sizing: border-box;
                text-align: left;
            }
            .actions .btn,
            .actions a,
            .actions button {
                width: 100%;
                box-sizing: border-box;
                justify-content: center;
            }
        }

        @media (max-width: 420px) {
            .form-grid {
                grid-template-columns: minmax(0, 1fr);
            }
            .content {
                width: calc(100% - 20px);
            }
            .card {
                padding: 13px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }

    </style>
</head>

<body>

    <?php include __DIR__ . '/../base/navbarUser.php'; ?>

    <div class="content">

        <header class="page-heading">
            <h1 class="page-title">Lista de Pedidos</h1>
            <p class="page-description">Consulte, filtre e acompanhe os pedidos da sua empresa.</p>
        </header>
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
                        

                    <div>
                        <label>Andamento Processo</label>
                        <select name="statusProcesso" id="statusProcesso">
                            <option value="0" <?=($statusProcessoFiltro=='0')?'selected':''?>>Todos</option>
                            <option value="1" <?=($statusProcessoFiltro=='1')?'selected':''?>>Em Andamento</option>
                            <option value="2" <?=($statusProcessoFiltro=='2')?'selected':''?>>Concluído</option>
                        </select>
                    </div>

                </div>

                <div class="actions" style="margin-top:20px;">
                    <a href="Pedido" class="btn btn">Novo Pedido</a>
                    <button type="submit" class="btn btn-secondary">Consultar</button>
                    <button type="button" class="btn btn-secondary" onclick="imprimirLista()">
                        🖨 Imprimir
                    </button>

                </div>

            </form>
        </div>

        <form method="POST">

            <div style="margin-bottom:15px;">
                <button type="submit" 
                        name="marcar_pago" 
                        class="btn"
                        onclick="return confirm('Deseja realmente marcar os pedidos selecionados como pagos?')">
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
                            <th style="text-align:center;">Processo</th>
                            <th style="text-align:center;">Status</th>
                            <th>Valor</th>
                             <th>Lucro</th>
                        </tr>
                    </thead>
                    
                    <tbody>
    
                        <?php if(count($listaPedidos)>0): ?>
                        <?php foreach($listaPedidos as $row): ?>
    
                        <tr onclick="window.location.href='/gerencial/Pedido?id=<?= $row['id'] ?>'">
    
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
                            <td style="text-align:center;">
                                <?php if ($row['StatusProcesso'] == 1): ?>
                                    <span class="status-processo processo-andamento">
                                        ⏳ Em Andamento
                                    </span>
                                <?php elseif ($row['StatusProcesso'] == 2): ?>
                                    <span class="status-processo processo-concluido">
                                        ✔ Concluído
                                    </span>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                            </td>
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
    
    // function carregarModal(filtro = '') {
    //     let head = document.getElementById('modalHead');
    //     let body = document.getElementById('modalBody');

    //     head.innerHTML = '';
    //     body.innerHTML = '';

    //     filtro = filtro.toLowerCase();
    //     let html = '';

    //     if (tipoModal === 'cliente') {
    //         tipoSelecionando = 'cliente';
    //         document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';
    //         head.innerHTML = '<th>Nome</th><th>Documento</th>';

    //         (clientes || [])
    //             .filter(c => (c.Nome || '').toLowerCase().includes(filtro))
    //             .forEach(c => {
    //                 let nomeSeguro = (c.Nome || '').replace(/'/g, "\'");
    //                 html += `
    //                     <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
    //                         <td>${c.Nome}</td>
    //                         <td>${c.Documento || ''}</td>
    //                     </tr>`;
    //             });
    //     }

    //     if (tipoModal === 'repasse') {
    //         tipoSelecionando = 'repasse';
    //         document.getElementById('modalTitulo').innerText = 'Selecionar Pagador';
    //         head.innerHTML = '<th>Nome</th><th>Documento</th>';

    //         (clientes || [])
    //             .filter(c => (c.Nome || '').toLowerCase().includes(filtro))
    //             .forEach(c => {
    //                 let nomeSeguro = (c.Nome || '').replace(/'/g, "\'");
    //                 html += `
    //                     <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
    //                         <td>${c.Nome}</td>
    //                         <td>${c.Documento || ''}</td>
    //                     </tr>`;
    //             });
    //     }

    //     body.innerHTML = html;
    // }

    function carregarModal(filtro = '') {
        let head = document.getElementById('modalHead');
        let body = document.getElementById('modalBody');

        head.innerHTML = '';
        body.innerHTML = '<tr><td colspan="2" style="text-align:center; color:#777;">A carregar...</td></tr>';

        if (tipoModal === 'cliente') {
            tipoSelecionando = 'cliente';
            document.getElementById('modalTitulo').innerText = 'Selecionar Cliente';
        } else if (tipoModal === 'repasse') {
            tipoSelecionando = 'repasse';
            document.getElementById('modalTitulo').innerText = 'Selecionar Pagador';
        }

        head.innerHTML = '<th>Nome</th><th>Documento</th>';

        fetch(`Pedidos?buscar_clientes=1&q=${encodeURIComponent(filtro)}`)
            .then(response => response.json())
            .then(clientes => {
                let html = '';
                
                if (!clientes || clientes.length === 0) {
                    body.innerHTML = `<tr><td colspan="2" style="text-align:center; color:#777;">Nenhum registo encontrado</td></tr>`;
                    return;
                }

                clientes.forEach(c => {
                    let nome = c.Nome || '';
                    let documento = c.Documento || '';
                    let nomeSeguro = nome.replace(/'/g, "\\'");
                    
                    html += `
                        <tr onclick="selecionarCliente(${c.id}, '${nomeSeguro}')">
                            <td>${nome}</td>
                            <td>${documento}</td>
                        </tr>`;
                });

                body.innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao buscar clientes:', error);
                body.innerHTML = `<tr><td colspan="2" style="text-align:center; color:red;">Erro ao carregar dados</td></tr>`;
            });
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
        window.location.href = 'Pedido?id=' + id;
    }

    function imprimirLista() {
        const form = document.getElementById("formFiltro");

        const formData = new FormData(form);

        const url = new URLSearchParams(formData).toString();

        window.open("Pedidos/ImprimirLista?" + url, "_blank");
    }

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
                          .querySelectorAll("td")[10]
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