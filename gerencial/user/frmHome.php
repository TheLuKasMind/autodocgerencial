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

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;


if ($_SESSION['usuario_id'] == 0) {
    // Significa que chegou após cadastrar a empresa.

    $sql = "
        SELECT 
            u.id, 
            u.nome, 
            u.senha, 
            u.tipo, 
            u.idEmpresa, 
            u.AdminGeral,
            e.Status,
            e.ValidadePlano,
            e.Email As EmpresaEmail,
            u.Email As UserEmail,
            u.Cargo As Cargo,
            u.NaoVerDadosFinanceiro,
            e.LimiteUsuarios
        FROM user u
        INNER JOIN empresa e ON e.id = u.idEmpresa
        WHERE u.idEmpresa = ?
        LIMIT 1
    ";

    $resultado = ExSqlNET($sql, null, [$_SESSION['idEmpresa']]);
    $usuario = $resultado[0] ?? null;

    if ($usuario) {
        $_SESSION['usuario_id']   = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['AdminGeral']   = $usuario['AdminGeral'];
        $_SESSION['usuario_cargo'] = $usuario['Cargo'];
        $_SESSION['usuario_config_NaoVerDadosFinanceiro'] = $usuario['NaoVerDadosFinanceiro'];
        if ($usuario['EmpresaEmail'] == $usuario['UserEmail']){
            $_SESSION['UserAdmin'] = 1;
        }else{
            $_SESSION['UserAdmin'] = 0;
        }

        /* ===== PERMISSÕES DE ACESSO A TELAS ===== */
        $sql = "SELECT pagina FROM userpermissoes WHERE idEmpresa = ? AND idUsuario = ?";
        $permissoes = ExSqlNET($sql,null,[$_SESSION['idEmpresa'],$_SESSION['usuario_id']]);
        $_SESSION['permissoes'] = [];
        if (!empty($permissoes)) {
            foreach ($permissoes as $permissao) {
                $_SESSION['permissoes'][] =
                $permissao['pagina'];
            }
        }
        header("Location: Home");
        exit;
    }else{
        header("Location: Login");
        exit;
    }
}

/* ================= EMPRESA ================= */

$sqlEmpresa = "
    SELECT 
        empresa.Nome,
        Documento,
        planos.Nome As Plano,
        ValidadePlano,
        empresa.MetaMensal,
        empresa.MetaDiaria,
        planos.Valor As ValorPlano,
        id_Asaas,
        Email,
        empresa.Plano As idPlano,
        empresa.Nome as nome,
        empresa.Documento as documento,
        empresa.Email as email,
        empresa.LimiteUsuarios
    FROM empresa
    LEFT JOIN planos on planos.id = empresa.Plano
    WHERE empresa.id = ?
";

$empresa = ExSqlNET($sqlEmpresa, null, [$idEmpresa])[0] ?? null;


/* ================= CONTADORES ================= */


$dadosItensHoje = ExSqlNET(
    "SELECT COUNT(movimentoitem.id) AS Total
FROM movimentoitem
LEFT JOIN movimento on movimento.id = movimentoitem.ControleMovimento
WHERE movimentoitem.idEmpresa = ?
AND DATE(movimento.Data) = CURDATE()",
    null,
    [$idEmpresa]
)[0] ?? [];

$dadosItensMes = ExSqlNET(
    "SELECT COUNT(movimentoitem.id) AS Total
     FROM movimentoitem
     LEFT JOIN movimento 
        ON movimento.id = movimentoitem.ControleMovimento
     WHERE movimentoitem.idEmpresa = ?
     AND MONTH(movimento.Data) = MONTH(CURDATE())
     AND YEAR(movimento.Data) = YEAR(CURDATE())",
    null,
    [$idEmpresa]
)[0] ?? [];


/* ================= SALDO DO DIA ================= */

$dataHoje = date('Y-m-d');

$resumo = ExSqlNET("
    SELECT 
        SUM(CASE WHEN TipoMov = 'ENTRADA' THEN Valor ELSE 0 END) entradas,
        SUM(CASE WHEN TipoMov = 'SAIDA' THEN Valor ELSE 0 END) saidas
    FROM movimentocc
    WHERE idEmpresa = ?
    AND CaixaGeral = 0
    AND DATE(Data) = ?
", null, [$idEmpresa, $dataHoje])[0] ?? [];

$saldo = ($resumo['entradas'] ?? 0) - ($resumo['saidas'] ?? 0);


/* ================= CONTAS A RECEBER ================= */

$totalReceber = ExSqlNET("
SELECT SUM(TotalItem) As Total 
FROM movimentoitem 
LEFT JOIN movimento on movimento.id = movimentoitem.ControleMovimento
WHERE movimento.idEmpresa = ?
AND movimento.Status in (0, 3)
", null, [$idEmpresa])[0] ?? [];


/* ================= METAS ================= */

$metaMensal = (float)($empresa['MetaMensal'] ?? 0);
$metaDiaria = (float)($empresa['MetaDiaria'] ?? 0);


/* ===== FATURAMENTO MÊS ===== */

$faturamentoMes = ExSqlNET("
    SELECT COALESCE(SUM(Valor),0) as Total
    FROM movimentocc
    WHERE idEmpresa = ?
    AND TipoMov = 'ENTRADA'
    AND Descricao = 'LUCRO VENDA PEDIDO'
    AND Data >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    AND Data < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)
", null, [$idEmpresa]);

$faturamentoMes = $faturamentoMes[0]['Total'] ?? 0;


/* ===== FATURAMENTO DIA ===== */


$dataHoje = date('Y-m-d');
// $dataHoje = date('Y-m-d', strtotime('-1 day')); //PARA TESTES

$faturamentoDia = ExSqlNET("
SELECT SUM(Valor) as Total FROM movimentocc
WHERE idEmpresa = ?
AND TipoMov = 'ENTRADA'
AND Descricao = 'LUCRO VENDA PEDIDO'
AND DATE(movimentocc.Data) = ?
", null, [$idEmpresa, $dataHoje])[0]['Total'] ?? 0;

/* ================= CÁLCULOS ================= */

$percMensal = $metaMensal > 0 ? ($faturamentoMes / $metaMensal) * 100 : 0;
$percMensal = min($percMensal, 100);
$faltaMensal = max($metaMensal - $faturamentoMes, 0);

$percDiaria = $metaDiaria > 0 ? ($faturamentoDia / $metaDiaria) * 100 : 0;
$percDiaria = min($percDiaria, 100);
$faltaDiaria = max($metaDiaria - $faturamentoDia, 0);


$naoVerFinanceiro = $_SESSION['usuario_config_NaoVerDadosFinanceiro'] ?? 0;

$idUser = $_SESSION['usuario_id'];

/* ================= LEMBRETES ================= */

$lembretes = ExSqlNET("
    SELECT *
    FROM movtolembrete
    WHERE idEmpresa = ?
    AND Concluido = 0
    AND idUser = ?
    AND DataLembrete <= CURDATE()
    ORDER BY DataLembrete ASC, id ASC
", null, [$idEmpresa, $idUser]);

/* ================= CONCLUIR LEMBRETE ================= */

if(isset($_GET['concluirLembrete'])){
    $idLembrete = (int)$_GET['concluirLembrete'];
    ExSqlNET("
        UPDATE movtolembrete
        SET 
            Concluido = 1,
            DataConclusao = NOW()
        WHERE idEmpresa = ?
        AND id = ?
    ", null, [
        $idEmpresa,
        $idLembrete
    ]);
    header("Location: Home");
    exit;
}

/* ================= FUNÇÕES ================= */

function formatarData($data) {
    if (!$data) return '-';
    return date('d/m/Y', strtotime($data));
}

$planoVencido = false;
if (!empty($empresa['ValidadePlano'])) {
    $planoVencido = strtotime($empresa['ValidadePlano']) < strtotime(date('Y-m-d'));
}

$diasRestantesPlano = 999;

if (!empty($empresa['ValidadePlano'])) {
    $diasRestantesPlano = (int)((strtotime($empresa['ValidadePlano']) - strtotime(date('Y-m-d'))) / 86400);
}

$listaEmpresas = ExSqlNET("
    SELECT id, Nome
    FROM empresa
    WHERE ValidadePlano >= CURDATE()
    ORDER BY Nome
");

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['acao'] ?? '') === 'trocarEmpresa') {

    ExSqlNET("
        UPDATE user
        SET idEmpresa = ?
        WHERE id = ?
    ", null, [
        $_POST['idEmpresa'],
        $_SESSION['usuario_id']
    ]);

    $retorno= ExSqlNET("SELECT id_Asaas FROM empresa WHERE id  = ?", null, [$_POST['idEmpresa']]);

    $_SESSION['idEmpresa'] = $_POST['idEmpresa'];
    $_SESSION['id_Asaas']  = $retorno['id_Asaas'];

    $_SESSION['mensagem_sucesso'] = "Empresa de acesso alterada com sucesso.";

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}


$diasRestantesPlano = 999;

if (!empty($empresa['ValidadePlano'])) {
    $hoje = new DateTime();
    $validade = new DateTime($empresa['ValidadePlano']);
    $diasRestantesPlano = (int)$hoje->diff($validade)->format('%r%a');
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Autodoc Gerencial</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/gerencial/img/favicon.png">
    <link rel="stylesheet" href="/gerencial/css/base.css?v=15">
    <style>

    .modal-aviso {
        margin-top: 15px;
        font-size: 14px;
        color: #666;
        text-align: center;
        line-height: 1.4;
    }

    .btn-pagar-plano{
        margin-top:15px;
        width:100%;
        padding:14px 20px;
        background:#86efac;
        color:#14532d;
        border:none;
        border-radius:12px;
        font-size:16px;
        font-weight:700;
        cursor:pointer;
        box-shadow:0 4px 12px rgba(0,0,0,.15);
        transition:.2s;

        animation: pulsePlano 1.5s infinite;
    }

    @keyframes pulsePlano{
        0%{
            transform:scale(1);
            box-shadow:0 0 0 0 rgba(34,197,94,.7);
        }

        50%{
            transform:scale(1.04);
            box-shadow:0 0 0 15px rgba(34,197,94,0);
        }

        100%{
            transform:scale(1);
            box-shadow:0 0 0 0 rgba(34,197,94,0);
        }
    }

    .btn-pagar-plano:hover{
        background:#4ade80;
        transform:translateY(-2px);
    }

    .btn-pagar-plano{
        position:relative;
        overflow:hidden;
        animation:pulsePlano 1.5s infinite;
    }

    .btn-pagar-plano::before{
        content:"";
        position:absolute;
        top:0;
        left:-80%;
        width:50%;
        height:100%;
        background:linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.8),
            transparent
        );
        transform:skewX(-25deg);
        animation:brilhoPlano 2.5s infinite;
    }

    @keyframes brilhoPlano{
        0%{
            left:-80%;
        }
        100%{
            left:150%;
        }
    }

    .valor-plano{
        margin-top:20px;
        text-align:center;
        font-size:42px;
        font-weight:900;
        color:#f97316;
        letter-spacing:-1px;
    }

    #imgQrCode{
        width:250px;
        display:block;
        margin:25px auto;
        padding:15px;
        background:white;
        border-radius:20px;
        box-shadow:0 10px 25px rgba(0,0,0,.08);
    }

    .contador-box{
        margin-top:10px;
        text-align:center;
        padding:15px;
        border-radius:16px;
        background:#fff7ed;
        border:1px solid #fed7aa;
    }

    .contador-box div{
        font-size:13px;
        color:#78716c;
        font-weight:600;
    }

    #contadorPix{
        display:block;
        margin-top:5px;
        font-size:32px;
        font-weight:800;
        color:#ea580c;
    }

    .modal-recuperar {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        backdrop-filter: blur(6px);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn .25s ease;
    }

    .modal-box {
        width: 100%;
        max-width: 420px;
        background: white;
        border-radius: 28px;
        padding: 35px;
        position: relative;
        box-shadow:
            0 30px 60px rgba(0, 0, 0, .20),
            0 10px 25px rgba(0, 0, 0, .08);
        animation: modalUp .25s ease;
    }

    .modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 38px;
        height: 38px;
        border: none;
        border-radius: 12px;
        background: #f8fafc;
        color: #64748b;
        font-size: 18px;
        cursor: pointer;
        transition: .2s ease;
    }

    .modal-close:hover {
        background: #fff7ed;
        color: #ea580c;
        transform: rotate(90deg);
    }

    .modal-icon {
        width: 78px;
        height: 78px;
        border-radius: 22px;
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        margin: 0 auto 22px auto;
        box-shadow:
            inset 0 0 0 1px #fed7aa;
    }

    .modal-box h3 {
        margin: 0;
        text-align: center;
        font-size: 28px;
        font-weight: 800;
        color: #111827;
    }

    .modal-text {
        margin-top: 14px;
        text-align: center;
        color: #64748b;
        line-height: 1.6;
        font-size: 15px;
    }

    .modal-input-group {
        margin-top: 28px;
    }

    .modal-input-group input {
        width: 100%;
        height: 60px;
        border-radius: 18px;
        border: 2px solid #e2e8f0;
        padding: 0 18px;
        font-size: 15px;
        font-weight: 600;
        outline: none;
        transition: .25s ease;
        background: #fff;
    }

    .modal-input-group input:focus {
        border-color: #f97316;
        box-shadow:
            0 0 0 5px rgba(249, 115, 22, .12);
    }

    .btn-pagar-plano{
        margin-top:15px;
        width:100%;
        padding:14px 20px;

        background:#86efac;
        color:#14532d;

        border:none;
        border-radius:12px;

        font-size:16px;
        font-weight:700;

        cursor:pointer;
        box-shadow:0 4px 12px rgba(0,0,0,.15);

        transition:.2s;
    }

    .btn-pagar-plano:hover{
        background:#4ade80;
        transform:translateY(-2px);
    }

    .admin-switch-form{

        display:flex;
        gap:10px;
        flex:1;
        flex-wrap:wrap;

    }

    .admin-switch-card{

        margin-top:18px;

        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;

        padding:12px 16px;

        background:#f8fafc;

        border:1px solid var(--border);
        border-radius:12px;

    }

    .admin-switch-card label{

        margin:0;

        font-size:13px;
        font-weight:600;

        color:var(--muted);

        white-space:nowrap;

    }

    .admin-switch-card select{

        flex:1;

        min-width:260px;
        height:42px;

        padding:0 12px;

        border:1px solid var(--border);
        border-radius:10px;

        background:white;

    }

    .admin-switch-card .btn{

        height:42px;
        padding:0 18px;

    }

    @media(max-width:768px){

        .admin-switch-card{
            flex-direction:column;
            align-items:stretch;
        }

    }

        .topo-empresa{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:20px;
            flex-wrap:wrap;
        }

        .usuario-logado{
            display:flex;
            align-items:center;
            gap:15px;

            background:rgba(255,255,255,0.12);
            border:1px solid rgba(255,255,255,0.15);

            padding:12px 16px;
            border-radius:14px;

            backdrop-filter:blur(4px);
        }

        .avatar-user{
            width:50px;
            height:50px;
            border-radius:50%;

            background:rgba(255,255,255,0.2);

            display:flex;
            align-items:center;
            justify-content:center;

            font-size:20px;
            font-weight:700;
            color:#fff;

            border:2px solid rgba(255,255,255,0.3);
        }

        .dados-user{
            display:flex;
            flex-direction:column;
        }

        .label-user{
            font-size:11px;
            opacity:.8;
            margin-bottom:2px;
        }

        .dados-user strong{
            font-size:15px;
            color:#fff;
        }

        .dados-user small{
            font-size:12px;
            color:#ffedd5;
        }

        .usuario-logado{
            margin-top:20px;
            display:flex;
            align-items:center;
            gap:15px;

            background:rgba(255,255,255,0.12);
            border:1px solid rgba(255,255,255,0.15);

            padding:15px;
            border-radius:14px;

            width:fit-content;
            backdrop-filter:blur(4px);
        }

        .avatar-user{
            width:52px;
            height:52px;
            border-radius:50%;

            background:rgba(255,255,255,0.2);

            display:flex;
            align-items:center;
            justify-content:center;

            font-size:22px;
            font-weight:700;
            color:#fff;

            border:2px solid rgba(255,255,255,0.3);
        }

        .dados-user{
            display:flex;
            flex-direction:column;
        }

        .label-user{
            font-size:12px;
            opacity:.8;
            margin-bottom:2px;
        }

        .dados-user strong{
            font-size:16px;
            color:#fff;
        }

        .dados-user small{
            font-size:13px;
            color:#ffedd5;
            margin-top:2px;
        }

        body {
            background: #f1f5f9;
        }

        .content {
            padding: 30px;
        }

        .empresa-box {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .empresa-nome {
            font-size: 30px;
            font-weight: 700;
        }

        .empresa-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .empresa-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px;
            border-radius: 10px;
        }

        .badge-plano {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 5px;
        }

        .plano-ativo {
            background: #22c55e;
        }

        .plano-vencido {
            background: #ef4444;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 6px solid #f97316;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
        }

        .card h3 {
            color: #64748b;
            font-size: 14px;
        }

        .card .value {
            font-size: 26px;
            font-weight: 700;
            color: #334155;
        }

        .metas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .meta-box {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.05);
            border-top: 5px solid #f97316;
        }

        .meta-box.diaria {
            border-top: 5px solid #22c55e;
        }

        .meta-titulo {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #334155;
        }

        .meta-destaque {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .meta-valores {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .progress {
            width: 100%;
            height: 16px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #f97316, #ea580c);
        }

        .meta-box.diaria .progress-bar {
            background: linear-gradient(90deg, #22c55e, #16a34a);
        }

        .falta {
            margin-top: 10px;
            font-size: 13px;
            font-weight: 600;
        }

        .ok {
            color: #16a34a;
        }

        .warn {
            color: #ef4444;
        }
        
        .duplo-linha {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        
        .duplo-linha span {
            color: #64748b;
            font-size: 14px;
        }
        
        .duplo-linha strong {
            font-size: 22px;
            color: #334155;
        }


        .lembrete-box{
            background:white;
            border-radius:14px;
            padding:20px;
            box-shadow:0 6px 14px rgba(0,0,0,0.05);
            margin-top:30px;
            margin-bottom:25px;
        }

        .lembrete-item{
            border:1px solid #e2e8f0;
            border-left:5px solid #f97316;
            border-radius:12px;

            padding:15px;
            margin-top:12px;

            display:flex;
            justify-content:space-between;
            gap:15px;

            background:#fff;
        }

        .lembrete-titulo{
            font-size:16px;
            font-weight:700;
            color:#334155;
            margin-bottom:6px;
        }

        .lembrete-descricao{
            font-size:14px;
            color:#64748b;
            white-space:pre-line;
        }

        .lembrete-data{
            margin-top:8px;
            font-size:12px;
            color:#94a3b8;
        }

        .btn-concluir{
            min-width:42px;
            height:42px;

            border-radius:50%;
            border:none;

            background:#22c55e;
            color:white;

            font-size:20px;
            cursor:pointer;

            display:flex;
            align-items:center;
            justify-content:center;

            text-decoration:none;

            transition:.2s;
        }

        .btn-concluir:hover{
            transform:scale(1.06);
            background:#16a34a;
        }

    </style>
</head>

<body>

    <?php require_once  __DIR__ .'/../base/navbarUser.php';?>
    <div class="content">

        <!-- EMPRESA -->
        <div class="empresa-box">

            <div class="topo-empresa">
                <div class="empresa-nome">
                    <?= htmlspecialchars($empresa['Nome'] ?? 'Empresa') ?>
                </div>

                <div class="usuario-logado">
                    <div class="avatar-user">
                        <?= strtoupper(substr($_SESSION['usuario_nome'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="dados-user">
                        <strong>
                            <?= htmlspecialchars($_SESSION['usuario_nome'] ?? '-') ?>
                        </strong>
                        <small>
                            <?= htmlspecialchars($_SESSION['usuario_cargo'] ?? 'Sem cargo') ?>
                        </small>

                    </div>
                </div>
            </div>

            <div class="empresa-info">

                <div class="empresa-item">
                    Documento<br>
                    <strong><?= htmlspecialchars($empresa['Documento'] ?? '-') ?></strong>
                </div>

                <div class="empresa-item">
                    Plano<br>
                    <strong><?= htmlspecialchars($empresa['Plano'] ?? '-') ?></strong>
                </div>

                <!-- <div class="empresa-item">
                    Validade<br>
                    <strong><?= formatarData($empresa['ValidadePlano'] ?? null) ?></strong>

                    <div class="badge-plano <?= $planoVencido ? 'plano-vencido' : 'plano-ativo' ?>">
                        <?= $planoVencido ? 'Plano Vencido' : 'Plano Ativo' ?>
                    </div>
                </div> -->
                <input type="hidden" name="id_Asaas" id="id_Asaas">
                <div class="empresa-item">
                    Validade<br>
                    <strong><?= formatarData($empresa['ValidadePlano'] ?? null) ?></strong>
                    <!-- <div class="badge-plano <?= $planoVencido ? 'plano-vencido' : 'plano-ativo' ?>">
                        <?= $planoVencido ? 'Plano Vencido' : 'Plano Ativo' ?>
                    </div> -->
                    <?php if ($planoVencido): ?>
                        <div class="badge-plano plano-vencido">
                            Plano Vencido
                        </div>
                    <?php elseif ($diasRestantesPlano <= 3): ?>
                        <div class="badge-plano" style="background:#f59e0b;">
                            Plano vence em <?= $diasRestantesPlano ?> dia(s)
                        </div>
                    <?php else: ?>
                        <div class="badge-plano plano-ativo">
                            Plano Ativo
                        </div>
                    <?php endif; ?>

                    <?php if ($diasRestantesPlano <= 3): ?>
                        <div style="margin-top:12px;">
                            <button class="btn-pagar-plano" onclick="pagarMensalidade()">
                                Renovar Plano - Pagar Mensalidade
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>


        <div class="cards">

        <?php if (!$naoVerFinanceiro): ?>
            <div class="card">

                <h3>Serviços / Produtos</h3>

                <div class="duplo-linha">
                    <span>Hoje</span>
                    <strong><?= $dadosItensHoje['Total'] ?? 0 ?></strong>
                </div>

                <div class="duplo-linha">
                    <span>Mês</span>
                    <strong><?= $dadosItensMes['Total'] ?? 0 ?></strong>
                </div>

            </div>


            <div class="card">
                <h3>Contas a Receber</h3>
                <div class="value">
                    R$ <?= number_format($totalReceber['Total'] ?? 0, 2, ',', '.') ?>
                </div>
            </div>

            <div class="card">
                <h3>Saldo do Dia</h3>
                <div class="value">
                    R$ <?= number_format($saldo ?? 0, 2, ',', '.') ?>
                </div>
            </div>

        </div>

        <div class="metas-grid">

            <!-- MENSAL -->
            <div class="meta-box">

                <div class="meta-titulo">Meta Mensal</div>

                <div class="meta-destaque">
                    <?= number_format($percMensal, 0) ?>%
                </div>

                <div class="meta-valores">
                    R$ <?= number_format($faturamentoMes, 2, ',', '.') ?>
                    de
                    R$ <?= number_format($metaMensal, 2, ',', '.') ?>
                </div>

                <div class="progress">
                    <div class="progress-bar" style="width: <?= $percMensal ?>%"></div>
                </div>

            </div>


            <!-- DIÁRIA -->
            <div class="meta-box diaria">

                <div class="meta-titulo">Meta Diária</div>

                <div class="meta-destaque">
                    <?= number_format($percDiaria, 0) ?>%
                </div>

                <div class="meta-valores">
                    R$ <?= number_format($faturamentoDia, 2, ',', '.') ?>
                    de
                    R$ <?= number_format($metaDiaria, 2, ',', '.') ?>
                </div>

                <div class="progress">
                    <div class="progress-bar" style="width: <?= $percDiaria ?>%"></div>
                </div>

            </div>
            
        <?php endif; ?>
        </div>

        <?php if(count($lembretes) > 0): ?>

        <div class="lembrete-box">
            <div class="meta-titulo">📌 Lembretes</div>

            <?php foreach($lembretes as $lem): ?>
                <div class="lembrete-item">
                    <div style="flex:1;">
                        <div class="lembrete-titulo">
                            <?= htmlspecialchars($lem['Titulo']) ?>
                        </div>
                        <?php if(!empty($lem['Descricao'])): ?>
                            <div class="lembrete-descricao">
                                <?= htmlspecialchars($lem['Descricao']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="lembrete-data">
                            Data:<?= date('d/m/Y', strtotime($lem['DataLembrete'])) ?>
                        </div>
                    </div>
                    <a href="?concluirLembrete=<?= $lem['id'] ?>" class="btn-concluir" onclick="return confirm('Concluir lembrete?')" title="Concluir lembrete">✔</a>
                </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>

        <?php if ($_SESSION['AdminGeral'] == 1): ?>

        <div class="admin-switch-card">

            <label>Empresa de acesso:</label>

            <form method="post" class="admin-switch-form">

                <input type="hidden" name="acao" value="trocarEmpresa">

                <select name="idEmpresa">

                    <option value=""></option>

                    <?php foreach ($listaEmpresas as $empresaUsar): ?>

                        <option
                            value="<?= $empresaUsar['id'] ?>"
                            <?= $_SESSION['idEmpresa'] == $empresaUsar['id'] ? 'selected' : '' ?>
                        >
                            <?= $empresaUsar['Nome'] ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <button type="submit" class="btn btn-gerenciar">
                    Acessar
                </button>

            </form>

        </div>

        <?php endif; ?>

    </div>

    <div id="modalPix" class="modal-recuperar">
        <div class="modal-box">
            <h3>Pagamento via PIX</h3>

            <p class="modal-text">
                Escaneie o QR Code para concluir sua assinatura.
            </p>

            <div class="valor-plano" id="valorPlanoPix">
                R$ 0,00
            </div>

            <img id="imgQrCode">

            <p class="modal-aviso">
                Após efetuar o pagamento, aguarde alguns instantes enquanto confirmamos a transação. Não feche esta janela até que a confirmação seja realizada.
            </p>

            <div class="contador-box">
                <div>⏳ Tempo restante</div>
                <span id="contadorPix">02:00</span>
            </div>
        </div>
    </div>

    <script>
        let intervaloPagamento = null;
        let contadorPagamento = null;
        let segundosPagamento = 120;
        let idCobrancaPix = null;
        let liberarSistema = 1;

        function atualizarContador(){
            let minutos = Math.floor(segundosPagamento / 60);
            let segundos = segundosPagamento % 60;
            minutos = String(minutos).padStart(2,'0');
            segundos = String(segundos).padStart(2,'0');
            document.getElementById("contadorPix").innerHTML =
                minutos + ":" + segundos;
        }

        function iniciarContadorPagamento() {
            clearInterval(contadorPagamento);
            segundosPagamento = 120;
            atualizarContador();
            contadorPagamento = setInterval(function () {
                segundosPagamento--;
                atualizarContador();
                if (segundosPagamento <= 0) {
                    clearInterval(contadorPagamento);
                    // alert("ID Asaas recebido: " + idClienteAsaas);
                    alert("Tempo do pagamento expirou.");
                    fecharModalPix();
                    document.getElementById("modalPix").style.display = "none";
                }
            }, 1000);
        }

        let empresa = <?= json_encode($empresa); ?>;
        function gerarPagamento() {
            let dados = new FormData();

            for (let chave in empresa) {
                dados.append(chave, empresa[chave]);
            }

            let plano = empresa.idPlano;

            dados.append("idPlano", plano);
            dados.append("totalUsuarios", empresa.LimiteUsuarios);

            fetch("ajax/ajaxGerarPagamentoPlano.php", {
                method: "POST",
                body: dados
            })
            .then(async (r) => {
                const texto = await r.text();

                try {
                    return JSON.parse(texto);
                } catch (e) {
                    console.error("Resposta do PHP:");
                    console.log(texto);

                    throw new Error(texto);
                }
            })
            .then(retorno => {

                if (!retorno.sucesso) {
                    alert("Erro ao gerar cobrança, contate o administrador.");
                    return;
                }

                idCobrancaPix = retorno.idCobranca;

                document.getElementById("valorPlanoPix").innerHTML =
                    "R$ " + retorno.valor;

                document.getElementById("imgQrCode").src =
                    "data:image/png;base64," + retorno.qrCode;

                document.getElementById("modalPix").style.display = "flex";

                document.getElementById("id_Asaas").value = retorno.id_Asaas;

                iniciarConsultaPagamento();
                iniciarContadorPagamento();
            })
            .catch(error => {
                console.error(error);

                let msg = error.message;

                if (msg.length > 1000) {
                    msg = msg.substring(0, 1000);
                }

                alert("Erro:\n\n" + msg);
            });
        }

        function pagarMensalidade(){
            gerarPagamento();
        }

        function atualizarContador() {
            let minutos = Math.floor(segundosPagamento / 60);
            let segundos = segundosPagamento % 60;
            minutos = String(minutos).padStart(2, '0');
            segundos = String(segundos).padStart(2, '0');
            document.getElementById("contadorPix").innerHTML =
                minutos + ":" + segundos;
        }

        function iniciarConsultaPagamento() {
            intervaloPagamento = setInterval(function() {
                fetch("ajax/ajaxStatusPagamento.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "id=" + encodeURIComponent(idCobrancaPix)
                })
                .then(r => r.json())
                // .then(async r => {
                //     let texto = await r.text();
                //     console.log("Resposta PHP:", texto);
                //     return JSON.parse(texto);

                // })
                .then(retorno => {
                    // console.log("Status pagamento:", retorno);
                    if(
                        retorno.status == "RECEIVED" ||
                        retorno.status == "CONFIRMED"
                    ){
                        if(liberarSistema == '1'){
                             fetch("ajax/ajaxStatusPagamento.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "id=" + encodeURIComponent(idCobrancaPix)
                                + "&liberarSistema=1"
                            })
                        }
                        clearInterval(intervaloPagamento);
                        clearInterval(contadorPagamento);
                        document.getElementById("modalPix").style.display = "none";
                        window.location.href = "Home";
                        // alert("Pagamento aprovado!");
                    }
                })
                .catch(erro => {
                    console.error("Erro consulta pagamento:", erro);
                });
            }, 5000);
        }

        function fecharModalPix() {
            document.getElementById("modalPix").style.display = "none";
            clearInterval(intervaloPagamento);
            clearInterval(contadorPagamento);
            if(idCobrancaPix){
                cancelarPagamento();
            }
        }

        function cancelarPagamento(){
            fetch("ajax/ajaxCancelarPagamento.php", {
                method: "POST",
                headers:{
                    "Content-Type":"application/x-www-form-urlencoded"
                },
                body:"id=" + encodeURIComponent(idCobrancaPix)
                + "&naoExcluirCliente=1"
            });
        }

        document.getElementById("modalPix").addEventListener("click", function(event){
            if(event.target === this){
                document.getElementById("modalPix").style.display = "none";
                fecharModalPix();
            }
        });

    </script>

</body>

</html>