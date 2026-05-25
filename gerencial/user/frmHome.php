<?php
require_once '../base/connection.php';
include '../base/baseFuncoes.php';
require_once '../base/verificaPlano.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../frmLogin.php");
    exit;
}

$idEmpresa = $_SESSION['idEmpresa'] ?? 0;


/* ================= EMPRESA ================= */

$sqlEmpresa = "
    SELECT 
        empresa.Nome,
        Documento,
        planos.Nome As Plano,
        ValidadePlano,
        empresa.MetaMensal,
        empresa.MetaDiaria
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
    header("Location: frmHome.php");
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
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Autodoc Gerencial</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/base.css?v=15">

    <style>

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

    <?php include '../base/navbarUser.php'; ?>

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

                <div class="empresa-item">
                    Validade<br>
                    <strong><?= formatarData($empresa['ValidadePlano'] ?? null) ?></strong>

                    <div class="badge-plano <?= $planoVencido ? 'plano-vencido' : 'plano-ativo' ?>">
                        <?= $planoVencido ? 'Plano Vencido' : 'Plano Ativo' ?>
                    </div>
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

    </div>
</body>

</html>